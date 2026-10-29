<?php

declare(strict_types=1);

/* [AI:GPT-6 | 2026-10-29 04:17:13 UTC] */
/** Core-owned organization records, with explicit additive SQL installation. */
class organization_model extends model
{
    // PHP-request-local only; never shared across requests or logins.
    private array $authorizationCache = [];

    public function ready(): bool
    {
        return ($this->authorizationCache['schema'] ??= $this->schemaState()) === 'ready';
    }

    private function schemaState(): string
    {
        $found = 0;
        foreach (['organization_positions', 'organization_credentials', 'organization_missions'] as $table) {
            if ($this->fetch('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?', [$table])) {
                $found++;
            }
        }
        return $found === 3 ? 'ready' : ($found === 0 ? 'missing' : 'partial');
    }

    public function install(): void
    {
        $sql = file_get_contents(APPROOT . '/install/organization.sql');
        if ($sql === false) {
            throw new RuntimeException('Organization schema is unavailable.');
        }
        foreach (explode(';', $sql) as $statement) {
            if (trim($statement) !== '') {
                $this->query($statement);
            }
        }
    }

    public function account(int $id): ?array
    {
        $row = $this->fetch('SELECT id, username, user_level, role FROM accounts WHERE id = ?', [$id]);
        return is_array($row) ? $row : null;
    }

    public function tier(int $id): int
    {
        if (array_key_exists($id, $this->authorizationCache['tier'] ?? [])) {
            return $this->authorizationCache['tier'][$id];
        }
        $account = $this->account($id);
        if ($account === null) {
            return 0;
        }
        if ((int) $account['user_level'] >= 9) {
            return $this->authorizationCache['tier'][$id] = 9;
        }
        $state = $this->authorizationCache['schema'] ??= $this->schemaState();
        if ($state === 'partial') {
            return $this->authorizationCache['tier'][$id] = 0;
        }
        if ($state === 'ready') {
            $position = $this->fetch('SELECT tier FROM organization_positions WHERE account_id = ?', [$id]);
            if ($position) {
                $tier = (int) $position['tier'];
                return $this->authorizationCache['tier'][$id] = ($tier >= 1 && $tier <= 8 ? $tier : 0);
            }
        }
        return $this->authorizationCache['tier'][$id] = (in_array((int) $account['user_level'], [7, 8], true) ? (int) $account['user_level'] : 0);
    }

    public function records(int $id): array
    {
        return $this->authorizationCache['records'][$id] ??= [
            'position' => $this->fetch('SELECT * FROM organization_positions WHERE account_id = ?', [$id]),
            'credentials' => $this->fetchAll('SELECT * FROM organization_credentials WHERE account_id = ? ORDER BY id', [$id]),
            'missions' => $this->fetchAll('SELECT * FROM organization_missions WHERE account_id = ? ORDER BY id', [$id]),
        ];
    }

