<?php

declare(strict_types=1);

/* [AI:GPT-6 | 2026-10-29 04:17:13 UTC] */
/** Pure organizational policy; credentials establish eligibility, never access. */
final class organization_policy
{
    public const TIERS = [
        1 => 'General Support', 2 => 'Advanced Support',
        3 => 'Technical Support — Database', 4 => 'Technical Support — Code',
        5 => 'Support Manager', 6 => 'Assistant Department / Section Manager',
        7 => 'Developer / AI Agent', 8 => 'Department / Section Manager',
        9 => 'Administrator — TSC',
    ];

    public static function technical(int $tier): bool
    {
        return in_array($tier, [3, 4, 7], true);
    }

    /** Evaluate already loaded records without trusting session claims. */
    public static function permits(int $tier, array $missions, array $credentials, string $module, ?string $system, string $today): bool
    {
        if ($tier === 9) {
            return true;
        }
        if ($tier < 1 || $tier > 8 || ($tier <= 5 && $module !== 'support')) {
            return false;
        }
        foreach ($missions as $mission) {
            if ($mission['module_slug'] !== $module || !$mission['active']
                || ($system !== null && $mission['system_slug'] !== $system)
                || (!empty($mission['expires_on']) && $mission['expires_on'] < $today)) {
                continue;
            }
            /*
             * [AI:GPT-6 | 2026-10-29 UTC]
             * Every mission requires a current, matching certification.
             * Technical positions additionally require qualification at
             * their responsibility tier. No credential is itself a mission.
             */
            $certified = false;
            $qualified = false;
            foreach ($credentials as $credential) {
                if ($credential['system_slug'] !== $mission['system_slug']
                    || $credential['status'] !== 'active'
                    || (!empty($credential['expires_on']) && $credential['expires_on'] < $today)) {
                    continue;
                }
                if ($credential['kind'] === 'certification') {
                    $certified = true;
                }
                if ($credential['kind'] === 'qualification'
                    && (int) ($credential['qualification_tier'] ?? 0) === $tier) {
                    $qualified = true;
                }
            }
            if ($certified && (!self::technical($tier) || $qualified)) {
                return true;
            }
            /* [End AI:GPT-6] */
        }
        return false;
    }

    public static function supportAction(int $tier, string $action): bool
    {
        // TSC retains unrestricted administration; requester correspondence is
        // still reserved to the two explicitly authorized support positions.
        if ($action === 'requester_email') {
            return in_array($tier, [1, 2], true);
        }
        if ($tier === 9) {
            return true;
        }
        return match ($action) {
            'read', 'note' => in_array($tier, [1, 2, 3, 4, 5], true),
            'status' => in_array($tier, [1, 2, 5], true),
            'tier', 'assign' => in_array($tier, [1, 2, 5], true),
            default => false,
        };
    }
}
/* [End AI:GPT-6] */
