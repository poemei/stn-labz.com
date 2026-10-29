<?php
declare(strict_types=1);
/* [AI:GPT-6 | 2026-10-29 UTC] */
final class certifications extends controller
{
    private const ADMIN_ACTIONS = ['install_sql', 'issue', 'status'];

    public function index(array $params = []): void
    {
        $this->view('index', ['title' => 'STN-Labz Certifications']);
    }

    public function admin(array $params = []): void
    {
        // Tier 9 remains the issuing/management authority; never assume a
        // certificate itself entitles its holder to issue more certificates.
        $this->require_admin(9);
        if (admin_access::tier() !== 9) {
            http_response_code(403);
            $this->error_page('Certification management requires Tier 9 authority.');
            return;
        }
        /** @var certifications_model $model */
        $model = $this->model('certifications_model');
        $ready = $model->ready();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();
            $action = (string) ($_POST['action'] ?? '');
            if (!in_array($action, self::ADMIN_ACTIONS, true)) {
                http_response_code(400);
                $this->error_page('Invalid certification management action.');
                return;
            }
            if ($action === 'install_sql') {
                if ($ready) {
                    http_response_code(409);
                    $this->error_page('Certifications SQL is already installed.');
                    return;
                }
                $model->installSchema();
            } elseif (!$ready) {
                http_response_code(409);
                $this->error_page('Install module SQL first.');
                return;
            } elseif ($action === 'issue') {
                $model->issue($_POST, (int) ($_SESSION['user_id'] ?? 0));
            } elseif ($action === 'status') {
                $model->setStatus((string) ($_POST['certificate_id'] ?? ''), (string) ($_POST['status'] ?? ''));
            }
            header('Location: /admin/certifications');
            exit;
        }
        $this->view('admin/index', [
            'ready' => $ready,
            'accounts' => $ready ? $model->accounts() : [],
            'records' => $ready ? $model->listRecords() : [],
        ]);
    }
}
/* [End AI:GPT-6] */
