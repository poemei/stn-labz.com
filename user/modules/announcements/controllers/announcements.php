<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/announcements_content.php';

/* [AI:GPT-5.6 | 2026-09-01 05:00:00 UTC] */
final class announcements extends controller
{
    private const ADMIN_ACTIONS = ['install_sql', 'update_sql', 'delete_data', 'create', 'update', 'delete'];

    public function index(array $params = []): void
    {
        $model = $this->model('announcements_model');
        if ($model->databaseState() !== 'current') {
            http_response_code(503);
            $this->error_page('Announcements is unavailable until its database is installed and current.');
        }
        $this->view('index', ['items' => $model->getActive()]);
    }

    public function admin(array $params = []): void
    {
        $this->require_admin(7);
        $model = $this->model('announcements_model');
        $state = $model->databaseState();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();
            $action = trim((string) ($_POST['action'] ?? ''));
            if (!in_array($action, self::ADMIN_ACTIONS, true)) {
                http_response_code(400);
                $this->error_page('Invalid Announcements module action.');
            }

            if ($action === 'install_sql') {
                if ($state !== 'missing') {
                    $this->error_page('Announcements schema is already installed.');
                }
                $model->installSchema();
                header('Location: /admin/announcements?installed=1');
                exit;
            }
            if ($action === 'update_sql') {
                $model->updateSchema();
                header('Location: /admin/announcements?updated=1');
                exit;
            }
            if ($state !== 'current') {
                $this->error_page('Install the Announcements database before managing announcements.');
            }
            if ($action === 'delete_data') {
                $model->deleteData();
                header('Location: /admin/announcements?deleted_data=1');
                exit;
            }

            $id = $this->validId($_POST['id'] ?? null);
            if ($action === 'delete') {
                if ($id === null) {
                    $this->invalidRecord();
                }
                $model->deleteAnnouncement($id);
            } else {
                $announcement = $this->validatedAnnouncement($_POST);
                if ($action === 'create') {
                    $model->createAnnouncement($announcement);
                } elseif ($id === null) {
                    $this->invalidRecord();
                } else {
                    $model->updateAnnouncement($id, $announcement);
                }
            }

            header('Location: /admin/announcements');
            exit;
        }

        $editItem = null;
        $editOffset = array_search('edit', $params, true);
        $requestedEditId = $editOffset !== false
            ? ($params[$editOffset + 1] ?? null)
            : ($_GET['edit'] ?? null);
        if ($requestedEditId !== null) {
            $id = $this->validId($requestedEditId);
            if ($id === null || !is_array($editItem = $model->getById($id))) {
                $this->invalidRecord();
            }
        }

        $this->view('admin/announcements', [
            'items' => $model->getAll(),
            'edit_item' => $editItem,
            'database_state' => $state,
            'installed' => isset($_GET['installed']),
            'updated' => isset($_GET['updated']),
            'deleted_data' => isset($_GET['deleted_data']),
        ]);
    }

    private function validatedAnnouncement(array $input): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $body = trim((string) ($input['body'] ?? ''));
        $contentType = strtolower(trim((string) ($input['content_type'] ?? 'markdown')));
        if ($title === '' || strlen($title) > 255 || $body === ''
            || !in_array($contentType, ['markdown', 'html', 'text'], true)) {
            http_response_code(422);
            $this->error_page('Enter a valid announcement title, body, and content format.');
        }
        return ['title' => $title, 'body' => $body, 'content_type' => $contentType,
            'published' => isset($input['published']) ? 1 : 0];
    }

    private function renderBody(string $body, string $contentType): string
    {
        return (new announcements_content($this->render_md))->render($body, $contentType);
    }

    private function validId($value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false || $id === null ? null : (int) $id;
    }

    private function invalidRecord(): void
    {
        http_response_code(400);
        $this->error_page('Invalid Announcement record.');
    }
}
/* [End AI:GPT-5.6] */
