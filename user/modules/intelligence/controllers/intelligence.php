<?php
declare(strict_types=1);

/* [AI:GPT-5.6 Sol | 2026-10-06 UTC] */

final class intelligence extends controller
{
    private const ADMIN_ACTIONS = [
        'install_sql',
        'create_intelligence',
        'create_assignment',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function index(array $params = []): void
    {
        $model = $this->model('intelligence_model');

        if ($model->databaseState() !== 'current') {
            http_response_code(503);
            $this->error_page(
                'The Intelligence repository is unavailable.'
            );
            return;
        }

        $id = trim((string) ($params[0] ?? ''));
        if ($id !== '') {
            $record = $model->getByIntelligenceId($id);
            if (!$record) {
                http_response_code(404);
                $this->error_page('Intelligence record not found.');
                return;
            }
            $this->view('detail', [
                'title' => (string) $record['title'],
                'record' => $record,
            ]);
            return;
        }

        $data = [
            'title' => 'Intelligence',
            'sub_title' => 'STN-LABZ Persistent Intelligence',
            'records' => $model->getRecent(50),
        ];

        $this->view('index', $data);
    }

    /** Create an intelligence entry through an authenticated form/API session. */
    public function create(array $params = []): void
    {
        $this->require_admin(7);
        require_once APPROOT . '/lib/admin_access.php';
        if (!admin_access::canAccess(
            (int) ($_SESSION['user_id'] ?? 0),
            (int) ($_SESSION['user_level'] ?? 0),
            'intelligence'
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
        $this->require_csrf();
        $model = $this->model('intelligence_model');
        if ($model->databaseState() !== 'current') {
            http_response_code(503);
            $this->error_page('The Intelligence repository is unavailable.');
            return;
        }
        try {
            $model->createIntelligence(
                (string) ($_POST['title'] ?? ''),
                (string) ($_POST['content'] ?? ''),
                (string) ($_POST['source'] ?? ''),
                (string) ($_POST['status'] ?? 'ACTIVE')
            );
            header('Location: /admin/intelligence');
            exit;
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            $this->error_page($e->getMessage());
        }
    }

    public function report(array $params = []): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'method_not_allowed',
                ],
                405
            );
            return;
        }

        $model = $this->model('intelligence_model');

