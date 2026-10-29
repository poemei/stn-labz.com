<?php

declare(strict_types=1);

/* [AI:GPT-6 | 2026-10-29 04:17:13 UTC] */
// Run: php -d extension=pdo_sqlite tests/organization_access.php
// Uses real policy/controllers/models with an isolated SQLite transport.
// MySQL installation DDL and SMTP delivery require deployment-environment tests.
if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    fwrite(STDERR, "Enable PDO SQLite to run these isolated regression tests.\n");
    exit(1);
}
define('APPROOT', dirname(__DIR__) . '/app');
define('USERROOT', dirname(__DIR__) . '/user');
define('URLROOT', 'https://example.invalid');
define('DB_NAME', 'test');
$database = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$database->sqliteCreateFunction('DATABASE', static fn () => 'test');
$database->exec("ATTACH DATABASE ':memory:' AS information_schema");
$database->exec('CREATE TABLE information_schema.tables (table_schema TEXT, table_name TEXT)');
foreach ([
    'accounts' => 'id INTEGER PRIMARY KEY, username TEXT, display_name TEXT, email_address TEXT, role TEXT, user_level INTEGER',
    'account_module_access' => 'account_id INTEGER, module_slug TEXT',
    'organization_positions' => 'account_id INTEGER PRIMARY KEY, tier INTEGER, position_name TEXT, changed_by INTEGER, updated_at TEXT',
    'organization_credentials' => 'id INTEGER PRIMARY KEY, account_id INTEGER, kind TEXT, qualification_tier INTEGER, system_slug TEXT, standard_reference TEXT, evidence_reference TEXT, status TEXT DEFAULT "active", expires_on TEXT, changed_by INTEGER, created_at TEXT, updated_at TEXT',
    'organization_missions' => 'id INTEGER PRIMARY KEY, account_id INTEGER, module_slug TEXT, system_slug TEXT, mission_reference TEXT, active INTEGER DEFAULT 1, expires_on TEXT, changed_by INTEGER, created_at TEXT, updated_at TEXT',
    'support_tickets' => 'id INTEGER PRIMARY KEY, ticket_id TEXT, type TEXT, subject TEXT, description TEXT, name TEXT, email TEXT, tier TEXT, status TEXT, assigned_to TEXT, created_at TEXT, closed_at TEXT',
    'support_replies' => 'id INTEGER PRIMARY KEY, ticket_id INTEGER, author TEXT, message TEXT, is_internal INTEGER, created_at TEXT',
    'support_events' => 'id INTEGER PRIMARY KEY, ticket_id INTEGER, event_type TEXT, from_value TEXT, to_value TEXT, actor TEXT, note TEXT, created_at TEXT',
] as $table => $columns) {
    $database->exec("CREATE TABLE $table ($columns)");
    $database->prepare('INSERT INTO information_schema.tables VALUES (?,?)')->execute(['test', $table]);
}

