<?php
declare(strict_types=1);

/**
 * STN-LABZ
 * Company Module Controller
 *
 * Public content:
 * - Careers
 * - Acknowledgments
 * - Strategic Relationships
 *
 * Admin content:
 * - Careers
 * - Acknowledgments
 * - Strategic Relationships
 * - public/.well-known/security.txt
 */
final class company extends controller
{
    private const PAGES = [
        'careers',
        'acknowledgments',
        'strategic',
    ];

    /**
     * Company module root.
     */
    public function index($url_params = null): void
    {
        $util = new utility();
        $util->redirect_to('/');
    }

    /**
     * Render an approved JSON-backed company page.
     *
     * Examples:
     * /company/page/careers
     * /company/page/acknowledgments
     * /company/page/strategic
     */
    public function page($url_params = null): void
    {
        $page = '';

        if (is_array($url_params)) {
            $page = trim((string)($url_params[0] ?? ''));
        } elseif (is_string($url_params)) {
            $page = trim($url_params);
        }

        if (!in_array($page, self::PAGES, true)) {
            http_response_code(404);
            return;
        }

        try {
            $contents = $this->model('company_model')->getData();
        } catch (RuntimeException $e) {
            http_response_code(503);
            return;
        }

        if (
            !isset($contents[$page])
            || !is_array($contents[$page])
        ) {
            http_response_code(404);
            return;
        }

        $data = [
            'title' => (string)($contents[$page]['title'] ?? ucfirst($page)),
            'content' => (string)($contents[$page]['content'] ?? ''),
            'page' => $page,
        ];

        $this->view('document', $data);
    }

    /**
     * Company Admin interface.
     */
    public function admin($url_params = null): void
    {
        $this->require_admin(7);

        $model = $this->model('company_model');

        $error = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();

            try {
                $action = $this->postText('action');

                switch ($action) {
                    case 'save_page':
                        $this->savePage($model);
                        break;

                    case 'save_security':
                        $this->saveSecurity($model);
                        break;

                    default:
                        throw new InvalidArgumentException(
                            'Invalid company Admin action.'
                        );
                }
            } catch (InvalidArgumentException $e) {
                http_response_code(422);
                $error = $e->getMessage();
            } catch (RuntimeException | JsonException $e) {
                http_response_code(500);
                $error = 'Unable to save company data.';
            }
        }

        try {
            $contents = $model->getData();
            $securityText = $model->getSecurityText();
        } catch (RuntimeException $e) {
            http_response_code(503);
            return;
        }

        $data = [
            'title' => 'Company Administration',
            'sub_title' => 'Manage Careers, Acknowledgments, Strategic Relationships, and security.txt.',
            'contents' => $contents,
            'pages' => self::PAGES,
            'security_text' => $securityText,
            'error' => $error,
        ];

        $this->view('admin/company', $data);
    }

    /**
     * Save a JSON-backed company page.
     */
    private function savePage(company_model $model): void
    {
        $page = $this->postText('page');

        if (!in_array($page, self::PAGES, true)) {
            throw new InvalidArgumentException(
                'Invalid company page.'
            );
        }

        $title = trim($this->postText('title'));
        $content = trim($this->postText('content'));

        if ($title === '') {
            throw new InvalidArgumentException(
                'Title is required.'
            );
        }

        $model->savePage(
            $page,
            $title,
            $content
        );

        $this->redirectAdmin('page');
    }

    /**
     * Save public/.well-known/security.txt.
     */
    private function saveSecurity(company_model $model): void
    {
        $securityText = $this->postText('security_text');

        $model->saveSecurityText($securityText);

        $this->redirectAdmin('security');
    }

    /**
     * Redirect back to Company Admin after a successful save.
     */
    private function redirectAdmin(string $saved): never
    {
        header(
            'Location: /admin/company?saved='
            . rawurlencode($saved),
            true,
            303
        );

        exit;
    }

    /**
     * Return a POST field as text.
     */
    private function postText(string $key): string
    {
        $value = $_POST[$key] ?? '';

        if (!is_string($value)) {
            throw new InvalidArgumentException(
                'Invalid form field: ' . $key
            );
        }

        return $value;
    }
}