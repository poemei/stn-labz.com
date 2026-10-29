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

    /** The legacy level argument is retained for callers, never trusted. */
    public static function canAccess(int $accountId, int $userLevel, string $module): bool
    {
        return self::mission($accountId, $module);
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
        return organization_policy::supportAction(self::tier($accountId), $action)
            && self::mission($accountId, 'support', $system);
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