/** Isolated transport implements the unchanged Core model query contract. */
class model
{
    protected $db;
    public function __construct() { $this->db = $GLOBALS['database']; }
    public function query($sql, $params = []) { $stmt = $this->db->prepare($sql); $stmt->execute($params); return $stmt; }
    public function fetch(string $sql, array $params = []) { return $this->query($sql, $params)->fetch(); }
    public function fetchAll(string $sql, array $params = []) { return $this->query($sql, $params)->fetchAll(); }
}
class denied extends RuntimeException {}
class error_handler
{
    public static function respond($code, $title, $message): never { throw new denied($message, $code); }
    public function unauthorized(): never { throw new denied('Unauthorized', 403); }
}
class theme { public static function render($part, $data): bool { return true; } }
class test_mail
{
    public string $Subject = '';
    public string $Body = '';
    private string $address = '';
    public function setFrom($address, $name): void {}
    public function addAddress($address, $name): void { $this->address = $address; }
    public function isHTML($value): void {}
    public function send(): void { $GLOBALS['sent'][] = [$this->address, $this->Subject, $this->Body]; }
}
class mailer { public function create(): test_mail { return new test_mail(); } }
foreach (['lib/organization_policy.php', 'models/organization_model.php', 'models/accounts_model.php', 'lib/admin_access.php', 'core/controller.php', 'core/router.php', 'controllers/accounts.php'] as $file) {
    require APPROOT . '/' . $file;
}
require USERROOT . '/modules/support/models/support_model.php';
require USERROOT . '/modules/support/controllers/support.php';
$checks = 0;
function check(bool $condition, string $label): void
{
    $GLOBALS['checks']++;
    if (!$condition) { throw new RuntimeException('FAILED: ' . $label); }
}
function reject(callable $operation, string $label): void
{
    try { $operation(); } catch (denied | InvalidArgumentException $error) { check(true, $label); return; }
    check(false, $label);
}
function callPrivate(object $object, string $method, ...$args): mixed
{
    return (new ReflectionMethod($object, $method))->invoke($object, ...$args);
}
function render(callable $operation): string
{
    ob_start();
    try { $operation(); return (string) ob_get_contents(); } finally { ob_end_clean(); }
}
$org = new organization_model();
$database->exec("INSERT INTO accounts VALUES (10,'admin','Administrator','a@example.invalid','admin',9)");
for ($tier = 0; $tier <= 8; $tier++) {
    $database->prepare('INSERT INTO accounts VALUES (?,?,?,?,?,?)')->execute([$tier + 1, 'person' . $tier, 'Person ' . $tier, 'person@example.invalid', $tier === 9 ? 'admin' : 'user', $tier === 9 ? 9 : 1]);
    if ($tier > 0 && $tier < 9) {
        $database->prepare('INSERT INTO organization_positions VALUES (?,?,?,?,?)')->execute([$tier + 1, $tier, organization_policy::TIERS[$tier], 10, '2026-10-28']);
        $org->change(10, $tier + 1, ['action' => 'mission', 'module_slug' => 'support', 'system_slug' => 'digit', 'mission_reference' => 'test assignment'], ['support']);
        foreach (['certification', 'qualification'] as $kind) {
            $org->change(10, $tier + 1, ['action' => 'credential', 'kind' => $kind, 'qualification_tier' => $tier, 'system_slug' => 'digit', 'standard_reference' => 'test standard', 'evidence_reference' => 'test evidence'], []);
        }
    }
}
$_SESSION = ['user_id' => 1, 'user_level' => 9, 'csrf_token' => 'valid', 'display_name' => 'Test actor'];
check(admin_access::tier() === 0, 'ordinary user is not staff despite stale session level');
check(!admin_access::canAccess(1, 9, 'support'), 'forged session level grants nothing');
check(!admin_access::canAccess(999, 9, 'accounts'), 'deleted account grants nothing');
check(admin_access::canAccess(10, 0, 'accounts'), 'TSC preserved from current account');
foreach (range(1, 8) as $tier) {
    check(!admin_access::canAccess($tier + 1, 9, 'accounts'), 'protected account management tier ' . $tier);
    check(!admin_access::mission($tier + 1, 'support', 'ezra'), 'cross-product access denied tier ' . $tier);
    foreach (['requester_email', 'note', 'read', 'status', 'assign', 'tier', 'install_sql'] as $action) {
        $expected = match ($action) {
            'requester_email' => in_array($tier, [1, 2], true),
            'note', 'read' => $tier <= 5,
            'status', 'assign', 'tier' => in_array($tier, [1, 2, 5], true),
            default => false,
        };
        check(admin_access::support('digit', $action, $tier + 1) === $expected, "tier $tier action $action");
    }
}
// Qualification must match the actual responsibility, not just the product.
$database->exec("UPDATE organization_credentials SET qualification_tier=3 WHERE account_id=5 AND kind='qualification'");
check(!admin_access::mission(5, 'support', 'digit'), 'database qualification cannot authorize code investigation');
$database->exec("UPDATE organization_credentials SET qualification_tier=4 WHERE account_id=5 AND kind='qualification'");
foreach (['pending', 'suspended', 'expired', 'revoked'] as $status) {
    $database->prepare("UPDATE organization_credentials SET status=? WHERE account_id=5 AND kind='certification'")->execute([$status]);
    check(!admin_access::mission(5, 'support', 'digit'), 'inactive certification ' . $status);
}
$database->exec("UPDATE organization_credentials SET status='active', expires_on='2000-01-01' WHERE account_id=5 AND kind='certification'");
check(!admin_access::mission(5, 'support', 'digit'), 'expired certification date');
$database->exec("UPDATE organization_credentials SET expires_on=NULL WHERE account_id=5");
check(admin_access::mission(5, 'support', 'digit'), 'restored current credentials');
$mission = $org->records(5)['missions'][0];
$org->change(10, 5, ['action' => 'withdraw', 'record_id' => $mission['id']], []);
check(!admin_access::mission(5, 'support', 'digit'), 'withdrawal effective without logout');
check(count($org->records(5)['missions']) === 1, 'withdrawal preserves mission record');
$database->exec('UPDATE organization_missions SET active=1 WHERE account_id=5');
$database->exec("UPDATE organization_missions SET expires_on='2000-01-01' WHERE account_id=5");
check(!admin_access::mission(5, 'support', 'digit'), 'mission expiration enforced');
$database->exec('UPDATE organization_missions SET expires_on=NULL WHERE account_id=5');
reject(fn () => $org->change(6, 5, ['action' => 'withdraw', 'record_id' => $mission['id']], []), 'support manager cannot grant or revoke missions');
reject(fn () => $org->change(10, 5, ['action' => 'position', 'tier' => 9], []), 'position form cannot create TSC');
reject(fn () => $org->change(10, 5, ['action' => 'mission', 'module_slug' => 'accounts', 'system_slug' => 'digit', 'mission_reference' => 'test'], ['accounts']), 'protected module cannot be assigned');
reject(fn () => $org->change(10, 5, ['action' => 'credential', 'kind' => 'qualification', 'system_slug' => 'digit', 'standard_reference' => 's', 'evidence_reference' => 'e', 'qualification_tier' => 0], []), 'qualification requires responsibility');
reject(fn () => $org->change(10, 5, ['action' => 'mission', 'module_slug' => 'support', 'system_slug' => 'digit', 'mission_reference' => 'test', 'expires_on' => '2026-02-31'], ['support']), 'invalid date rejected');

