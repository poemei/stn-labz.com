<?php

declare(strict_types=1);

/**
 * Letters Model
 *
 * Path: /user/modules/letters/models/letters_model.php
 */

/* [AI:GPT-5.6 | 2026-09-01 02:15:00 UTC] */
final class letters_model extends model
{
    private const LETTERS_TABLE = 'letters';
    private const SUBSCRIBERS_TABLE = 'letters_subscribers';

    public function databaseState(): string
    {
        if (!$this->tableExists(self::LETTERS_TABLE)) {
            return 'missing';
        }

        if (
            !$this->tableExists(self::SUBSCRIBERS_TABLE)
            || !$this->columnExists(self::LETTERS_TABLE, 'recipient_count')
        ) {
            return 'update';
        }

        return 'ready';
    }

    /**
     * Install the current module schema from sql/schema.sql.
     */
    public function installSql(): void
    {
        if ($this->databaseState() !== 'missing') {
            throw new RuntimeException('Letters database installation is not required.');
        }

        $this->applySqlFile(
            dirname(__DIR__) . '/sql/schema.sql'
        );

        if ($this->databaseState() !== 'ready') {
            throw new RuntimeException('Letters database installation did not complete.');
        }
    }

    /**
     * Apply the legacy-to-current SQL update packaged with the module.
     *
     * The 1.0.0 and 1.0.1 migrations to 1.1.1 are intentionally
     * identical, so schema inspection is sufficient for this legacy
     * update state.
     */
    public function updateSql(): void
    {
        if ($this->databaseState() !== 'update') {
            throw new RuntimeException('Letters database update is not required.');
        }

        $this->applySqlFile(
            dirname(__DIR__) . '/sql/patches/1.0.0-to-1.1.1.sql'
        );

        if ($this->databaseState() !== 'ready') {
            throw new RuntimeException('Letters database update did not complete.');
        }
    }

    /** @param array{name: string, email: string} $subscription */
    public function subscribe(array $subscription): void
    {
        $this->query(
            'INSERT INTO `letters_subscribers`
                (`name`, `email`, `unsubscribe_token`, `status`, `consent_at`)
             VALUES (:name, :email, :token, \'active\', CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`),
                `unsubscribe_token` = VALUES(`unsubscribe_token`),
                `status` = \'active\',
                `consent_at` = CURRENT_TIMESTAMP,
                `unsubscribed_at` = NULL',
            [
                'name' => $subscription['name'],
                'email' => $subscription['email'],
                'token' => bin2hex(random_bytes(32)),
            ]
        );
    }

    /** @return array<string, mixed>|false */
    public function getSubscriberByToken(string $token)
    {
        return $this->fetch(
            'SELECT `id`, `name`, `email`, `status` '
            . 'FROM `letters_subscribers` '
            . 'WHERE `unsubscribe_token` = :token LIMIT 1',
            ['token' => $token]
        );
    }

    public function unsubscribeByToken(string $token): void
    {
        $this->query(
            'UPDATE `letters_subscribers` '
            . 'SET `status` = \'unsubscribed\', '
            . '`unsubscribed_at` = CURRENT_TIMESTAMP '
            . 'WHERE `unsubscribe_token` = :token',
            ['token' => $token]
        );
    }

    /** @param array{subject: string, body: string} $letter */
    public function createLetter(array $letter): int
    {
        return (int) $this->insert(self::LETTERS_TABLE, [
            'subject' => $letter['subject'],
            'body' => $letter['body'],
            'recipient_count' => 0,
        ]);
    }

    public function setRecipientCount(int $id, int $recipientCount): void
    {
        $this->update(
            self::LETTERS_TABLE,
            ['recipient_count' => $recipientCount],
            'id = :record_id',
            ['record_id' => $id]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function getLetters(): array
    {
        return $this->fetchAll('SELECT * FROM `letters` ORDER BY `id` DESC');
    }

    public function deleteLetter(int $id): void
    {
        $this->query('DELETE FROM `letters` WHERE `id` = :id', ['id' => $id]);
    }

    /** @return array<int, array<string, mixed>> */
    public function getSubscribers(): array
    {
        return $this->fetchAll(
            'SELECT * FROM `letters_subscribers` '
            . 'ORDER BY `consent_at` DESC, `id` DESC'
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function getActiveSubscribers(): array
    {
        return $this->fetchAll(
            'SELECT `name`, `email`, `unsubscribe_token` '
            . 'FROM `letters_subscribers` '
            . 'WHERE `status` = :status ORDER BY `id`',
            ['status' => 'active']
        );
    }

    public function countActiveSubscribers(): int
    {
        $record = $this->fetch(
            'SELECT COUNT(*) AS `total` FROM `letters_subscribers` '
            . 'WHERE `status` = :status',
            ['status' => 'active']
        );

        return is_array($record) ? (int) $record['total'] : 0;
    }

    public function setSubscriberActive(int $id, bool $active): void
    {
        $this->query(
            'UPDATE `letters_subscribers` '
            . 'SET `status` = :status, `unsubscribed_at` = '
            . ($active ? 'NULL' : 'CURRENT_TIMESTAMP')
            . ' WHERE `id` = :id',
            ['status' => $active ? 'active' : 'unsubscribed', 'id' => $id]
        );
    }

    public function deleteSubscriber(int $id): void
    {
        $this->query(
            'DELETE FROM `letters_subscribers` WHERE `id` = :id',
            ['id' => $id]
        );
    }

    /**
     * Execute a module-owned SQL lifecycle file statement by statement.
     */
    private function applySqlFile(string $file): void
    {
        if (!is_file($file)) {
            throw new RuntimeException('Letters SQL lifecycle file not found.');
        }

        $sql = file_get_contents($file);

        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException('Letters SQL lifecycle file is empty.');
        }

        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            static fn (string $statement): bool => $statement !== ''
                && preg_match('/^\s*\/\*.*?\*\/\s*$/s', $statement) !== 1
        );

        foreach ($statements as $statement) {
            $this->query($statement);
        }
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->fetch(
            'SELECT 1 FROM information_schema.tables '
            . 'WHERE table_schema = :schema AND table_name = :table_name LIMIT 1',
            ['schema' => DB_NAME, 'table_name' => $table]
        );
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->fetch(
            'SELECT 1 FROM information_schema.columns '
            . 'WHERE table_schema = :schema '
            . 'AND table_name = :table_name '
            . 'AND column_name = :column_name LIMIT 1',
            ['schema' => DB_NAME, 'table_name' => $table, 'column_name' => $column]
        );
    }

}
/* [End AI:GPT-5.6] */
