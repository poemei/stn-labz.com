<?php
declare(strict_types=1);

/** Security module request controller. */
/* [AI:GPT-5.6 Sol | 2026-09-02 UTC] */
final class security extends controller
{
    private const ADMIN_ACTIONS = ['install_sql', 'save_settings', 'delete_data'];

    public function index(array $params = []): void
    {
        if ($params !== []) {
            $this->notFound();
        }

        $model = $this->model('security_model');
        if ($model->databaseState() !== 'ready') {
            http_response_code(503);
            $this->error_page('The security information is temporarily unavailable.');
        }

        $this->view('index', [
            'settings' => $model->getSettings(),
            'sentinel_installed' => $model->isSentinelInstalled(),
        ]);
    }

    public function admin(array $params = []): void
    {
        $this->require_admin(7);
        $model = $this->model('security_model');
        $state = $model->databaseState();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();
            $action = trim((string) ($_POST['action'] ?? ''));

            if (!in_array($action, self::ADMIN_ACTIONS, true)) {
                http_response_code(400);
                $this->error_page('Invalid Security module action.');
            }

            if ($action === 'install_sql') {
                if ($state !== 'missing') {
                    http_response_code(409);
                    $this->error_page('The Security schema is already installed.');
                }
                $model->installSchema();
                if ($model->databaseState() !== 'ready') {
                    throw new RuntimeException('Security schema installation did not complete.');
                }
                header('Location: /admin/security?installed=1');
                exit;
            }

            if ($state !== 'ready') {
                $this->renderAdmin($model, $state, ['Install the module database before managing Security.']);
                return;
            }

            if ($action === 'save_settings') {
                $errors = [];
                $settings = $this->validateSettings($_POST, $errors);
                if ($errors !== []) {
                    $this->renderAdmin($model, 'ready', $errors, $settings);
                    return;
                }
                $model->saveSettings($settings);
                header('Location: /admin/security?saved=1');
                exit;
            }

            if ($action === 'delete_data') {
                $model->deleteData();
                header('Location: /admin/security?deleted_data=1');
                exit;
            }
        }

        $this->renderAdmin($model, $state);
    }

    private function validateSettings(array $input, array &$errors): array
    {
        $pgpLabel = trim((string) ($input['pgp_label'] ?? ''));
        $pgpPublicKey = trim((string) ($input['pgp_public_key'] ?? ''));
        $rsaLabel = trim((string) ($input['rsa_label'] ?? ''));
        $rsaPublicKey = trim((string) ($input['rsa_public_key'] ?? ''));
        $securityTxtUrl = trim((string) ($input['security_txt_url'] ?? '/.well-known/security.txt'));

        if (strlen($pgpLabel) > 160 || strlen($rsaLabel) > 160) {
            $errors[] = 'Key labels must not exceed 160 characters.';
        }
        if (strlen($pgpPublicKey) > 1000000 || strlen($rsaPublicKey) > 1000000) {
            $errors[] = 'Public key text must not exceed 1,000,000 characters.';
        }
        if ($securityTxtUrl !== '' && !$this->validUrl($securityTxtUrl)) {
            $errors[] = 'Security.txt URL must be a valid absolute or site-relative URL.';
        }

        return [
            'pgp_label' => $pgpLabel,
            'pgp_public_key' => $pgpPublicKey,
            'rsa_label' => $rsaLabel,
            'rsa_public_key' => $rsaPublicKey,
            'security_txt_url' => $securityTxtUrl,
        ];
    }

    private function validUrl(string $url): bool
    {
        if (str_starts_with($url, '/')) {
            return !str_starts_with($url, '//') && strlen($url) <= 2048;
        }
        return filter_var($url, FILTER_VALIDATE_URL) !== false && strlen($url) <= 2048;
    }

    private function renderAdmin(security_model $model, string $state, array $errors = [], array $values = []): void
    {
        if ($state === 'ready' && $values === []) {
            $values = $model->getSettings();
        }
        $this->view('admin/security', [
            'database_state' => $state,
            'errors' => $errors,
            'values' => $values,
            'sentinel_installed' => $model->isSentinelInstalled(),
            'installed' => isset($_GET['installed']),
            'saved' => isset($_GET['saved']),
            'deleted_data' => isset($_GET['deleted_data']),
        ]);
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->error_page('The requested security resource does not exist.');
    }
}
/* [End AI:GPT-5.6 Sol] */