// Legacy allowance adoption never deletes records or silently installs schema.
$database->exec("INSERT INTO accounts VALUES (20,'legacy','Legacy','l@example.invalid','developer',7)");
$database->exec("INSERT INTO account_module_access VALUES (20,'news'),(20,'support')");
check(!admin_access::canAccess(20, 7, 'news'), 'legacy allowance alone cannot bypass mission requirements');
check(!admin_access::canAccess(20, 7, 'support'), 'legacy support allowance cannot bypass new policy');
$org->change(10, 20, ['action' => 'mission', 'module_slug' => 'news', 'system_slug' => 'news', 'mission_reference' => 'adoption'], ['news']);
$legacyMission = $org->records(20)['missions'][0];
$org->change(10, 20, ['action' => 'withdraw', 'record_id' => $legacyMission['id']], []);
check(!admin_access::canAccess(20, 7, 'news'), 'withdrawn explicit mission never falls back to old allowance');
check((int) $database->query('SELECT COUNT(*) FROM account_module_access')->fetchColumn() === 2, 'legacy grants remain stored');

// Real Support controller and model, with captured email and rendered views.
$support = (new ReflectionClass(support::class))->newInstanceWithoutConstructor();
$support->setModuleContext('support');
$model = new support_model();
$ticket = ['id' => 1, 'ticket_id' => 'abcdef', 'type' => 'digit', 'subject' => 'Digit case', 'description' => 'Issue', 'name' => 'Requester', 'email' => 'requester@example.invalid', 'tier' => 't4', 'status' => 'open', 'assigned_to' => 'account:5', 'created_at' => '2026-10-28'];
$fields = implode(',', array_keys($ticket));
$database->prepare('INSERT INTO support_tickets (' . $fields . ') VALUES (' . implode(',', array_fill(0, count($ticket), '?')) . ')')->execute(array_values($ticket));
$other = array_replace($ticket, ['id' => 2, 'ticket_id' => '123abc', 'type' => 'ezra', 'subject' => 'Secret Ezra case']);
$database->prepare('INSERT INTO support_tickets (' . $fields . ') VALUES (' . implode(',', array_fill(0, count($other), '?')) . ')')->execute(array_values($other));
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SESSION['user_id'] = 5;
$queue = render(fn () => $support->admin());
check(str_contains($queue, 'Digit case') && !str_contains($queue, 'Secret Ezra case'), 'queue filters unrelated product tickets');
check(!str_contains($queue, 'id="config-pane"'), 'non-admin configuration UI hidden');
$detail = render(fn () => $support->admin(['support', 'abcdef']));
check(str_contains($detail, 'Internal findings only') && !str_contains($detail, 'name="status"'), 'technical view has internal findings without status mutation');
reject(fn () => $support->admin(['support', '123abc']), 'direct URL cannot bypass product scope');
$GLOBALS['sent'] = [];
$_POST = ['message' => 'Forged public response'];
reject(fn () => callPrivate($support, 'handleTicketAction', $model, $ticket, 'reply'), 'forged public reply rejected');
check((int) $database->query('SELECT COUNT(*) FROM support_replies')->fetchColumn() === 0, 'forged reply causes no write');
$_POST = ['message' => 'Internal evidence', 'is_internal' => '1'];
callPrivate($support, 'handleTicketAction', $model, $ticket, 'reply');
check(count($model->getPublicReplies(1)) === 0 && count($model->getReplies(1)) === 1, 'internal evidence hidden from public ticket');
check($GLOBALS['sent'] === [], 'internal evidence sends no requester mail');
foreach ([4, 5, 6, 10] as $id) {
    $_SESSION['user_id'] = $id;
    callPrivate($support, 'sendReplyNotification', $model, $ticket, 'actor', 'private');
    callPrivate($support, 'sendStatusNotification', $model, $ticket);
    callPrivate($support, 'sendUpdateNotification', $model, $ticket, 'private');
}
check($GLOBALS['sent'] === [], 'every requester notification path enforces tier 1/2 boundary');
foreach ([2, 3] as $id) {
    $_SESSION['user_id'] = $id;
    $_POST = ['message' => 'Approved requester update'];
    callPrivate($support, 'handleTicketAction', $model, $ticket, 'reply');
}
check(count($GLOBALS['sent']) === 2 && count($model->getPublicReplies(1)) === 2, 'tier 1/2 retain correspondence after escalation');
$_SESSION['user_id'] = 6;
$_POST = ['status' => 'in_progress'];
callPrivate($support, 'handleTicketAction', $model, $ticket, 'status');
check(count($GLOBALS['sent']) === 2, 'manager status update cannot email requester');
$_POST = ['tier' => 't4', 'assigned_to' => '4'];
reject(fn () => callPrivate($support, 'handleTicketAction', $model, $ticket, 'tier'), 'database technician cannot be assigned code escalation');
$_POST = ['tier' => 't4', 'assigned_to' => '5', 'note' => 'Investigate'];
callPrivate($support, 'handleTicketAction', $model, $ticket, 'tier');
check($model->findTicket('abcdef')['assigned_to'] === 'account:5', 'eligible technical escalation uses account identity');
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['action' => 'delete_data', 'csrf_token' => 'valid'];
reject(fn () => $support->admin(), 'support manager cannot delete support records');
$_SESSION['user_id'] = 2;
$_POST = ['action' => 'reply', 'ticket_id' => 'abcdef', 'message' => 'No token'];
reject(fn () => $support->admin(), 'CSRF enforced before mutations');
$_POST = ['action' => 'arbitrary_method', 'csrf_token' => 'valid'];
reject(fn () => $support->admin(), 'unknown action rejected');

