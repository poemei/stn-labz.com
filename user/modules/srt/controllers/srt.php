<?php
declare(strict_types=1);

final class srt extends controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index($url_params = null): void
    {
        $model = $this->model('srt_model');

        $data = [
            'title' => 'Security Research',
            'sub_title' => 'Confirmed STN-LABZ Security Research Targets.',
            'srts' => $model->getAll()
        ];

        $this->view('index', $data);
    }

    public function detail($url_params = null): void
    {
        $srt_id = trim((string)($url_params[0] ?? ''));

        if ($srt_id === '') {
            http_response_code(404);
            return;
        }

        $model = $this->model('srt_model');
        $srt = $model->getBySrtId($srt_id);

        if (!$srt) {
            http_response_code(404);
            return;
        }

        $data = [
            'title' => $srt['title'],
            'sub_title' => $srt['srt_id'],
            'srt' => $srt
        ];

        $this->view('detail', $data);
    }

    /** Publish an SRT through the authenticated, module-granted session. */
    public function create($url_params = null): void
    {
        $this->require_admin(7);
        require_once APPROOT . '/lib/admin_access.php';
        if (!admin_access::canAccess(
            (int) ($_SESSION['user_id'] ?? 0),
            (int) ($_SESSION['user_level'] ?? 0),
            'srt'
        )) {
            http_response_code(403);
            $this->error_page('Module access denied.');
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        // Reuse the existing CSRF, field validation, and model insertion.
        $_POST['action'] = 'create';
        $this->admin($url_params);
    }

    public function admin($url_params = null): void
    {
        $this->require_admin(7);
        $model = $this->model('srt_model');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();
            $action = (string) ($_POST['action'] ?? '');
            if (!in_array($action, ['create', 'update'], true)) {
                http_response_code(400);
                $this->error_page('Invalid SRT action.');
                return;
            }

            $srtId = trim((string) ($_POST['srt_id'] ?? ''));
            $status = strtoupper(trim((string) ($_POST['status'] ?? 'ACTIVE')));
            $title = trim((string) ($_POST['title'] ?? ''));
            $summary = trim((string) ($_POST['summary'] ?? ''));
            $authorizedBy = trim((string) ($_POST['authorized_by'] ?? ''));
            $authorizedAt = trim((string) ($_POST['authorized_at'] ?? ''));
            if (
                !preg_match('/^SRT-[A-Za-z0-9-]{1,60}$/', $srtId)
                || !in_array($status, ['ACTIVE', 'HOLD', 'CLOSED'], true)
                || $title === '' || $summary === '' || $authorizedBy === ''
                || strlen($title) > 255 || strlen($srtId) > 64
                || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $authorizedAt)
            ) {
                http_response_code(400);
                $this->error_page('Invalid or missing SRT fields.');
                return;
            }
            $fields = [
                'srt_id' => $srtId,
                'title' => $title,
                'status' => $status,
                'classification' => trim((string) ($_POST['classification'] ?? '')),
                'summary' => $summary,
                'source_intelligence_id' => trim((string) ($_POST['source_intelligence_id'] ?? '')),
                'authorized_by' => $authorizedBy,
                'authorized_at' => str_replace('T', ' ', $authorizedAt) . ':00',
            ];
            if ($action === 'create' && $model->existsBySrtId($srtId)) {
                http_response_code(409);
                $this->error_page('SRT identifier already exists.');
                return;
            }
            try {
                $success = $action === 'create'
                    ? $model->create($fields)
                    : $model->updateSrt($srtId, $fields);
                if (!$success) {
                    throw new RuntimeException('SRT save failed.');
                }
            } catch (Throwable $error) {
                error_log('SRT save failed: ' . $error->getMessage());
                http_response_code(500);
                $this->error_page('Could not save SRT.');
                return;
            }
            header('Location: /admin/srt');
            exit;
        }

        $data = [
            'title' => 'Security Research Administration',
            'sub_title' => 'Manage permanent STN-LABZ Security Research Target records.',
            'srts' => $model->getAll()
        ];

        $this->view('admin/index', $data);
    }
}