<?php

declare(strict_types=1);

/**
 * Letters Controller
 *
 * Path: /user/modules/letters/controllers/letters.php
 */

/* [AI:GPT-5.6 | 2026-09-01 02:15:00 UTC] */
final class letters extends controller
{
    private const ADMIN_ACTIONS = [
        'install_sql',
        'update_sql',
        'broadcast',
        'delete_letter',
        'activate_subscriber',
        'deactivate_subscriber',
        'delete_subscriber',
    ];

    /** @param array<int, string> $params */
    public function index(array $params = []): void
    {
        if ($params !== []) {
            $this->notFound();
        }

        $model = $this->model('letters_model');

        if ($model->databaseState() !== 'ready') {
            http_response_code(503);
            $this->error_page('Newsletter subscriptions are temporarily unavailable.');
        }

        $data = [
            'subscribed' => isset($_GET['subscribed']),
            'errors' => [],
            'values' => [],
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->view('index', $data);
            return;
        }

        $this->require_csrf();

        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            header('Location: /letters?subscribed=1');
            exit;
        }

        $subscription = $this->validateSubscription($_POST, $data['errors']);
        $data['values'] = $subscription;

        if ($data['errors'] !== []) {
            $this->view('index', $data);
            return;
        }

        $model->subscribe($subscription);
        header('Location: /letters?subscribed=1');
        exit;
    }

    /** @param array<int, string> $params */
    public function unsubscribe(array $params = []): void
    {
        $token = count($params) === 1 ? trim((string) $params[0]) : '';

        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            $this->notFound();
        }

        $model = $this->model('letters_model');

        if ($model->databaseState() !== 'ready') {
            $this->notFound();
        }

        $subscriber = $model->getSubscriberByToken($token);

        if (!is_array($subscriber)) {
            $this->notFound();
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();
            $model->unsubscribeByToken($token);

            $this->view('unsubscribe', [
                'subscriber' => $subscriber,
                'completed' => true,
                'token' => $token,
            ]);
            return;
        }

        $this->view('unsubscribe', [
            'subscriber' => $subscriber,
            'completed' => false,
            'token' => $token,
        ]);
    }

    /** @param array<int, string> $params */
    public function admin(array $params = []): void
    {
        $this->require_admin(7);
        $model = $this->model('letters_model');
        $state = $model->databaseState();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();
            $action = trim((string) ($_POST['action'] ?? ''));

            if (!in_array($action, self::ADMIN_ACTIONS, true)) {
                http_response_code(400);
                $this->error_page('Invalid Letters module action.');
            }

            if ($action === 'install_sql') {
                if ($state !== 'missing') {
                    http_response_code(409);
                    $this->error_page('Letters database installation is not required.');
                }

                $model->installSql();
                header('Location: /admin/letters?database=installed');
                exit;
            }

            if ($action === 'update_sql') {
                if ($state !== 'update') {
                    http_response_code(409);
                    $this->error_page('Letters database update is not required.');
                }

                $model->updateSql();
                header('Location: /admin/letters?database=updated');
                exit;
            }

            if ($state !== 'ready') {
                $this->renderAdmin($model, $state, [
                    'Complete the database lifecycle action before managing Letters.',
                ]);
                return;
            }

            if ($action === 'broadcast') {
                $errors = [];
                $letter = $this->validateLetter($_POST, $errors);

                if ($errors !== []) {
                    $this->renderAdmin($model, 'ready', $errors, $letter);
                    return;
                }

                $letterId = $model->createLetter($letter);
                $sent = $this->broadcastLetter($model->getActiveSubscribers(), $letter);
                $model->setRecipientCount($letterId, $sent);
                header('Location: /admin/letters?broadcast=' . $sent);
                exit;
            }

            $id = $this->validId($_POST['id'] ?? null);

            if ($id === null) {
                http_response_code(400);
                $this->error_page('Invalid Letters record.');
            }

            if ($action === 'delete_letter') {
                $model->deleteLetter($id);
            } elseif ($action === 'activate_subscriber') {
                $model->setSubscriberActive($id, true);
            } elseif ($action === 'deactivate_subscriber') {
                $model->setSubscriberActive($id, false);
            } else {
                $model->deleteSubscriber($id);
            }

            header('Location: /admin/letters?saved=1');
            exit;
        }

        $this->renderAdmin($model, $state);
    }

    /**
     * @param array<string, mixed> $input
     * @param array<int, string>   $errors
     * @return array{name: string, email: string}
     */
    private function validateSubscription(array $input, array &$errors): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));

        if ($name === '' || strlen($name) > 120) {
            $errors[] = 'Name is required and must not exceed 120 characters.';
        }

        if (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Enter a valid email address.';
        }

        if (!isset($input['consent'])) {
            $errors[] = 'Consent is required to join the newsletter.';
        }

        return ['name' => $name, 'email' => $email];
    }

    /**
     * @param array<string, mixed> $input
     * @param array<int, string>   $errors
     * @return array{subject: string, body: string}
     */
    private function validateLetter(array $input, array &$errors): array
    {
        $subject = trim((string) ($input['subject'] ?? ''));
        $body = trim((string) ($input['body'] ?? ''));

        if ($subject === '' || strlen($subject) > 255) {
            $errors[] = 'Subject is required and must not exceed 255 characters.';
        }

        if ($body === '' || strlen($body) > 100000) {
            $errors[] = 'Message is required and must not exceed 100,000 characters.';
        }

        return ['subject' => $subject, 'body' => $body];
    }

    /**
     * @param array<int, array<string, mixed>> $subscribers
     * @param array{subject: string, body: string} $letter
     */
    private function broadcastLetter(array $subscribers, array $letter): int
    {
        require_once APPROOT . '/lib/mailer.php';
        $sent = 0;
        $bodyHtml = $letter['body'] === strip_tags($letter['body'])
            ? nl2br($this->escape($letter['body']))
            : $letter['body'];

        foreach ($subscribers as $subscriber) {
            try {
                $mail = (new mailer())->create();
                $unsubscribeUrl = rtrim((string) URLROOT, '/')
                    . '/letters/unsubscribe/'
                    . rawurlencode((string) $subscriber['unsubscribe_token']);

                $mail->addAddress((string) $subscriber['email'], (string) $subscriber['name']);
                $mail->isHTML(true);
                $mail->Subject = $letter['subject'];
                $mail->Body = $bodyHtml
                    . '<hr><p><small><a href="'
                    . $this->escape($unsubscribeUrl)
                    . '">Unsubscribe from this newsletter</a></small></p>';
                $mail->AltBody = strip_tags($letter['body'])
                    . "\n\nUnsubscribe: " . $unsubscribeUrl;
                $mail->send();
                $sent++;
            } catch (Throwable $exception) {
                error_log('Letters broadcast error: ' . $exception->getMessage());
            }
        }

        return $sent;
    }

    /**
     * @param array<int, string>    $errors
     * @param array<string, mixed> $letterValues
     */
    private function renderAdmin(
        letters_model $model,
        string $state,
        array $errors = [],
        array $letterValues = []
    ): void {
        $ready = $state === 'ready';

        $this->view('admin/index', [
            'database_state' => $state,
            'letters' => $ready ? $model->getLetters() : [],
            'subscribers' => $ready ? $model->getSubscribers() : [],
            'active_count' => $ready ? $model->countActiveSubscribers() : 0,
            'errors' => $errors,
            'letter_values' => $letterValues,
            'saved' => isset($_GET['saved']),
            'broadcast' => isset($_GET['broadcast']) ? max(0, (int) $_GET['broadcast']) : null,
            'database_action' => isset($_GET['database'])
                ? trim((string) $_GET['database'])
                : '',
        ]);
    }

    private function validId($value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false || $id === null ? null : (int) $id;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->error_page('The requested newsletter resource does not exist.');
    }
}
/* [End AI:GPT-5.6] */
