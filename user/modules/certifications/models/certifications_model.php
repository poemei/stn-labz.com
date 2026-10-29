<?php
declare(strict_types=1);
/* [AI:GPT-6 | 2026-10-29 UTC] */
final class certifications_model extends model
{
    public function ready(): bool
    {
        return (bool) $this->fetch(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1',
            ['certifications_records']
        );
    }

    public function installSchema(): void
    {
        if ($this->ready()) {
            throw new RuntimeException('Certifications SQL is already installed.');
        }
        $file = __DIR__ . '/../sql/schema.sql';
        $sql = file_get_contents($file);
        if (!is_string($sql) || trim($sql) === '') {
            throw new RuntimeException('Missing Certifications schema.');
        }
        // schema.sql contains one CREATE TABLE statement and leading SQL comments.
        $this->query($sql);
    }

    public function accounts(): array
    {
        $records = $this->fetchAll('SELECT id, username, role, user_level FROM accounts ORDER BY username');
        return is_array($records) ? $records : [];
    }

    public function listRecords(): array
    {
        $records = $this->fetchAll(
            'SELECT c.*, a.username AS holder_name, a.role AS holder_role,
             a.user_level AS holder_level
             FROM certifications_records c JOIN accounts a ON a.id=c.account_id
             ORDER BY c.id DESC'
        );
        return is_array($records) ? $records : [];
    }

    public function issue(array $entry, int $issuer): string
    {
        $holder = (int) ($entry['account_id'] ?? 0);
        $account = $this->fetch('SELECT id FROM accounts WHERE id=?', [$holder]);
        if (!$account) {
            throw new InvalidArgumentException('Choose an existing account.');
        }
        $system = strtolower(trim((string) ($entry['system_slug'] ?? '')));
        $standard = trim((string) ($entry['standard_reference'] ?? ''));
        $evidence = trim((string) ($entry['evidence_reference'] ?? ''));
        $issued = trim((string) ($entry['issued_on'] ?? ''));
        $expires = trim((string) ($entry['expires_on'] ?? ''));
        if (!preg_match('/^[a-z][a-z0-9_-]{0,62}$/', $system)
            || $standard === '' || strlen($standard) > 255
            || $evidence === '' || strlen($evidence) > 255) {
            throw new InvalidArgumentException('System, standard and evidence are required.');
        }
        foreach ([$issued, $expires] as $date) {
            if ($date === '') { continue; }
            $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if (!$parsed || $parsed->format('Y-m-d') !== $date) {
                throw new InvalidArgumentException('Use valid YYYY-MM-DD dates.');
            }
        }
        if ($issued === '' || ($expires !== '' && $expires < $issued)) {
            throw new InvalidArgumentException('Issue date required; expiry must follow issuance.');
        }
        // 128 random bits from a cryptographically secure source; mixed-case
        // alphabet plus decimal digits. Check collisions at database UNIQUE key.
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        for ($attempt = 0; $attempt < 4; $attempt++) {
            $token = '';
            for ($i = 0; $i < 24; $i++) {
                $token .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            if (!preg_match('/[a-z]/', $token) || !preg_match('/[A-Z]/', $token)
                || !preg_match('/[0-9]/', $token)) { continue; }
            $id = 'certificat-0x-' . $token;
            if ($this->fetch('SELECT id FROM certifications_records WHERE certificate_id=?', [$id])) {
                continue;
            }
            $this->query(
                'INSERT INTO certifications_records
                 (certificate_id,account_id,system_slug,standard_reference,evidence_reference,issued_by,issued_on,expires_on,status)
                 VALUES (?,?,?,?,?,?,?,?,?)',
                [$id, $holder, $system, $standard, $evidence, $issuer, $issued, $expires ?: null, 'active']
            );
            return $id;
        }
        throw new RuntimeException('Certificate ID generation failed.');
    }

    public function setStatus(string $id, string $status): void
    {
        if (!in_array($status, ['active', 'suspended', 'revoked'], true)
            || !preg_match('/^certificat-0x-[A-Za-z0-9]{24}$/', $id)) {
            throw new InvalidArgumentException('Invalid certificate status change.');
        }
        $this->query('UPDATE certifications_records SET status=? WHERE certificate_id=?', [$status, $id]);
    }
}
/* [End AI:GPT-6] */
