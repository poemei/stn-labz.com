<?php
declare(strict_types=1);

/* [AI:GPT-6 | 2026-10-29 UTC] */
/**
 * Mission Control policy denial matrix.
 *
 * Run: php tests/mission_policy_denials.php
 * Pure policy tests: no database, emails, deployment or account mutations.
 * This does NOT test transitional Level 7 allowances in admin_access.
 */
require dirname(__DIR__) . '/app/lib/organization_policy.php';

$checks = 0;
function expectPolicy(bool $actual, bool $expected, string $label): void
{
    global $checks;
    ++$checks;
    if ($actual !== $expected) {
        throw new RuntimeException("FAILED: {$label}; expected " . ($expected ? 'allow' : 'deny'));
    }
}

$today = '2026-10-29';
$mission = static fn (array $overrides = []): array => array_replace([
    'module_slug' => 'support',
    'system_slug' => 'digit',
    'active' => 1,
    'expires_on' => null,
], $overrides);
$certificate = static fn (array $overrides = []): array => array_replace([
    'kind' => 'certification',
    'system_slug' => 'digit',
    'status' => 'active',
    'expires_on' => null,
    'qualification_tier' => null,
], $overrides);
$qualification = static fn (int $tier, array $overrides = []): array => array_replace([
    'kind' => 'qualification',
    'system_slug' => 'digit',
    'status' => 'active',
    'expires_on' => null,
    'qualification_tier' => $tier,
], $overrides);
$permits = static fn (int $tier, array $missions, array $credentials, ?string $system = 'digit', string $module = 'support'): bool =>
    organization_policy::permits($tier, $missions, $credentials, $module, $system, $today);

foreach (range(1, 8) as $tier) {
    $credentials = [$certificate(), $qualification($tier)];
    // Certification + mission eligibility is separate from Support action authority.
    // Tiers 6 and 8 may hold a valid mission, but cannot read Support tickets.
    expectPolicy($permits($tier, [$mission()], $credentials), true, "tier {$tier}: valid certification and mission");
    expectPolicy(organization_policy::supportAction($tier, 'read'), $tier <= 5, "tier {$tier}: Support read responsibility boundary");
    expectPolicy($permits($tier, [], $credentials), false, "tier {$tier}: no mission");
    expectPolicy($permits($tier, [$mission()], []), false, "tier {$tier}: no certification");
    expectPolicy($permits($tier, [$mission()], [$qualification($tier)]), false, "tier {$tier}: qualification alone");
    expectPolicy($permits($tier, [$mission(['active' => 0])], $credentials), false, "tier {$tier}: withdrawn mission");
    expectPolicy($permits($tier, [$mission(['expires_on' => '2026-10-28'])], $credentials), false, "tier {$tier}: expired mission");
    expectPolicy($permits($tier, [$mission()], [$certificate(['system_slug' => 'ezra']), $qualification($tier)]), false, "tier {$tier}: wrong-system certification");
    expectPolicy($permits($tier, [$mission()], $credentials, 'ezra'), false, "tier {$tier}: wrong-system request");
    expectPolicy($permits($tier, [$mission()], $credentials, 'digit', 'posts'), false, "tier {$tier}: wrong module");
    foreach (['pending', 'suspended', 'expired', 'revoked'] as $status) {
        expectPolicy($permits($tier, [$mission()], [$certificate(['status' => $status]), $qualification($tier)]), false, "tier {$tier}: {$status} certification");
    }
    expectPolicy($permits($tier, [$mission()], [$certificate(['expires_on' => '2026-10-28']), $qualification($tier)]), false, "tier {$tier}: dated-out certification");
    if (organization_policy::technical($tier)) {
        expectPolicy($permits($tier, [$mission()], [$certificate()]), false, "tier {$tier}: missing technical qualification");
        expectPolicy($permits($tier, [$mission()], [$certificate(), $qualification($tier + 1)]), false, "tier {$tier}: wrong qualification tier");
        expectPolicy($permits($tier, [$mission()], [$certificate(), $qualification($tier, ['status' => 'revoked'])]), false, "tier {$tier}: revoked qualification");
    }
}
// Tier 9 authority is explicitly maintained; Level 0 is always denied.
expectPolicy($permits(9, [], []), true, 'Tier 9 exception');
expectPolicy($permits(0, [$mission()], [$certificate()]), false, 'Level 0 denied');

echo "PASS: {$checks} Mission Control policy checks.\n";
/* [End AI:GPT-6] */