$router = (new ReflectionClass(router::class))->newInstanceWithoutConstructor();
$accounts = (new ReflectionClass(accounts::class))->newInstanceWithoutConstructor();
check(callPrivate($router, 'isRoutableAction', $accounts, 'organization'), 'organization route explicitly exposed');
check(!callPrivate($router, 'isRoutableAction', $support, 'sendReplyNotification'), 'private email helper cannot be routed');
check(!callPrivate($router, 'isRoutableAction', $support, 'admin'), 'module admin remains behind Core dispatch');
$_SESSION['user_id'] = 10;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = ['account_id' => 5];
$_POST = [];
$database->exec("UPDATE organization_credentials SET evidence_reference='<script>alert(1)</script>' WHERE account_id=5");
$screen = render(fn () => $accounts->organization());
check(str_contains($screen, 'Mission assignments') && str_contains($screen, 'qualification (Tier 4)'), 'organization management displays qualification responsibility');
check(str_contains($screen, '&lt;script&gt;') && !str_contains($screen, '<script>alert(1)</script>'), 'credential evidence is escaped in management view');
// A broken/partial installation denies non-TSC access without recreating tables.
$database->exec("DELETE FROM information_schema.tables WHERE table_name='organization_credentials'");
check(!admin_access::canAccess(20, 7, 'news'), 'partial schema cannot revive legacy allowances');
check(!admin_access::canAccess(2, 1, 'support'), 'partial schema denies staff access');
check(admin_access::canAccess(10, 9, 'accounts'), 'TSC can repair partial installation');
$database->exec("INSERT INTO information_schema.tables VALUES ('test','organization_credentials')");
// Eligibility alone and exact date boundaries are independently exercised.
$credentials = $org->records(5)['credentials'];
$missions = $org->records(5)['missions'];
check(!organization_policy::permits(4, [], $credentials, 'support', 'digit', '2026-10-28'), 'credentials alone never grant access');
$missions[0]['expires_on'] = '2026-10-28';
check(organization_policy::permits(4, $missions, $credentials, 'support', 'digit', '2026-10-28'), 'mission valid through expiration date');
check(!organization_policy::permits(4, $missions, $credentials, 'support', 'digit', '2026-10-29'), 'mission denied day after expiration');
$_SESSION['user_id'] = 2;
$_POST = ['tier' => 't4', 'assigned_to' => 5];
reject(fn () => callPrivate($support, 'handleTicketAction', $model, $ticket, 'tier'), 'general support cannot bypass advanced escalation');
$_POST = ['tier' => 't2', 'assigned_to' => 3];
callPrivate($support, 'handleTicketAction', $model, $ticket, 'tier');
check($model->findTicket('abcdef')['tier'] === 't2', 'general support can escalate to eligible advanced support');
$database->exec("CREATE TRIGGER fail_assignment BEFORE INSERT ON support_events WHEN NEW.event_type='assignment' BEGIN SELECT RAISE(ABORT, 'test failure'); END");
try {
    $model->escalateTicket($model->findTicket('abcdef'), 't3', 'account:4', 'test', null);
    check(false, 'assignment event failure must abort escalation');
} catch (PDOException $expected) {
    check($model->findTicket('abcdef')['tier'] === 't2' && $model->findTicket('abcdef')['assigned_to'] === 'account:3', 'escalation rollback preserves both tier and assignee');
}
$database->exec('DROP TRIGGER fail_assignment');
$credential = $org->records(5)['credentials'][0];
$org->change(10, 5, ['action' => 'credential_status', 'record_id' => $credential['id'], 'status' => 'revoked'], []);
check(!admin_access::mission(5, 'support', 'digit'), 'credential revocation effective immediately');
check(count($org->records(5)['credentials']) === 2, 'credential revocation preserves records');
$_SESSION['user_id'] = 10;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['action' => 'install_organization'];
reject(fn () => $accounts->organization(), 'organization install requires CSRF');
fwrite(STDOUT, "PASS: $checks organization and Support regression checks (SQLite fixture, captured mail).\n");
/* [End AI:GPT-6] */
