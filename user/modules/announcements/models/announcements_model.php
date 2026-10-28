<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/announcements_content.php';

/* [AI:GPT-5.6 | 2026-09-01 05:00:00 UTC] */
final class announcements_model extends model
{
    private const TABLE = 'announcements';

    public function databaseState(): string
    {
        if (!$this->tableExists(self::TABLE)) {
            return 'missing';
        }
        return $this->columnExists(self::TABLE, 'content_type') ? 'current' : 'outdated';
    }

    public function installSchema(): void
    {
        $this->executeSqlFile(__DIR__ . '/../sql/schema.sql');
    }

    public function updateSchema(): void
    {
        $state = $this->databaseState();
        if ($state === 'missing') {
            throw new RuntimeException('Install the Announcements database before updating it.');
        }
        if ($state === 'outdated') {
            $this->executeSqlFile(__DIR__ . '/../sql/patches/1.3.5-to-1.3.6.sql');
        }
    }

    public function deleteData(): void
    {
        if ($this->tableExists(self::TABLE)) {
            $this->query('DELETE FROM `announcements`');
        }
    }

    public function getAll(): array
    {
        return $this->fetchAll('SELECT * FROM `announcements` ORDER BY `id` DESC');
    }

    public function getById(int $id)
    {
        return $this->fetch('SELECT * FROM `announcements` WHERE `id` = :id LIMIT 1', ['id' => $id]);
    }

    public function getActive(): array
    {
        return $this->renderPublic($this->fetchAll('SELECT * FROM `announcements` WHERE `published` = :published ORDER BY `created_at` DESC', ['published' => 1]));
    }

    /** Compatibility API used by existing domain modules. */
    public function get_latest_single()
    {
        $item = $this->fetch(
            'SELECT * FROM `announcements` '
            . 'WHERE `published` = :published '
            . 'ORDER BY `created_at` DESC LIMIT 1',
            ['published' => 1]
        );
        return is_array($item) ? $this->renderPublicItem($item) : $item;
    }

    /** Compatibility API used by existing domain modules. */
    public function get_latest(int $limit = 5): array
    {
        $limit = max(1, min($limit, 50));

        $statement = $this->db->prepare(
            'SELECT * FROM `announcements` '
            . 'WHERE `published` = :published '
            . 'ORDER BY `created_at` DESC LIMIT :result_limit'
        );
        $statement->bindValue(':published', 1, PDO::PARAM_INT);
        $statement->bindValue(':result_limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $this->renderPublic($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Compatibility API retained for installed consumers. */
    public function get_active(): array
    {
        return $this->getActive();
    }

    /** Compatibility API retained for installed consumers. */
    public function get_all(): array
    {
        return $this->getAll();
    }

    /** Compatibility API retained for installed consumers. */
    public function get_by_id(int $id)
    {
        return $this->getById($id);
    }

    public function createAnnouncement(array $data): int
    {
        return (int) $this->insert(self::TABLE, $data);
    }

    public function updateAnnouncement(int $id, array $data): void
    {
        $this->update(self::TABLE, $data, 'id = :record_id', ['record_id' => $id]);
    }

    public function deleteAnnouncement(int $id): void
    {
        $this->query('DELETE FROM `announcements` WHERE `id` = :id', ['id' => $id]);
    }

    private function renderPublic(array $items): array
    {
        foreach ($items as &$item) {
            $item = $this->renderPublicItem($item);
        }
        unset($item);
        return $items;
    }

    private function renderPublicItem(array $item): array
    {
        $content = new announcements_content(new render_md());
        $item['rendered_body'] = $content->render(
            (string) ($item['body'] ?? ''),
            (string) ($item['content_type'] ?? '')
        );
        return $item;
    }

    private function tableExists(string $table): bool
    {
        $row = $this->fetch(
            'SELECT 1 AS `present` FROM `information_schema`.`tables` '
            . 'WHERE `table_schema` = DATABASE() AND `table_name` = :table_name LIMIT 1',
            ['table_name' => $table]
        );
        return is_array($row);
    }

    private function columnExists(string $table, string $column): bool
    {
        $row = $this->fetch(
            'SELECT 1 AS `present` FROM `information_schema`.`columns` '
            . 'WHERE `table_schema` = DATABASE() AND `table_name` = :table_name '
            . 'AND `column_name` = :column_name LIMIT 1',
            ['table_name' => $table, 'column_name' => $column]
        );
        return is_array($row);
    }

    private function executeSqlFile(string $path): void
    {
        $sql = is_file($path) && !is_link($path) ? file_get_contents($path) : false;
        if (!is_string($sql) || trim($sql) === '') {
            throw new RuntimeException('Announcements schema file is unavailable.');
        }
        $this->query($sql);
    }
}
/* [End AI:GPT-5.6] */