    /**
     * [AI:GPT-6 | 2026-10-29 UTC]
     * Read active certificates from the Certifications user module as an
     * additional credential source. Certificate holder is the account ID;
     * expiry is checked at the time of each authorization evaluation.
     * A certificate alone cannot authorize a module without a mission.
     */
    private function certificateCredentials(int $accountId, string $today): array
    {
        $key = $accountId . ':' . $today;
        if (isset($this->authorizationCache['certificates'][$key])) {
            return $this->authorizationCache['certificates'][$key];
        }
        $exists = $this->fetch(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1',
            ['certifications_records']
        );
        if (!$exists) {
            return $this->authorizationCache['certificates'][$key] = [];
        }
        $rows = $this->fetchAll(
            "SELECT system_slug, standard_reference, evidence_reference, expires_on
             FROM certifications_records
             WHERE account_id = ? AND status = 'active'
               AND issued_on <= ?
               AND (expires_on IS NULL OR expires_on >= ?)",
            [$accountId, $today, $today]
        );
        $credentials = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $credentials[] = [
                'kind' => 'certification',
                'system_slug' => $row['system_slug'],
                'status' => 'active',
                'expires_on' => $row['expires_on'],
                'qualification_tier' => null,
            ];
        }
        return $this->authorizationCache['certificates'][$key] = $credentials;
    }
    /* [End AI:GPT-6] */

    public function permits(int $id, string $module, ?string $system = null): bool
    {
        $key = json_encode([$id, $module, $system]);
        if (array_key_exists($key, $this->authorizationCache['permits'] ?? [])) {
            return $this->authorizationCache['permits'][$key];
        }
        $tier = $this->tier($id);
        if ($tier === 9) {
            return $this->authorizationCache['permits'][$key] = true;
        }
        if ($tier === 0 || !admin_access::isAssignable($module)) {
            return $this->authorizationCache['permits'][$key] = false;
        }
        if ($module === 'support' && !organization_policy::supportAction($tier, 'read')) {
            return $this->authorizationCache['permits'][$key] = false;
        }
        if ($this->ready()) {
            $records = $this->records($id);
            $today = gmdate('Y-m-d');
            $credentials = array_merge($records['credentials'], $this->certificateCredentials($id, $today));
            if (organization_policy::permits($tier, $records['missions'], $credentials, $module, $system, $today)) {
                return true;
            }
        }
        // Retain legacy allowance records, but never use them to bypass an
        // explicit mission or required current credentials.
        return $this->authorizationCache['permits'][$key] = false;
    }

    /** Read-only authority trace for a Tier 9 account inspector. */
    public function inspectSupportAuthority(int $id): array
    {
        $account = $this->account($id);
        $state = $this->schemaState();
        $result = [
            'account_found' => $account !== null,
            'account_level' => (int) ($account['user_level'] ?? 0),
            'schema' => $state,
            'position_tier' => null,
            'effective_tier' => $this->tier($id),
            'support_missions' => [],
            'certification_systems' => [],
            'support_permitted' => false,
        ];
        if ($account === null || $state !== 'ready') {
            return $result;
        }
        $position = $this->fetch('SELECT tier FROM organization_positions WHERE account_id = ?', [$id]);
        $result['position_tier'] = $position === false ? null : (int) $position['tier'];
        $today = gmdate('Y-m-d');
        $records = $this->records($id);
        foreach ($records['missions'] as $mission) {
            if ((string) $mission['module_slug'] === 'support') {
                $result['support_missions'][] = [
                    'system' => (string) $mission['system_slug'],
                    'active' => (bool) $mission['active'],
                    'expires_on' => $mission['expires_on'],
                ];
            }
        }
        foreach (array_merge($records['credentials'], $this->certificateCredentials($id, $today)) as $credential) {
            if (($credential['kind'] ?? '') === 'certification') {
                $result['certification_systems'][] = [
                    'system' => (string) $credential['system_slug'],
                    'status' => (string) $credential['status'],
                    'expires_on' => $credential['expires_on'],
                ];
            }
        }
        $result['support_permitted'] = $this->permits($id, 'support');
        return $result;
    }

    /** Tier 9 is the existing assignment authority; validate it again on writes. */
    public function change(int $actor, int $id, array $input, array $modules): void
    {
        if ($this->tier($actor) !== 9 || $this->account($id) === null || !$this->ready()) {
            throw new InvalidArgumentException('Organization management is unavailable or unauthorized.');
        }
        $action = (string) ($input['action'] ?? '');
        if ($action === 'position') {
            $tier = filter_var($input['tier'] ?? null, FILTER_VALIDATE_INT);
            // Existing account classification remains the only TSC grant path.
            if (!isset(organization_policy::TIERS[$tier]) || $tier === 9 || $this->tier($id) === 9) {
                throw new InvalidArgumentException('Choose a position from tiers 1–8 for a non-administrator account.');
            }
            $name = trim((string) ($input['position_name'] ?? ''));
            if ($name === '' || strlen($name) > 150) {
                throw new InvalidArgumentException('A position name of at most 150 bytes is required.');
            }
            $this->query('INSERT INTO organization_positions (account_id,tier,position_name,changed_by) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE tier=VALUES(tier),position_name=VALUES(position_name),changed_by=VALUES(changed_by)', [$id, $tier, $name, $actor]);
            return;
        }
        if (in_array($action, ['withdraw', 'credential_status'], true)) {
            $record = filter_var($input['record_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$record || $record < 1) {
                throw new InvalidArgumentException('Invalid record.');
            }
            if ($action === 'withdraw') {
                $this->query('UPDATE organization_missions SET active=0,changed_by=? WHERE id=? AND account_id=?', [$actor, $record, $id]);
            } else {
                $status = (string) ($input['status'] ?? '');
                if (!in_array($status, ['pending', 'active', 'suspended', 'expired', 'revoked'], true)) {
                    throw new InvalidArgumentException('Invalid credential status.');
                }
                $this->query('UPDATE organization_credentials SET status=?,changed_by=? WHERE id=? AND account_id=?', [$status, $actor, $record, $id]);
            }
            return;
        }
        if (!in_array($action, ['credential', 'mission'], true)) {
            throw new InvalidArgumentException('Invalid organization action.');
        }
        $system = trim((string) ($input['system_slug'] ?? ''));
        if (!preg_match('/^[a-z][a-z0-9_-]{0,62}$/', $system)) {
            throw new InvalidArgumentException('A valid system identifier is required.');
        }
        $expires = trim((string) ($input['expires_on'] ?? ''));
        if ($expires !== '') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $expires);
            if (!$date || $date->format('Y-m-d') !== $expires) {
                throw new InvalidArgumentException('Invalid expiration date.');
            }
        }
        if ($action === 'credential') {
            $kind = (string) ($input['kind'] ?? '');
            $standard = trim((string) ($input['standard_reference'] ?? ''));
            $evidence = trim((string) ($input['evidence_reference'] ?? ''));
            if (!in_array($kind, ['certification', 'qualification'], true) || $standard === '' || $evidence === '' || strlen($standard) > 255 || strlen($evidence) > 255) {
                throw new InvalidArgumentException('Credential kind, approved standard, and evidence references are required.');
            }
            $qualificationTier = $kind === 'qualification' ? (int) ($input['qualification_tier'] ?? 0) : null;
            if ($kind === 'qualification' && ($qualificationTier < 1 || $qualificationTier > 8)) {
                throw new InvalidArgumentException('Choose the responsibility tier demonstrated by this qualification.');
            }
            $this->query('INSERT INTO organization_credentials (account_id,kind,qualification_tier,system_slug,standard_reference,evidence_reference,expires_on,changed_by) VALUES (?,?,?,?,?,?,?,?)', [$id, $kind, $qualificationTier, $system, $standard, $evidence, $expires ?: null, $actor]);
            return;
        }
        $module = (string) ($input['module_slug'] ?? '');
        $reference = trim((string) ($input['mission_reference'] ?? ''));
        if (!in_array($module, $modules, true) || !admin_access::isAssignable($module) || $reference === '' || strlen($reference) > 255) {
            throw new InvalidArgumentException('An installed assignable module and mission authority reference are required.');
        }
        $this->query('INSERT INTO organization_missions (account_id,module_slug,system_slug,mission_reference,expires_on,changed_by) VALUES (?,?,?,?,?,?)', [$id, $module, $system, $reference, $expires ?: null, $actor]);
    }
}
/* [End AI:GPT-6] */
