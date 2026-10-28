<?php
declare(strict_types=1);

// path: /user/modules/sentinel/models/sentinel_model.php

class sentinel_model extends model
{
    private const API_INTEL = 'https://api.stn-labz.com/v1/intel';
    private const API_THREATS = 'https://api.stn-labz.com/v1/threats';

    private const SCHEMA_VERSION = '1.9.0';

    public function databaseState(): string
    {
        try {
            foreach (['sentinel', 'sentinel_config'] as $table) {
                if (!$this->tableExistsForLifecycle($table)) {
                    return 'missing';
                }
            }
            if (!$this->tableExistsForLifecycle('sentinel_schema')) {
                return $this->legacySchemaValid() ? 'update' : 'invalid';
            }
            $row = $this->query('SELECT schema_version FROM sentinel_schema WHERE id = 1')->fetch();
            if (!is_array($row)) {
                return 'invalid';
            }
            if ((string) $row['schema_version'] !== self::SCHEMA_VERSION) {
                return 'invalid';
            }
            return $this->hasUniqueSettingKey() ? 'current' : 'invalid';
        } catch (Throwable $exception) {
            error_log('Sentinel database lifecycle detection failed: ' . get_class($exception));
            return 'invalid';
        }
    }

    private function tableExistsForLifecycle(string $table): bool
    {
        return (bool) $this->query(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1',
            [DB_NAME, $table]
        )->fetch();
    }

    private function configColumns(): array
    {
        $rows = $this->query(
            'SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ?',
            [DB_NAME, 'sentinel_config']
        )->fetchAll();
        return array_map(static fn(array $row): string => (string) $row['column_name'], $rows);
    }

    private function legacySchemaValid(): bool
    {
        $columns = $this->configColumns();
        return count(array_diff(['id', 'setting_key', 'setting_value'], $columns)) === 0;
    }

    private function hasUniqueSettingKey(): bool
    {
        $rows = $this->query(
            "SELECT index_name FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = 'sentinel_config'
             AND column_name = 'setting_key' AND non_unique = 0",
            [DB_NAME]
        )->fetchAll();
        return count($rows) > 0;
    }

    /**
     * Perimeter availability is independent of Admin migration state.
     * A pending schema update must never disable existing enforcement.
     */
    public function schema_available(): bool
    {
        try {
            return $this->tableExistsForLifecycle('sentinel')
                && $this->tableExistsForLifecycle('sentinel_config');
        } catch (Throwable $exception) {
            return false;
        }
    }

