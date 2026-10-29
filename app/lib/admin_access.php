<?php

declare(strict_types=1);

/* [AI:GPT-6 | 2026-10-29 04:17:13 UTC] */
/** Core authorization shared by dispatch, navigation and module operations. */
final class admin_access
{
    private static ?organization_model $organization = null;
    private const RESTRICTED_MODULES = ['accounts', 'admin', 'auth', 'error_handler', 'install', 'modules', 'updater'];

    private static function organization(): organization_model
    {
        return self::$organization ??= new organization_model();
    }

    public static function isAssignable(string $module): bool
    {
        return preg_match('/^[a-z][a-z0-9_]{0,62}$/', $module) === 1
            && !in_array($module, self::RESTRICTED_MODULES, true);
    }

    public static function tier(?int $accountId = null): int
    {
        $accountId ??= (int) ($_SESSION['user_id'] ?? 0);
        if ($accountId < 1) {
            return 0;
        }
        try {
            return self::organization()->tier($accountId);
        } catch (Throwable $error) {
            error_log('Organization authorization failed: ' . $error->getMessage());
            return 0;
        }
    }

    public static function supportTier(?int $accountId = null): int
    {
        $accountId ??= (int) ($_SESSION['user_id'] ?? 0);
        if ($accountId < 1) { return 0; }
        try { return self::organization()->supportTier($accountId); }
        catch (Throwable $error) {
            error_log('Support responsibility resolution failed: ' . $error->getMessage());
            return 0;
        }
    }

    /** The legacy level argument is retained for callers, never trusted. */
    public static function canAccess(int $accountId, int $userLevel, string $module): bool
    {
        if ($module === 'support' && self::supportTier($accountId) > 0) { return true; }
        if (self::mission($accountId, $module)) {
            return true;
        }

        /*
         * [AI:GPT-6 | 2026-10-29 UTC]
         * Restore existing, specifically assigned Developer/AI Level 7 module
         * allowances during Mission Control credential/mission adoption.
         * This cannot assign new modules, confer Tier 9 authority, or grant
         * access to restricted Core administration.
         * Existing allowance records remain the transitional authority only.
         */
        if ($accountId < 1 || !self::isAssignable($module) || self::tier($accountId) !== 7) {
            return false;
        }

        try {
            require_once APPROOT . '/models/accounts_model.php';
            $account = (new accounts_model())->get_by_id($accountId);
            if (!is_array($account)
                || !in_array((string) ($account['role'] ?? ''), ['developer', 'ai'], true)
                || (int) ($account['user_level'] ?? 0) !== 7) {
                return false;
            }
            return (new accounts_model())->has_admin_module_access($accountId, $module);
        } catch (Throwable $error) {
            error_log('Level 7 module allowance lookup failed: ' . $error->getMessage());
            return false;
        }
        /* [End AI:GPT-6] */
    }

    public static function mission(int $accountId, string $module, ?string $system = null): bool
    {
        if ($accountId < 1) {
            return false;
        }
        try {
            return self::organization()->permits($accountId, $module, $system);
        } catch (Throwable $error) {
            error_log('Mission authorization failed: ' . $error->getMessage());
            return false;
        }
    }

    public static function support(string $system, string $action, ?int $accountId = null): bool
    {
        $accountId ??= (int) ($_SESSION['user_id'] ?? 0);
        return organization_policy::supportAction(self::supportTier($accountId), $action)
            && self::mission($accountId, 'support', $system);
    }

    /**
     * Check a ticket's product-scoped mission, or an explicit routing
     * destination matching the actor's Organization Support tier.
     * A destination does not bypass the need for an active certified mission,
     * and never grants requester correspondence outside Tiers 1 and 2.
     */
    public static function supportTicket(array $ticket, string $action, ?int $accountId = null): bool
    {
        $accountId ??= (int) ($_SESSION['user_id'] ?? 0);
        $tier = self::supportTier($accountId);
        if (!organization_policy::supportAction($tier, $action)) {
            return false;
        }
        $system = (string) ($ticket['type'] ?? '');
        if ($system !== '' && self::mission($accountId, 'support', $system)) {
            return true;
        }
        // Ticket destination is independently assigned by the escalation
        // workflow; unrelated topics are not disclosed outside that queue.
        if ($tier < 1 || $tier > 5 || $action === 'requester_email') {
            return false;
        }
        $target = 't' . $tier;
        return (string) ($ticket['tier'] ?? '') === $target
            && (string) ($ticket['assigned_to'] ?? '') === 'tier:' . $target
            && $tier === self::supportTier($accountId);
    }

    /** Navigation visibility is based on current grants, not position alone. */
    public static function hasAdminNavigation(): bool
    {
        $id = (int) ($_SESSION['user_id'] ?? 0);
        if ($id < 1) { return false; }
        $tier = self::tier($id);
        if ($tier === 9) { return true; }
        if ($tier < 1) { return false; }

        try {
            $modules = [];
            $organization = self::organization();
            if ($organization->ready()) {
                foreach ($organization->records($id)['missions'] as $mission) {
                    $slug = (string) ($mission['module_slug'] ?? '');
                    if (self::isAssignable($slug)) { $modules[$slug] = true; }
                }
            }
            if ($tier === 7) {
                require_once APPROOT . '/models/accounts_model.php';
                $accounts = new accounts_model();
                $account = $accounts->get_by_id($id);
                if (is_array($account)
                    && in_array((string) ($account['role'] ?? ''), ['developer', 'ai'], true)
                    && (int) ($account['user_level'] ?? 0) === 7) {
                    foreach ($accounts->get_admin_modules_for_account($id) as $slug) {
                        if (is_string($slug) && self::isAssignable($slug)) {
                            $modules[$slug] = true;
                        }
                    }
                }
            }
            foreach (array_keys($modules) as $slug) {
                if (self::canAccess($id, 0, $slug)) { return true; }
            }
        } catch (Throwable $error) {
            error_log('Admin navigation visibility failed: ' . $error->getMessage());
        }
        return false;
    }

    /** @param array<int,string> $modules @return array<int,string> */
    public static function filterForCurrentAccount(array $modules): array
    {
        return array_values(array_filter($modules, static fn (string $module): bool => self::canAccess(
            (int) ($_SESSION['user_id'] ?? 0), 0, $module
        )));
    }
}
/* [End AI:GPT-6] */
