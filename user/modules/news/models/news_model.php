<?php
declare(strict_types=1);

// path: /user/modules/news/models/news_model.php

/**
 * ChAoS MVC News Model
 *
 * Owns news articles, Markdown source, publication state,
 * schema lifecycle, and database operations.
 *
 * [AI:GPT-6 | 2026-10-22 UTC]
 */

final class news_model extends model
{
    private const TABLES = [
        'news_schema',
        'news_articles',
    ];

    private const STATE_TABLE = 'news_schema';
    private const ARTICLE_TABLE = 'news_articles';

    private const CATEGORIES = [
        'release',
        'security',
        'development',
        'announcement',
    ];

    public function getModuleInformation(): array
    {
        return [
            'name' => 'News',
            'slug' => 'news',
            'version' => '1.0.0',
            'schema_version' => $this->targetVersion(),
            'tables' => self::TABLES,
        ];
    }

    /*
     * ---------------------------------------------------------
     * Schema lifecycle
     * ---------------------------------------------------------
     */

    public function databaseState(): string
    {
        foreach (self::TABLES as $table) {
            if (!$this->tableExists($table)) {
                return 'missing';
            }
        }

        $current = $this->schemaVersion();
        $target = $this->targetVersion();

        if ($current === null || $target === '') {
            return 'invalid';
        }

        if ($current === $target) {
            return 'current';
        }

        return $this->patchFile($current, $target) !== null
            ? 'update'
            : 'invalid';
    }

    public function installSchema(): void
    {
        if ($this->databaseState() !== 'missing') {
            throw new RuntimeException(
                'News schema installation is not permitted.'
            );
        }

        $this->executeSqlFile(
            __DIR__ . '/../sql/schema.sql'
        );
    }

    public function updateSchema(): void
    {
        $current = $this->schemaVersion();
        $target = $this->targetVersion();

        $patch = $current === null
            ? null
            : $this->patchFile($current, $target);

        if ($patch === null) {
            throw new RuntimeException(
                'No valid News schema migration path exists.'
            );
        }

        $this->executeSqlFile($patch);

        $this->query(
            'UPDATE `news_schema`
             SET `schema_version` = :version
             WHERE `id` = 1',
            ['version' => $target]
        );
    }

    /*
     * ---------------------------------------------------------
     * Public queries
     * ---------------------------------------------------------
     */

    public function getPublishedArticles(): array
    {
        return $this->fetchAll(
            'SELECT `id`, `title`, `slug`, `category`,
                    `summary`, `published_at`, `updated_at`
             FROM `news_articles`
             WHERE `status` = :status
               AND `published_at` IS NOT NULL
               AND `published_at` <= CURRENT_TIMESTAMP
             ORDER BY `published_at` DESC, `id` DESC',
            ['status' => 'published']
        ) ?: [];
    }

    public function getPublishedArticle(
        string $slug
    ): ?array {
        $row = $this->fetch(
            'SELECT `id`, `title`, `slug`, `category`,
                    `summary`, `body_markdown`,
                    `published_at`, `updated_at`
             FROM `news_articles`
             WHERE `slug` = :slug
               AND `status` = :status
               AND `published_at` IS NOT NULL
               AND `published_at` <= CURRENT_TIMESTAMP
             LIMIT 1',
            [
                'slug' => $slug,
                'status' => 'published',
            ]
        );

        return is_array($row) ? $row : null;
    }

    /*
     * ---------------------------------------------------------
     * Administration queries
     * ---------------------------------------------------------
     */

    public function getAllArticles(): array
    {
        return $this->fetchAll(
            'SELECT `id`, `title`, `slug`, `category`,
                    `summary`, `body_markdown`, `status`,
                    `published_at`, `created_at`, `updated_at`
             FROM `news_articles`
             ORDER BY `created_at` DESC, `id` DESC'
        ) ?: [];
    }

    public function getArticle(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $row = $this->fetch(
            'SELECT *
             FROM `news_articles`
             WHERE `id` = :id
             LIMIT 1',
            ['id' => $id]
        );

        return is_array($row) ? $row : null;
    }

    /*
     * ---------------------------------------------------------
     * Article CRUD
     * ---------------------------------------------------------
     */

    public function createArticle(array $input): int
    {
        $data = $this->validatedArticle($input);

        $this->assertUniqueSlug($data['slug']);

        $data['published_at'] =
            $data['status'] === 'published'
                ? date('Y-m-d H:i:s')
                : null;

        return (int) $this->insert(
            self::ARTICLE_TABLE,
            $data
        );
    }

    public function updateArticle(array $input): void
    {
        $id = (int) ($input['id'] ?? 0);
        $existing = $this->getArticle($id);

        if ($existing === null) {
            throw new InvalidArgumentException(
                'A valid News article is required.'
            );
        }

        $data = $this->validatedArticle($input);

        $this->assertUniqueSlug($data['slug'], $id);

        if ($data['status'] === 'published') {
            $data['published_at'] =
                $existing['status'] === 'published'
                && !empty($existing['published_at'])
                    ? $existing['published_at']
                    : date('Y-m-d H:i:s');
        } else {
            $data['published_at'] = null;
        }

        $this->update(
            self::ARTICLE_TABLE,
            $data,
            'id = :id',
            ['id' => $id]
        );
    }