    private function executeLifecycleSql(string $relative): void
    {
        $path = dirname(__DIR__) . '/sql/' . $relative;
        $sql = @file_get_contents($path);
        if (!is_string($sql)) {
            throw new RuntimeException('Sentinel lifecycle SQL unavailable.');
        }
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            $this->query($statement);
        }
    }

    public function install_schema(): void
    {
        if ($this->databaseState() !== 'missing') {
            throw new RuntimeException('Sentinel schema installation requires missing state.');
        }
        $this->executeLifecycleSql('schema.sql');
        if ($this->databaseState() !== 'current') {
            throw new RuntimeException('Sentinel schema installation could not be verified.');
        }
    }

    /**
     * Explicit legacy-to-1.9.0 schema migration.
     * Preserve highest-id setting as effective value; backup before deleting.
     * Never invoked by request inspection or intelligence synchronization.
     */
    public function update_schema(): void
    {
        if ($this->databaseState() !== 'update') {
            throw new RuntimeException('No compatible Sentinel schema update is pending.');
        }
        // Stop before any destructive operation if another migration backup exists.
        if ($this->tableExistsForLifecycle('sentinel_config_pre_dedupe_backup')) {
            throw new RuntimeException('A prior config backup exists; inspect it before retrying migration.');
        }
        $this->executeLifecycleSql('patches/legacy-to-1.9.0.sql');
        if (!$this->hasUniqueSettingKey()) {
            $this->query('ALTER TABLE sentinel_config ADD UNIQUE KEY sentinel_config_setting_key_unique (setting_key)');
        }
        $this->query("CREATE TABLE IF NOT EXISTS sentinel_schema (
            id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
            schema_version VARCHAR(64) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->query(
            'INSERT INTO sentinel_schema (id, schema_version) VALUES (1, ?) ON DUPLICATE KEY UPDATE schema_version = ?',
            [self::SCHEMA_VERSION, self::SCHEMA_VERSION]
        );
        if ($this->databaseState() !== 'current') {
            throw new RuntimeException('Sentinel SQL update verification failed.');
        }
    }

    /**
     * Whitelist is local enforcement policy stored in sentinel_config.
     * It does not alter or delete threat intelligence.
     */
    public function get_whitelist(): array
    {
        $row = $this->query(
            'SELECT setting_value FROM sentinel_config WHERE setting_key = ? ORDER BY id DESC LIMIT 1',
            ['whitelist_ips']
        )->fetch();
        $items = json_decode((string) ($row['setting_value'] ?? '[]'), true);
        if (!is_array($items)) {
            return [];
        }
        $ips = [];
        foreach ($items as $ip) {
            if (is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP)) {
                $ips[$ip] = $ip;
            }
        }
        return array_values($ips);
    }

    public function is_whitelisted(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false
            && in_array($ip, $this->get_whitelist(), true);
    }

    public function whitelist_add(string $ip): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid IP address.');
        }
        $ips = $this->get_whitelist();
        if (!in_array($ip, $ips, true)) {
            $ips[] = $ip;
            $this->write_whitelist($ips);
        }
    }

    public function whitelist_remove(string $ip): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid IP address.');
        }
        $this->write_whitelist(array_values(array_filter(
            $this->get_whitelist(),
            static fn(string $item): bool => $item !== $ip
        )));
    }

    private function write_whitelist(array $ips): void
    {
        $json = json_encode(array_values($ips), JSON_THROW_ON_ERROR);
        $this->write_setting('whitelist_ips', $json);
    }

    public function is_blocked(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        return (bool) $this->query(
            "SELECT id FROM sentinel WHERE ip_address = ? AND status = 'blocked' LIMIT 1",
            [$ip]
        )->fetch();
    }

    public function block_ip(string $ip): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid IP address.');
        }
        $this->query("UPDATE sentinel SET status = 'blocked' WHERE ip_address = ?", [$ip]);
        $this->query(
            "INSERT IGNORE INTO sentinel (ip_address, threat_category, status) VALUES (?, 'auto_block', 'blocked')",
            [$ip]
        );
    }

    /**
     * The deployed legacy configuration table has an auto-increment id and
     * historically permitted duplicate setting keys. Last row wins until
     * the separate one-time database migration is performed.
     */
    public function get_config_map(): array
    {
        $rows = $this->query('SELECT setting_key, setting_value FROM sentinel_config ORDER BY id ASC')->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['setting_key']] = $row['setting_value'];
        }
        if (empty($map['site_id'])) {
            $map['site_id'] = 'site_' . bin2hex(random_bytes(8));
            $this->save_config(['site_id' => $map['site_id']]);
        }
        return $map;
    }

    public function save_config(array $settings): void
    {
        $allowed = ['site_id', 'api_key', 'httpbl_key', 'malicious_patterns', 'last_sync_time'];
        foreach ($settings as $key => $value) {
            if (!in_array($key, $allowed, true) || !is_scalar($value)) {
                continue;
            }
            $value = (string) $value;
            if (in_array($key, ['api_key', 'httpbl_key'], true) && $value === '') {
                continue;
            }
            if ($key === 'httpbl_key' && !preg_match('/\\A[a-z]{12}\\z/', $value)) {
                throw new InvalidArgumentException('Invalid Project Honey Pot access key.');
            }
            if ($key === 'site_id' && !preg_match('/\\A[A-Za-z0-9][A-Za-z0-9._-]{2,63}\\z/', $value)) {
                throw new InvalidArgumentException('Invalid site ID.');
            }
            $this->write_setting($key, $value);
        }
    }

    /**
     * Update an existing setting in place, without growing the table.
     * On legacy databases update every duplicate to the same value until
     * the unique-key migration safely removes historical duplicates.
     */
    private function write_setting(string $key, string $value): void
    {
        $existing = $this->query(
            'SELECT setting_value FROM sentinel_config WHERE setting_key = ? LIMIT 1',
            [$key]
        )->fetch();
        if ($existing !== false && $existing !== null) {
            if ((string) ($existing['setting_value'] ?? '') !== $value) {
                $this->query(
                    'UPDATE sentinel_config SET setting_value = ? WHERE setting_key = ?',
                    [$value, $key]
                );
            }
            return;
        }
        $this->query(
            'INSERT INTO sentinel_config (setting_key, setting_value) VALUES (?, ?)',
            [$key, $value]
        );
    }

    public function get_recent_events(): array
    {
        return $this->query('SELECT * FROM sentinel ORDER BY timestamp DESC LIMIT 100')->fetchAll();
    }

    public function delete_data(): void
    {
        $this->query('DELETE FROM sentinel');
    }

    /**
     * No canonical threat reference records are shipped with Sentinel.
     * Reset clears only mutable local event records, leaving configuration,
     * credentials and the installed schema intact.
     */
    public function reset_data(): void
    {
        $this->delete_data();
    }

    public function pull_global_intelligence(): array
    {
        $config = $this->get_config_map();
        if (empty($config['api_key'])) {
            throw new RuntimeException('STN-LABZ API key is not configured.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL support is unavailable.');
        }
        $ch = curl_init(self::API_INTEL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['X-API-KEY: ' . $config['api_key']],
            CURLOPT_TIMEOUT => 3,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        unset($ch);
        if (!is_string($response)) {
            error_log('Sentinel API sync transport failure: ' . $curlError);
            throw new RuntimeException('STN-LABZ API connection failed. Check the server error log.');
        }
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('STN-LABZ API returned HTTP ' . $status . '.');
        }
        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new RuntimeException('STN-LABZ API returned invalid JSON.');
        }
        $patterns = is_array($data['patterns'] ?? null) ? $data['patterns'] : [];
        if (is_array($data['stats']['by_type'] ?? null)) {
            $patterns = array_merge($patterns, array_keys($data['stats']['by_type']));
        }
        $existing = array_filter(array_map('trim', explode(',', (string) ($config['malicious_patterns'] ?? ''))));
        $patterns = array_filter($patterns, 'is_string');
        $merged = array_unique(array_merge($existing, $patterns));
        if ($merged) {
            $this->save_config(['malicious_patterns' => implode(',', $merged)]);
        }
        if (is_array($data['blocklists']['ips'] ?? null)) {
            foreach ($data['blocklists']['ips'] as $ip) {
                if (!is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
                    continue;
                }
                $existingIp = $this->query('SELECT id FROM sentinel WHERE ip_address = ? LIMIT 1', [$ip])->fetch();
                if (!$existingIp) {
                    $this->query(
                        "INSERT IGNORE INTO sentinel (ip_address, threat_category, status) VALUES (?, 'global_sync', 'blocked')",
                        [$ip]
                    );
                }
            }
        }
        $this->save_config(['last_sync_time' => (string) time()]);
        return $data;
    }

    /** Optional plugin capability; no effect on local enforcement or API telemetry. */
    public function lookup_httpbl(string $ip): array
    {
        $file = dirname(__DIR__) . '/plugins/sentinel_plugins.php';
        if (!is_file($file) || is_link($file)) {
            throw new RuntimeException('Sentinel plugin registry unavailable.');
        }
        require_once $file;
        return sentinel_plugins::lookup('httpbl', $ip, $this->get_config_map());
    }

    public function log_threat(string $ip, string $category, string $url, ?int $responseStatus = null): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return;
        }
        $this->query(
            "INSERT IGNORE INTO sentinel (ip_address, threat_category, request_url, status) VALUES (?, ?, ?, 'blocked')",
            [$ip, $category, substr($url, 0, 2048)]
        );
        $config = $this->get_config_map();
        if (empty($config['api_key']) || !function_exists('curl_init')) {
            return;
        }
        $details = [
            'request_url' => $url,
            'ip_address' => $ip,
            'domain' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        ];
        // Only a response determined by Sentinel itself may be reported.
        // Allowed requests' eventual application status remains unknown.
        if ($responseStatus === 403) {
            $details['response_status'] = 403;
        }
        $payload = json_encode([
            'source' => 'sentinel',
            'site_id' => $config['site_id'],
            'type' => $category,
            'details' => $details,
        ]);
        $ch = curl_init(self::API_THREATS);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-KEY: ' . $config['api_key'],
            ],
            CURLOPT_TIMEOUT => 2,
        ]);
        curl_exec($ch);
        unset($ch);
    }
}
