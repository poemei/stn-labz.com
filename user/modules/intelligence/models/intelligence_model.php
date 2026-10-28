<?php
declare(strict_types=1);

/* [AI:GPT-5.6 Sol | 2026-10-06 UTC] */

final class intelligence_model extends model
{
    private const RECORDS_TABLE = 'intelligence_records';
    private const OBSERVATIONS_TABLE = 'intelligence_observations';
    private const ASSIGNMENTS_TABLE = 'intelligence_assignments';

    /*
     * Intelligence is a permanent institutional repository.
     *
     * This model intentionally provides no deleteData(),
     * uninstall, truncate, reset, or destructive lifecycle operation.
     */

    public function databaseState(): string
    {
        foreach ([
            self::RECORDS_TABLE,
            self::OBSERVATIONS_TABLE,
            self::ASSIGNMENTS_TABLE,
        ] as $table) {
            if (!$this->tableExists($table)) {
                return 'missing';
            }
        }

        return 'current';
    }

    public function installSchema(): void
    {
        $this->executeSqlFile(__DIR__ . '/../sql/schema.sql');
    }

    public function getAll(): array
    {
        $rows = $this->fetchAll(
            'SELECT *
             FROM `' . self::RECORDS_TABLE . '`
             ORDER BY `created_at` DESC, `id` DESC'
        );

        return is_array($rows) ? $rows : [];
    }

    public function getRecent(int $limit = 50): array
    {
        if ($limit < 1) {
            throw new InvalidArgumentException(
                'Intelligence record limit must be greater than zero.'
            );
        }

        $rows = $this->fetchAll(
            'SELECT *
             FROM `' . self::RECORDS_TABLE . '`
             ORDER BY `created_at` DESC, `id` DESC
             LIMIT ' . $limit
        );

        return is_array($rows) ? $rows : [];
    }

    public function getByIntelligenceId(string $intelligenceId): ?array
    {
        $intelligenceId = trim($intelligenceId);

        if ($intelligenceId === '') {
            return null;
        }

        $row = $this->fetch(
            'SELECT *
             FROM `' . self::RECORDS_TABLE . '`
             WHERE `intelligence_id` = :intelligence_id
             LIMIT 1',
            [
                'intelligence_id' => $intelligenceId,
            ]
        );

        return is_array($row) ? $row : null;
    }

    public function intelligenceExists(string $intelligenceId): bool
    {
        return $this->getByIntelligenceId($intelligenceId) !== null;
    }

    public function createIntelligence(
        string $title,
        string $content,
        ?string $source = null,
        string $status = 'ACTIVE'
    ): string {
        $title = trim($title);
        $content = trim($content);
        $source = $source !== null ? trim($source) : null;
        $status = strtoupper(trim($status));

        if ($title === '' || $content === '' || $status === '') {
            throw new InvalidArgumentException(
                'Required intelligence fields are missing.'
            );
        }

        $allowedStatuses = [
            'ACTIVE',
            'WATCHING',
            'CLOSED',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                'Invalid intelligence status.'
            );
        }

        $intelligenceId = $this->nextIdentifier(
            self::RECORDS_TABLE,
            'intelligence_id',
            'INT'
        );

        $this->query(
            'INSERT INTO `' . self::RECORDS_TABLE . '`
                (
                    `intelligence_id`,
                    `title`,
                    `content`,
                    `origin`,
                    `source`,
                    `status`
                )
             VALUES
                (
                    :intelligence_id,
                    :title,
                    :content,
                    :origin,
                    :source,
                    :status
                )',
            [
                'intelligence_id' => $intelligenceId,
                'title' => $title,
                'content' => $content,
                'origin' => 'HUMAN',
                'source' => $source !== '' ? $source : null,
                'status' => $status,
            ]
        );