    public function deleteArticle(int $id): void
    {
        if ($this->getArticle($id) === null) {
            throw new InvalidArgumentException(
                'A valid News article is required.'
            );
        }

        $this->query(
            'DELETE FROM `news_articles`
             WHERE `id` = :id',
            ['id' => $id]
        );
    }

    /*
     * ---------------------------------------------------------
     * Validation
     * ---------------------------------------------------------
     */

    private function validatedArticle(
        array $input
    ): array {
        $title = trim((string) ($input['title'] ?? ''));
        $slug = strtolower(
            trim((string) ($input['slug'] ?? ''))
        );
        $category = trim(
            (string) ($input['category'] ?? 'announcement')
        );
        $summary = trim(
            (string) ($input['summary'] ?? '')
        );
        $body = (string) ($input['body_markdown'] ?? '');
        $status = trim(
            (string) ($input['status'] ?? 'draft')
        );

        if ($title === '' || mb_strlen($title) > 150) {
            throw new InvalidArgumentException(
                'Title is required and must not exceed 150 characters.'
            );
        }

        if ($slug === '') {
            $slug = strtolower($title);
            $slug = preg_replace(
                '/[^a-z0-9]+/',
                '-',
                $slug
            );
            $slug = trim((string) $slug, '-');
        }

        if (
            strlen($slug) > 180
            || !preg_match(
                '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $slug
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid article slug.'
            );
        }

        if (!in_array($category, self::CATEGORIES, true)) {
            throw new InvalidArgumentException(
                'Invalid News category.'
            );
        }

        if (mb_strlen($summary) > 300) {
            throw new InvalidArgumentException(
                'Summary must not exceed 300 characters.'
            );
        }

        if (strlen($body) > 262144) {
            throw new InvalidArgumentException(
                'Markdown content exceeds the 256 KiB limit.'
            );
        }

        if (!in_array($status, ['draft', 'published'], true)) {
            throw new InvalidArgumentException(
                'Invalid publication status.'
            );
        }

        if (
            $status === 'published'
            && trim($body) === ''
        ) {
            throw new InvalidArgumentException(
                'Published articles must contain Markdown content.'
            );
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'category' => $category,
            'summary' => $summary,
            'body_markdown' => $body,
            'status' => $status,
        ];
    }

    private function assertUniqueSlug(
        string $slug,
        int $excludeId = 0
    ): void {
        $existing = $this->fetch(
            'SELECT `id`
             FROM `news_articles`
             WHERE `slug` = :slug
             LIMIT 1',
            ['slug' => $slug]
        );

        if (
            is_array($existing)
            && (int) $existing['id'] !== $excludeId
        ) {
            throw new InvalidArgumentException(
                'An article with this slug already exists.'
            );
        }
    }

    /*
     * ---------------------------------------------------------
     * Lifecycle helpers
     * ---------------------------------------------------------
     */

    private function schemaVersion(): ?string
    {
        if (!$this->tableExists(self::STATE_TABLE)) {
            return null;
        }

        $row = $this->fetch(
            'SELECT `schema_version`
             FROM `news_schema`
             WHERE `id` = 1 LIMIT 1'
        );

        $version = is_array($row)
            ? (string) ($row['schema_version'] ?? '')
            : '';

        return $this->validVersion($version)
            ? $version
            : null;
    }

    private function targetVersion(): string
    {
        $raw = file_get_contents(
            __DIR__ . '/../module.json'
        );

        $metadata = is_string($raw)
            ? json_decode($raw, true)
            : null;

        $version = is_array($metadata)
            ? (string) ($metadata['schema_version'] ?? '')
            : '';

        return $this->validVersion($version)
            ? $version
            : '';
    }

    private function validVersion(string $version): bool
    {
        return preg_match(
            '/^[0-9]+\.[0-9]+\.[0-9]+$/',
            $version
        ) === 1;
    }

    private function patchFile(
        string $current,
        string $target
    ): ?string {
        $file = __DIR__
            . '/../sql/patches/'
            . $current
            . '-to-'
            . $target
            . '.sql';

        return is_file($file) && !is_link($file)
            ? $file
            : null;
    }

    private function executeSqlFile(string $file): void
    {
        if (!is_file($file) || is_link($file)) {
            throw new RuntimeException(
                'News SQL file could not be read.'
            );
        }

        $sql = file_get_contents($file);

        if (!is_string($sql) || trim($sql) === '') {
            throw new RuntimeException(
                'News SQL file is empty or unreadable.'
            );
        }

        $statements = preg_split(
            '/;\s*(?:\r?\n|$)/',
            $sql
        );

        if (!is_array($statements)) {
            throw new RuntimeException(
                'News SQL file could not be parsed.'
            );
        }

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement !== '') {
                $this->query($statement);
            }
        }
    }

    private function tableExists(string $table): bool
    {
        if (!in_array($table, self::TABLES, true)) {
            return false;
        }

        return (bool) $this->fetch(
            'SELECT 1
             FROM information_schema.tables
             WHERE table_schema = :schema
               AND table_name = :table_name
             LIMIT 1',
            [
                'schema' => DB_NAME,
                'table_name' => $table,
            ]
        );
    }
}

/* [End AI:GPT-6 | 2026-10-22] */
