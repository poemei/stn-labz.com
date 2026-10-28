<?php
declare(strict_types=1);

// path: /user/modules/legal/controllers/legal.php

/*
 * [AI:GPT-5.6 Sol | 2026-09-01 21:49:00 UTC]
 */

/**
 * Legal module controller.
 */
class legal extends controller
{
    /**
     * Allowed module-owned Admin actions.
     */
    private const ADMIN_ACTIONS = [
        'save_document',
    ];

    /**
     * Display the Terms of Use.
     *
     * @return void
     */
    public function terms(): void
    {
        $model = $this->model('legal_model');

        $this->view('terms', [
            'document' => $model->getDocument('terms'),
        ]);
    }

    /**
     * Display the Privacy Policy.
     *
     * @return void
     */
    public function privacy(): void
    {
        $model = $this->model('legal_model');

        $this->view('privacy', [
            'document' => $model->getDocument('privacy'),
        ]);
    }

    /**
     * Display and process module administration.
     *
     * Module-local models are loaded here rather than in the constructor
     * because Chaos MVC establishes module context during Admin dispatch.
     *
     * @param array $params Route parameters.
     *
     * @return void
     */
    public function admin($params = []): void
    {
        $this->require_admin(7);

        $model = $this->model('legal_model');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();

            $action = (string) ($_POST['action'] ?? '');

            if (!in_array($action, self::ADMIN_ACTIONS, true)) {
                http_response_code(400);
                $this->error_page('Invalid Legal module action.');
                return;
            }

            if ($action === 'save_document') {
                $model->saveDocument(
                    (string) ($_POST['document'] ?? ''),
                    (string) ($_POST['title'] ?? ''),
                    (string) ($_POST['content'] ?? '')
                );
            }

            header('Location: /admin/legal');
            exit;
        }

        $this->view('admin/legal', [
            'module' => $model->getModuleInformation(),
            'documents' => $model->getDocuments(),
        ]);
    }
}

/* [End AI:GPT-5.6 Sol] */