        return $intelligenceId;
    }

    public function reportIntelligence(
        string $intelligenceId,
        string $title,
        string $content,
        ?string $source = null,
        string $status = 'ACTIVE'
    ): string {
        $intelligenceId = strtoupper(trim($intelligenceId));
        $title = trim($title);
        $content = trim($content);
        $source = $source !== null ? trim($source) : null;
        $status = strtoupper(trim($status));

        if (
            $intelligenceId === ''
            || $title === ''
            || $content === ''
            || $status === ''
        ) {
            throw new InvalidArgumentException(
                'Required intelligence fields are missing.'
            );
        }

        if (
            preg_match(
                '/^INT-[A-Z0-9-]+$/',
                $intelligenceId
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                'Invalid intelligence identifier.'
            );
        }

        $allowedStatuses = [
            'ACTIVE',
            'WATCHING',
            'CLOSED',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                'Invalid intelligence status.'
            );
        }

        if ($this->intelligenceExists($intelligenceId)) {
            return $intelligenceId;
        }

        $this->query(
            'INSERT INTO `' . self::RECORDS_TABLE . '`
                (
                    `intelligence_id`,
                    `title`,
                    `content`,
                    `origin`,
                    `source`,
                    `status`
                )
             VALUES
                (
                    :intelligence_id,
                    :title,
                    :content,
                    :origin,
                    :source,
                    :status
                )',
            [
                'intelligence_id' => $intelligenceId,
                'title' => $title,
                'content' => $content,
                'origin' => 'RICTUS',
                'source' => $source !== '' ? $source : null,
                'status' => $status,
            ]
        );

        return $intelligenceId;
    }

    public function getObservations(string $intelligenceId): array
    {
        $rows = $this->fetchAll(
            'SELECT *
             FROM `' . self::OBSERVATIONS_TABLE . '`
             WHERE `intelligence_id` = :intelligence_id
             ORDER BY `created_at` ASC, `id` ASC',
            [
                'intelligence_id' => trim($intelligenceId),
            ]
        );

        return is_array($rows) ? $rows : [];
    }

    public function addObservation(
        string $observationId,
        string $intelligenceId,
        string $content,
        string $origin,
        ?string $source = null,
        ?string $observedAt = null
    ): void {
        $observationId = trim($observationId);
        $intelligenceId = trim($intelligenceId);
        $content = trim($content);
        $origin = strtoupper(trim($origin));
        $source = $source !== null ? trim($source) : null;

        if (
            $observationId === ''
            || $intelligenceId === ''
            || $content === ''
            || $origin === ''
        ) {
            throw new InvalidArgumentException(
                'Required observation fields are missing.'
            );
        }

        if (!$this->intelligenceExists($intelligenceId)) {
            throw new RuntimeException(
                'Parent intelligence record does not exist.'
            );
        }

        $this->query(
            'INSERT INTO `' . self::OBSERVATIONS_TABLE . '`
                (
                    `observation_id`,
                    `intelligence_id`,
                    `content`,
                    `origin`,
                    `source`,
                    `observed_at`
                )
             VALUES
                (
                    :observation_id,
                    :intelligence_id,
                    :content,
                    :origin,
                    :source,
                    :observed_at
                )',
            [
                'observation_id' => $observationId,
                'intelligence_id' => $intelligenceId,
                'content' => $content,
                'origin' => $origin,
                'source' => $source !== '' ? $source : null,
                'observed_at' => $observedAt,
            ]
        );
    }

    public function getAssignments(?string $status = null): array
    {
        if ($status !== null && trim($status) !== '') {
            $rows = $this->fetchAll(
                'SELECT *
                 FROM `' . self::ASSIGNMENTS_TABLE . '`
                 WHERE `status` = :status
                 ORDER BY `created_at` ASC, `id` ASC',
                [
                    'status' => strtoupper(trim($status)),
                ]
            );

            return is_array($rows) ? $rows : [];
        }

        $rows = $this->fetchAll(
            'SELECT *
             FROM `' . self::ASSIGNMENTS_TABLE . '`
             ORDER BY `created_at` DESC, `id` DESC'
        );

        return is_array($rows) ? $rows : [];
    }

    public function createAssignment(
        string $intelligenceId,
        string $assignmentType,
        ?string $instructions = null,
        ?int $createdBy = null
    ): string {
        $intelligenceId = trim($intelligenceId);
        $assignmentType = strtoupper(trim($assignmentType));
        $instructions = $instructions !== null
            ? trim($instructions)
            : null;

        if ($intelligenceId === '' || $assignmentType === '') {
            throw new InvalidArgumentException(
                'Required assignment fields are missing.'
            );
        }

        if (!$this->intelligenceExists($intelligenceId)) {
            throw new RuntimeException(
                'Parent intelligence record does not exist.'
            );
        }

        $assignmentId = $this->nextIdentifier(
            self::ASSIGNMENTS_TABLE,
            'assignment_id',
            'ASN'
        );

        $this->query(
            'INSERT INTO `' . self::ASSIGNMENTS_TABLE . '`
                (
                    `assignment_id`,
                    `intelligence_id`,
                    `assignment_type`,
                    `status`,
                    `instructions`,
                    `created_by`
                )
             VALUES
                (
                    :assignment_id,
                    :intelligence_id,
                    :assignment_type,
                    :status,
                    :instructions,
                    :created_by
                )',
            [
                'assignment_id' => $assignmentId,
                'intelligence_id' => $intelligenceId,
                'assignment_type' => $assignmentType,
                'status' => 'PENDING',
                'instructions' => $instructions !== ''
                    ? $instructions
                    : null,
                'created_by' => $createdBy,
            ]
        );

        return $assignmentId;
    }

    public function updateAssignmentStatus(
        string $assignmentId,
        string $status
    ): void {
        $assignmentId = trim($assignmentId);
        $status = strtoupper(trim($status));

        $allowed = [
            'PENDING',
            'ACKNOWLEDGED',
            'ACTIVE',
            'COMPLETED',
            'FAILED',
        ];

        if (
            $assignmentId === ''
            || !in_array($status, $allowed, true)
        ) {
            throw new InvalidArgumentException(
                'Invalid assignment status.'
            );
        }

        $acknowledgedAt = null;
        $completedAt = null;

        if ($status === 'ACKNOWLEDGED') {
            $acknowledgedAt = date('Y-m-d H:i:s');
        }

        if ($status === 'COMPLETED') {
            $completedAt = date('Y-m-d H:i:s');
        }

        $this->query(
            'UPDATE `' . self::ASSIGNMENTS_TABLE . '`
             SET
                `status` = :status,
                `acknowledged_at` =
                    COALESCE(:acknowledged_at, `acknowledged_at`),
                `completed_at` =
                    COALESCE(:completed_at, `completed_at`)
             WHERE `assignment_id` = :assignment_id',
            [
                'status' => $status,
                'acknowledged_at' => $acknowledgedAt,
                'completed_at' => $completedAt,
                'assignment_id' => $assignmentId,
            ]
        );
    }

    public function linkSrt(
        string $intelligenceId,
        string $srtId
    ): void {
        $intelligenceId = trim($intelligenceId);
        $srtId = trim($srtId);

        if ($intelligenceId === '' || $srtId === '') {
            throw new InvalidArgumentException(
                'Intelligence ID and SRT ID are required.'
            );
        }

        $this->query(
            'UPDATE `' . self::RECORDS_TABLE . '`
             SET `srt_id` = :srt_id
             WHERE `intelligence_id` = :intelligence_id',
            [
                'srt_id' => $srtId,
                'intelligence_id' => $intelligenceId,
            ]
        );
    }

    private function nextIdentifier(
        string $table,
        string $column,
        string $prefix
    ): string {
        $date = gmdate('Ymd');
        $pattern = $prefix . '-' . $date . '-%';

        $row = $this->fetch(
            'SELECT `' . $column . '`
             FROM `' . $table . '`
             WHERE `' . $column . '` LIKE :pattern
             ORDER BY `id` DESC
             LIMIT 1',
            [
                'pattern' => $pattern,
            ]
        );

        $sequence = 1;

        if (is_array($row)) {
            $current = (string) ($row[$column] ?? '');

            if (
                preg_match(
                    '/^' . preg_quote($prefix, '/') .
                    '-' . $date . '-([0-9]{3})$/',
                    $current,
                    $matches
                ) === 1
            ) {
                $sequence = ((int) $matches[1]) + 1;
            }
        }

        if ($sequence > 999) {
            throw new RuntimeException(
                'Daily identifier sequence exhausted.'
            );
        }

        return sprintf(
            '%s-%s-%03d',
            $prefix,
            $date,
            $sequence
        );
    }

    private function executeSqlFile(string $file): void
    {
        $sql = is_file($file)
            ? file_get_contents($file)
            : false;

        if (!is_string($sql) || trim($sql) === '') {
            throw new RuntimeException(
                'SQL file could not be read.'
            );
        }

        $statements = preg_split(
            '/;\s*(?:\r?\n|$)/',
            $sql
        );

        if (!is_array($statements)) {
            throw new RuntimeException(
                'SQL file could not be parsed.'
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
        try {
            $this->fetch(
                'SELECT 1 FROM `' . $table . '` LIMIT 1'
            );

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}

/* [End AI:GPT-5.6 Sol] */