        if ($model->databaseState() !== 'current') {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'repository_unavailable',
                ],
                503
            );
            return;
        }

        try {
            $payload = $this->jsonInput();

            $intelligenceId = $model->reportIntelligence(
                (string) ($payload['intelligence_id'] ?? ''),
                (string) ($payload['title'] ?? ''),
                (string) ($payload['content'] ?? ''),
                isset($payload['source'])
                    ? (string) $payload['source']
                    : null,
                (string) ($payload['status'] ?? 'ACTIVE')
            );

            $this->jsonResponse(
                [
                    'ok' => true,
                    'intelligence_id' => $intelligenceId,
                ]
            );
        } catch (InvalidArgumentException $e) {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'invalid_request',
                    'message' => $e->getMessage(),
                ],
                400
            );
        } catch (Throwable $e) {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'report_failed',
                ],
                500
            );
        }
    }

    public function observation(array $params = []): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'method_not_allowed',
                ],
                405
            );
            return;
        }

        $model = $this->model('intelligence_model');

        if ($model->databaseState() !== 'current') {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'repository_unavailable',
                ],
                503
            );
            return;
        }

        try {
            $payload = $this->jsonInput();

            $model->addObservation(
                (string) ($payload['observation_id'] ?? ''),
                (string) ($payload['intelligence_id'] ?? ''),
                (string) ($payload['content'] ?? ''),
                'RICTUS',
                isset($payload['source'])
                    ? (string) $payload['source']
                    : null,
                isset($payload['observed_at'])
                    ? (string) $payload['observed_at']
                    : null
            );

            $this->jsonResponse(
                [
                    'ok' => true,
                    'observation_id' => (string) (
                        $payload['observation_id'] ?? ''
                    ),
                ]
            );
        } catch (InvalidArgumentException $e) {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'invalid_request',
                    'message' => $e->getMessage(),
                ],
                400
            );
        } catch (RuntimeException $e) {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'intelligence_not_found',
                    'message' => $e->getMessage(),
                ],
                404
            );
        } catch (Throwable $e) {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'observation_failed',
                ],
                500
            );
        }
    }

    public function assignments(array $params = []): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'method_not_allowed',
                ],
                405
            );
            return;
        }

        $model = $this->model('intelligence_model');

        if ($model->databaseState() !== 'current') {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'repository_unavailable',
                ],
                503
            );
            return;
        }

        try {
            $status = trim(
                (string) ($_GET['status'] ?? '')
            );

            $assignments = $model->getAssignments(
                $status !== '' ? $status : null
            );

            $this->jsonResponse(
                [
                    'ok' => true,
                    'assignments' => $assignments,
                ]
            );
        } catch (Throwable $e) {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'assignment_fetch_failed',
                ],
                500
            );
        }
    }

    public function assignment(array $params = []): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'method_not_allowed',
                ],
                405
            );
            return;
        }

        $model = $this->model('intelligence_model');

        if ($model->databaseState() !== 'current') {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'repository_unavailable',
                ],
                503
            );
            return;
        }

        try {
            $payload = $this->jsonInput();

            $assignmentId = trim(
                (string) ($payload['assignment_id'] ?? '')
            );

            $status = strtoupper(
                trim(
                    (string) ($payload['status'] ?? '')
                )
            );

            $model->updateAssignmentStatus(
                $assignmentId,
                $status
            );

            $this->jsonResponse(
                [
                    'ok' => true,
                    'assignment_id' => $assignmentId,
                    'status' => $status,
                ]
            );
        } catch (InvalidArgumentException $e) {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'invalid_request',
                    'message' => $e->getMessage(),
                ],
                400
            );
        } catch (Throwable $e) {
            $this->jsonResponse(
                [
                    'ok' => false,
                    'error' => 'assignment_update_failed',
                ],
                500
            );
        }
    }

    public function admin(array $params = []): void
    {
        $this->require_admin(7);

        $model = $this->model('intelligence_model');
        $state = $model->databaseState();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();

            $action = trim(
                (string) ($_POST['action'] ?? '')
            );

            if (!in_array($action, self::ADMIN_ACTIONS, true)) {
                http_response_code(400);
                $this->error_page('Invalid module action.');
                return;
            }

            if ($action === 'install_sql') {
                if ($state !== 'missing') {
                    http_response_code(409);
                    $this->error_page(
                        'The Intelligence database is already installed.'
                    );
                    return;
                }

                $model->installSchema();

                header(
                    'Location: /admin/intelligence'
                    . '?tab=database&installed=1'
                );
                exit;
            }

            if ($state !== 'current') {
                http_response_code(503);
                $this->error_page(
                    'The Intelligence repository is unavailable.'
                );
                return;
            }

            if ($action === 'create_intelligence') {
                try {
                    $intelligenceId = $model->createIntelligence(
                        (string) ($_POST['title'] ?? ''),
                        (string) ($_POST['content'] ?? ''),
                        (string) ($_POST['source'] ?? ''),
                        (string) ($_POST['status'] ?? 'ACTIVE')
                    );
                } catch (Throwable $e) {
                    http_response_code(400);
                    $this->error_page($e->getMessage());
                    return;
                }

                header(
                    'Location: /admin/intelligence'
                    . '?tab=intelligence'
                    . '&created=' . rawurlencode($intelligenceId)
                );
                exit;
            }

            if ($action === 'create_assignment') {
                try {
                    $assignmentId = $model->createAssignment(
                        (string) ($_POST['intelligence_id'] ?? ''),
                        (string) ($_POST['assignment_type'] ?? ''),
                        (string) ($_POST['instructions'] ?? '')
                    );
                } catch (Throwable $e) {
                    http_response_code(400);
                    $this->error_page($e->getMessage());
                    return;
                }

                header(
                    'Location: /admin/intelligence'
                    . '?tab=assignments'
                    . '&assigned=' . rawurlencode($assignmentId)
                );
                exit;
            }
        }

        $data = [
            'title' => 'Intelligence Administration',
            'sub_title' =>
                'STN-LABZ Persistent Intelligence Repository',
            'database_state' => $state,
            'records' => $state === 'current'
                ? $model->getAll()
                : [],
            'assignments' => $state === 'current'
                ? $model->getAssignments()
                : [],
            'installed' => isset($_GET['installed']),
            'created' => trim(
                (string) ($_GET['created'] ?? '')
            ),
            'assigned' => trim(
                (string) ($_GET['assigned'] ?? '')
            ),
        ];

        $this->view('admin/intelligence', $data);
    }

    private function jsonInput(): array
    {
        $raw = file_get_contents('php://input');

        if (!is_string($raw) || trim($raw) === '') {
            throw new InvalidArgumentException(
                'JSON request body is required.'
            );
        }

        try {
            $payload = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                'Invalid JSON request body.'
            );
        }

        if (!is_array($payload)) {
            throw new InvalidArgumentException(
                'JSON request body must be an object.'
            );
        }

        return $payload;
    }

    private function jsonResponse(
        array $payload,
        int $status = 200
    ): void {
        http_response_code($status);

        header(
            'Content-Type: application/json; charset=utf-8'
        );

        header('Cache-Control: no-store');

        echo json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );
    }
}

/* [End AI:GPT-5.6 Sol] */