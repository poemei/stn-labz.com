<?php
declare(strict_types=1);

/** Security module persistence and inert module discovery. */
/* [AI:GPT-5.6 Sol | 2026-09-02 UTC] */
final class security_model extends model
{
    private const SETTINGS_TABLE = 'security_settings';

    public function databaseState(): string
    {
        return $this->tableExists(self::SETTINGS_TABLE) ? 'ready' : 'missing';
    }

    public function installSchema(): void
    {
        $file = __DIR__ . '/../sql/schema.sql';
        if (!is_file($file)) {
            throw new RuntimeException('Security schema.sql was not found.');
        }
        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException('Security schema.sql could not be read.');
        }
        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql);
        if (!is_array($statements)) {
            throw new RuntimeException('Security schema.sql could not be parsed.');
        }
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement !== '') {
                $this->query($statement);
            }
        }
    }

    public function getSettings(): array
    {
        $record = $this->fetch('SELECT * FROM `security_settings` WHERE `id` = :id LIMIT 1', ['id' => 1]);
        if (!is_array($record)) {
            return $this->emptySettings();
        }
        return [
            'pgp_label' => (string) ($record['pgp_label'] ?? ''),
            'pgp_public_key' => (string) ($record['pgp_public_key'] ?? ''),
            'rsa_label' => (string) ($record['rsa_label'] ?? ''),
            'rsa_public_key' => (string) ($record['rsa_public_key'] ?? ''),
            'security_txt_url' => (string) ($record['security_txt_url'] ?? ''),
        ];
    }

    public function saveSettings(array $settings): void
    {
        $this->query(
            'INSERT INTO `security_settings` (`id`, `pgp_label`, `pgp_public_key`, `rsa_label`, `rsa_public_key`, `security_txt_url`) '
            . 'VALUES (:id, :pgp_label, :pgp_public_key, :rsa_label, :rsa_public_key, :security_txt_url) '
            . 'ON DUPLICATE KEY UPDATE `pgp_label` = VALUES(`pgp_label`), '
            . '`pgp_public_key` = VALUES(`pgp_public_key`), `rsa_label` = VALUES(`rsa_label`), '
            . '`rsa_public_key` = VALUES(`rsa_public_key`), '
            . '`security_txt_url` = VALUES(`security_txt_url`)',
            ['id' => 1] + $settings
        );
    }

    public function deleteData(): void
    {
        $this->query('DELETE FROM `security_settings`');
    }

    /**
     * Mirror bootstrap's optional Sentinel perimeter integration check.
     *
     * Detection remains inert here: bootstrap owns loading and execution.
     */
    public function isSentinelInstalled(): bool
    {
        $sentinelModule = USERROOT . '/modules/sentinel';
        $sentinelPerimeter = $sentinelModule . '/lib/sentinel_perimeter.php';
        $sentinelModuleRoot = is_link($sentinelModule)
            ? false
            : realpath($sentinelModule);
        $sentinelPerimeterFile = is_link($sentinelPerimeter)
            ? false
            : realpath($sentinelPerimeter);

        return $sentinelModuleRoot !== false
            && $sentinelPerimeterFile !== false
            && str_starts_with(
                $sentinelPerimeterFile,
                $sentinelModuleRoot . DIRECTORY_SEPARATOR
            )
            && is_file($sentinelPerimeterFile);
    }

    private function emptySettings(): array
    {
        return [
            'pgp_label' => '', 'pgp_public_key' => '',
            'rsa_label' => '', 'rsa_public_key' => '',
            'security_txt_url' => '/.well-known/security.txt',
        ];
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->fetch(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = :schema AND table_name = :table_name LIMIT 1',
            ['schema' => DB_NAME, 'table_name' => $table]
        );
    }
}
/* [End AI:GPT-5.6 Sol] */
