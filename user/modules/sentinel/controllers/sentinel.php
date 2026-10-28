<?php
declare(strict_types=1);

// path: /user/modules/sentinel/controllers/sentinel.php

class sentinel extends controller
{
    public function index(array $params = []): void
    {
        $this->view('index');
    }

    /**
     * Called only from an explicitly configured, approved pre-dispatch hook.
     * Do not invoke this from index() or the Admin page.
     */
    public static function inspect(): void
    {
        require_once dirname(__DIR__) . '/lib/sentinel_perimeter.php';
        sentinel_perimeter::inspect();
    }

    public function admin(array $params = []): void
    {
        $this->require_admin(9);
        $model = $this->model('sentinel_model');
        $error = null;
        $notice = null;
        $httpbl = null;
        // Router manages the session lifecycle.
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['sentinel_admin_notice'])) {
            $notice = $_SESSION['sentinel_admin_notice'];
            unset($_SESSION['sentinel_admin_notice']);
        }
        $databaseState = $model->databaseState();
        $schemaAvailable = $databaseState === 'current';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();
            $action = trim((string) ($_POST['action'] ?? ''));

            try {
                if ($action === 'install_sql' && $databaseState === 'missing') {
                    $model->install_schema();
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        $_SESSION['sentinel_admin_notice'] = ['type' => 'success', 'message' => 'Sentinel SQL installed.'];
                    }
                    header('Location: /admin/sentinel?status=installed');
                    exit;
                }
                if ($action === 'update_sql' && $databaseState === 'update') {
                    $model->update_schema();
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        $_SESSION['sentinel_admin_notice'] = ['type' => 'success', 'message' => 'Sentinel schema updated; existing settings preserved and duplicate settings consolidated.'];
                    }
                    header('Location: /admin/sentinel?status=schema_updated');
                    exit;
                }
                if ($databaseState !== 'current') {
                    throw new RuntimeException('Complete the Sentinel SQL lifecycle action before using other Admin operations.');
                }

                $successMessage = 'Sentinel action completed.';
                if ($action === 'update_config') {
                    $settings = is_array($_POST['settings'] ?? null) ? $_POST['settings'] : [];
                    $keySupplied = isset($settings['httpbl_key']) && is_string($settings['httpbl_key']) && $settings['httpbl_key'] !== '';
                    $model->save_config($settings);
                    $successMessage = $keySupplied
                        ? 'Sentinel settings saved. http:BL key accepted and stored; DNS connectivity has not yet been tested.'
                        : 'Sentinel settings saved. Existing http:BL key preserved if configured; Sentinel operates independently of this plugin.';
                } elseif ($action === 'block_ip') {
                    $model->block_ip((string) ($_POST['ip'] ?? ''));
                } elseif ($action === 'whitelist_add') {
                    $model->whitelist_add((string) ($_POST['ip'] ?? ''));
                } elseif ($action === 'whitelist_remove') {
                    $model->whitelist_remove((string) ($_POST['ip'] ?? ''));
                } elseif ($action === 'sync_intelligence') {
                    $model->pull_global_intelligence();
                    $successMessage = 'STN-LABZ intelligence sync completed successfully.';
                } elseif ($action === 'lookup_httpbl') {
                    $httpbl = $model->lookup_httpbl(trim((string) ($_POST['ip'] ?? '')));
                } elseif ($action === 'delete_data') {
                    $model->delete_data();
                    $successMessage = 'Sentinel local threat records deleted; configuration and schema preserved.';
                } elseif ($action === 'reset_data') {
                    $model->reset_data();
                    $successMessage = 'Sentinel mutable threat data reset. Configuration and schema preserved.';
                } else {
                    throw new InvalidArgumentException('Unknown Sentinel action.');
                }

                if ($action !== 'lookup_httpbl') {
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        $_SESSION['sentinel_admin_notice'] = ['type' => 'success', 'message' => $successMessage];
                    }
                    header('Location: /admin/sentinel?status=updated');
                    exit;
                }
            } catch (InvalidArgumentException | RuntimeException $exception) {
                $error = $exception->getMessage();
            } catch (Throwable $exception) {
                error_log('Sentinel Admin action ' . preg_replace('/[^a-z_]/', '', $action)
                    . ' failed: ' . get_class($exception) . ' (code ' . $exception->getCode() . ')');
                $error = $action === 'sync_intelligence'
                    ? 'STN-LABZ intelligence sync failed due to an internal error. Check the server error log.'
                    : 'Sentinel action failed due to an internal error. Check the server error log.';
            }
            if ($error !== null && session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['sentinel_admin_notice'] = ['type' => 'error', 'message' => $error];
                header('Location: /admin/sentinel?status=failed');
                exit;
            }
        }

        $this->view('admin/index', [
            'schema_available' => $schemaAvailable,
            'database_state' => $model->databaseState(),
            'config_map' => $schemaAvailable ? $model->get_config_map() : [],
            'events' => $schemaAvailable ? $model->get_recent_events() : [],
            'whitelist' => $schemaAvailable ? $model->get_whitelist() : [],
            'error' => $error,
            'notice' => $notice,
            'httpbl' => $httpbl,
        ]);
    }
}
