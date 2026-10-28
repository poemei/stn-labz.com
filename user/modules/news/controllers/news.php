<?php

declare(strict_types=1);

// path: /user/modules/news/controllers/news.php

/**
 * ChAoS MVC News Controller
 *
 * Installation-owned News module for chaos-mvc.org.
 *
 * Handles public news, article routing, and authenticated
 * administration. Content remains owned by news_model.
 *
 * [AI:GPT-6 | 2026-10-22 UTC]
 */

final class news extends controller
{
    private const CRUD_ACTIONS = [
        'create',
        'update',
        'delete',
    ];

    private const LIFECYCLE_ACTIONS = [
        'install_sql',
        'update_sql',
    ];

    /**
     * Public news listing or individual article.
     */
    public function index(array $params = []): void
    {
        $model = $this->model('news_model');

        if (count($params) > 1) {
            http_response_code(404);
            $this->error_page('News article not found.');
        }

        if (count($params) === 1) {
            $slug = (string) $params[0];

            if (
                !preg_match(
                    '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    $slug
                )
            ) {
                http_response_code(404);
                $this->error_page('News article not found.');
            }

            $article = $model->getPublishedArticle($slug);

            if ($article === null) {
                http_response_code(404);
                $this->error_page('News article not found.');
            }

            $this->view('article', [
                'article' => $article,
            ]);

            return;
        }

        $this->view('index', [
            'articles' => $model->getPublishedArticles(),
        ]);
    }

    /**
     * Authenticated News administration.
     */
    public function admin(array $params = []): void
    {
        $this->require_admin(7);

        $model = $this->model('news_model');
        $state = $model->databaseState();
        $error = null;

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();

            $action = trim(
                (string) ($_POST['action'] ?? '')
            );

            try {
                if (
                    in_array(
                        $action,
                        self::LIFECYCLE_ACTIONS,
                        true
                    )
                ) {
                    $this->handleLifecycleAction(
                        $model,
                        $state,
                        $action
                    );
                }

                if ($state !== 'current') {
                    throw new RuntimeException(
                        'Complete the News database lifecycle first.'
                    );
                }

                if (
                    !in_array(
                        $action,
                        self::CRUD_ACTIONS,
                        true
                    )
                ) {
                    throw new InvalidArgumentException(
                        'Invalid News administration action.'
                    );
                }

                if ($action === 'create') {
                    $model->createArticle($_POST);
                    $this->redirectAdmin('created');
                }

                if ($action === 'update') {
                    $model->updateArticle($_POST);
                    $this->redirectAdmin('updated');
                }

                $model->deleteArticle(
                    (int) ($_POST['id'] ?? 0)
                );

                $this->redirectAdmin('deleted');
            } catch (
                InvalidArgumentException | RuntimeException $exception
            ) {
                $error = $exception->getMessage();
            } catch (Throwable $exception) {
                error_log(
                    'News administration failure: '
                    . $exception->getMessage()
                );

                $error = 'The News operation could not be completed.';
            }
        }

        $state = $model->databaseState();

        $this->view('admin/index', [
            'module' => $model->getModuleInformation(),
            'database_state' => $state,
            'articles' => $state === 'current'
                ? $model->getAllArticles()
                : [],
            'message' => $this->statusMessage(
                trim((string) ($_GET['status'] ?? ''))
            ),
            'error' => $error,
        ]);
    }

    /**
     * Perform module schema lifecycle actions.
     */
    private function handleLifecycleAction(
        news_model $model,
        string $state,
        string $action
    ): never {
        if ($action === 'install_sql') {
            if ($state !== 'missing') {
                throw new RuntimeException(
                    'News schema is already installed.'
                );
            }

            $model->installSchema();
            $this->redirectAdmin('installed');
        }

        if ($action === 'update_sql') {
            if ($state !== 'update') {
                throw new RuntimeException(
                    'No News schema update is pending.'
                );
            }

            $model->updateSchema();
            $this->redirectAdmin('schema_updated');
        }

        throw new InvalidArgumentException(
            'Invalid News lifecycle action.'
        );
    }

    /**
     * Resolve administration status messages.
     */
    private function statusMessage(
        string $status
    ): ?string {
        $messages = [
            'installed' => 'News schema installed.',
            'schema_updated' => 'News schema updated.',
            'created' => 'News article created.',
            'updated' => 'News article updated.',
            'deleted' => 'News article deleted.',
        ];

        return $messages[$status] ?? null;
    }

    /**
     * Redirect after successful administration action.
     */
    private function redirectAdmin(
        string $status
    ): never {
        header(
            'Location: /admin/news?status='
            . rawurlencode($status)
        );

        exit;
    }
}

/* [End AI:GPT-6 | 2026-10-22] */
