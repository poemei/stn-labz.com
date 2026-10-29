<?php

declare(strict_types=1);

/* [AI:GPT-6 | 2026-10-29 04:17:13 UTC] */

final class support extends controller
{
    private const ADMIN_ACTIONS = [
        'install_sql', 'save_config', 'status', 'tier', 'assign', 'reply', 'delete_data',
    ];

    public function index(array $params = []): void
    {
        /** @var support_model $model */
        $model = $this->model('support_model');
        if ($model->databaseState() !== 'ready') {
            http_response_code(503);
            $this->view('index', ['available' => false, 'error' => null, 'topics' => []]);
            return;
        }

        $topics = $model->getTopics(true);
        // Use the account record rather than trusting names/emails in session.
        $contact = null;
        if ((int) ($_SESSION['user_id'] ?? 0) > 0) {
            require_once APPROOT . '/models/accounts_model.php';
            $contact = (new accounts_model())->support_contact((int) $_SESSION['user_id']);
        }
        $displayName = trim((string) ($contact['display_name'] ?? ''));
        $accountName = $displayName !== '' ? $displayName : (string) ($contact['username'] ?? '');
        $accountEmail = trim((string) ($contact['email_address'] ?? ''));
        $data = [
            'available' => true, 'error' => null, 'topics' => $topics,
            'signed_in' => $contact !== null,
            'name' => $accountName,
            'email' => $accountEmail,
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();
            $name = $contact !== null ? $accountName : trim((string) ($_POST['name'] ?? ''));
            $email = $contact !== null ? $accountEmail : trim((string) ($_POST['email'] ?? ''));
            $type = strtolower(trim((string) ($_POST['type'] ?? 'general')));
            $subject = trim((string) ($_POST['subject'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $allowedTypes = array_column($topics, 'value');

            if (!in_array($type, $allowedTypes, true)) {
                $data['error'] = 'Please select a valid Support topic.';
            } elseif ($name === '' || $subject === '' || $description === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $data['error'] = 'Please complete all required fields.';
            } else {
                $ticketId = $model->createTicket(compact('name', 'email', 'type', 'subject', 'description'));
                $ticket = $model->findTicket($ticketId);
                if ($ticket !== null) {
                    $this->sendNewTicketNotifications($model, $ticket);
                }
                header('Location: /support/ticket/' . rawurlencode($ticketId));
                exit;
            }
        }
        $this->view('index', $data);
    }

    public function ticket(array $params = []): void
    {
        /** @var support_model $model */
        $model = $this->model('support_model');
        if ($model->databaseState() !== 'ready') {
            http_response_code(503);
            $this->view('ticket', ['available' => false, 'ticket' => null, 'replies' => []]);
            return;
        }
        $ticketId = strtolower(trim((string) ($params[0] ?? '')));
        if (!preg_match('/^[a-f0-9]{6}$/', $ticketId)) {
            http_response_code(404);
            $this->view('ticket', ['available' => true, 'ticket' => null, 'replies' => []]);
            return;
        }
        $ticket = $model->findTicket($ticketId);
        if ($ticket === null) {
            http_response_code(404);
            $this->view('ticket', ['available' => true, 'ticket' => null, 'replies' => []]);
            return;
        }
        $this->view('ticket', [
            'available' => true,
            'ticket' => $ticket,
            'replies' => $model->getPublicReplies((int) $ticket['id']),
        ]);
    }

    public function admin(array $params = []): void
    {
        $this->require_admin(7);
        $manage = admin_access::tier() === 9;
        /** @var support_model $model */
        $model = $this->model('support_model');
        $state = $model->databaseState();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->require_csrf();
            $action = trim((string) ($_POST['action'] ?? ''));
            if (!in_array($action, self::ADMIN_ACTIONS, true)) {
                http_response_code(400);
                $this->error_page('Invalid Support administrative action.');
            }

            if (in_array($action, ['save_config', 'install_sql', 'delete_data'], true) && !$manage) {
                http_response_code(403);
                $this->error_page('Administrator authority is required for Support configuration and lifecycle.');
            }

            if ($action === 'save_config') {
                $this->saveConfiguration($model);
                header('Location: /admin/support?configured=1');
                exit;
            }

            if ($action === 'install_sql') {
                if ($state !== 'missing') {
                    http_response_code(409);
                    $this->error_page('The Support schema is already installed.');
                }
                $model->installSchema();
                if ($model->databaseState() !== 'ready') {
                    throw new RuntimeException('Support schema installation did not complete.');
                }
                header('Location: /admin/support?installed=1');
                exit;
            }

            if ($state !== 'ready') {
                http_response_code(409);
                $this->error_page('The Support database is not available.');
            }
            if ($action === 'delete_data') {
                $model->deleteData();
                header('Location: /admin/support?deleted=1');
                exit;
            }

            $ticketId = strtolower(trim((string) ($_POST['ticket_id'] ?? '')));
            if (!preg_match('/^[a-f0-9]{6}$/', $ticketId)) {
                http_response_code(400);
                $this->error_page('Invalid Support ticket identifier.');
            }
            $ticket = $model->findTicket($ticketId);
            if ($ticket === null) {
                http_response_code(404);
                $this->error_page('The requested Support ticket was not found.');
            }
            $this->handleTicketAction($model, $ticket, $action);
            header('Location: /admin/support/' . rawurlencode($ticketId));
            exit;
        }

        $site = $this->siteIdentity();
        if ($state !== 'ready') {
            $this->view('admin/index', [
                'manage' => $manage, 'database_state' => $state, 'tickets' => [], 'config' => $model->getConfig(),
                'site_from_name' => $site['name'], 'site_from_email' => $site['email'],
            ]);
            return;
        }

        $ticketId = strtolower(trim((string) ($params[1] ?? '')));
        if ($ticketId === '') {
            $this->view('admin/index', [
                'manage' => $manage, 'database_state' => $state, 'tickets' => array_values(array_filter($model->getTickets(),
                    static fn (array $ticket): bool => admin_access::support((string) $ticket['type'], 'read'))), 'config' => $model->getConfig(),
                'site_from_name' => $site['name'], 'site_from_email' => $site['email'],
            ]);
            return;
        }
        if (!preg_match('/^[a-f0-9]{6}$/', $ticketId)) {
            http_response_code(404);
            $this->view('admin/ticket', ['database_state'=>$state,'ticket'=>null,'replies'=>[],'events'=>[]]);
            return;
        }
        $ticket = $model->findTicket($ticketId);
        if ($ticket === null) {
            http_response_code(404);
            $this->view('admin/ticket', ['database_state'=>$state,'ticket'=>null,'replies'=>[],'events'=>[]]);
            return;
        }
        $this->requireTicket($ticket, 'read');
        $permissions = [];
        foreach (['note', 'requester_email', 'status', 'tier', 'assign'] as $operation) {
            $permissions[$operation] = admin_access::support((string) $ticket['type'], $operation);
        }
        $this->view('admin/ticket', [
            'permissions' => $permissions,
            'database_state'=>$state, 'ticket'=>$ticket,
            'replies'=>$model->getReplies((int)$ticket['id']), 'events'=>$model->getEvents((int)$ticket['id']),
        ]);
    }

    /** Enforce product scope and the actor's assigned responsibility. */
    private function requireTicket(array $ticket, string $action): void
    {
        if (!admin_access::support((string) $ticket['type'], $action)) {
            http_response_code(403);
            $this->error_page('This Support operation requires an eligible position and active mission for this system.');
        }
    }

    private function saveConfiguration(support_model $model): void
    {
        $fromName = trim((string) ($_POST['from_name'] ?? ''));
        $fromEmail = trim((string) ($_POST['from_email'] ?? ''));
        if ($fromEmail !== '' && filter_var($fromEmail, FILTER_VALIDATE_EMAIL) === false) {
            http_response_code(400);
            $this->error_page('Invalid Support From email address.');
        }

        $topics = [];
        foreach ((array) ($_POST['topics'] ?? []) as $topic) {
            if (!is_array($topic) || !empty($topic['remove'])) { continue; }
            $value = strtolower(trim((string) ($topic['value'] ?? '')));
            $label = trim((string) ($topic['label'] ?? ''));
            if ($value === '' || $label === '') { continue; }
            if (!preg_match('/^[a-z0-9_-]+$/', $value)) {
                http_response_code(400);
                $this->error_page('Support topic values may contain only lowercase letters, numbers, underscores, and hyphens.');
            }
            $topics[$value] = ['value'=>$value,'label'=>$label,'enabled'=>((string)($topic['enabled'] ?? '0') === '1')];
        }

        $newValue = strtolower(trim((string) ($_POST['new_topic_value'] ?? '')));
        $newLabel = trim((string) ($_POST['new_topic_label'] ?? ''));
        if ($newValue !== '' || $newLabel !== '') {
            if ($newValue === '' || $newLabel === '' || !preg_match('/^[a-z0-9_-]+$/', $newValue)) {
                http_response_code(400);
                $this->error_page('A new Support topic requires a valid value and label.');
            }
            $topics[$newValue] = ['value'=>$newValue,'label'=>$newLabel,'enabled'=>isset($_POST['new_topic_enabled'])];
        }
        if ($topics === []) {
            http_response_code(400);
            $this->error_page('Support must have at least one topic.');
        }
        $model->saveConfig([
            'version'=>'1',
            'mail'=>['from_name'=>$fromName,'from_email'=>$fromEmail],
            'topics'=>array_values($topics),
        ]);
    }

    private function handleTicketAction(support_model $model, array $ticket, string $action): void
    {
        $this->requireTicket($ticket, $action === 'reply' ? 'note' : $action);
        $actor = $this->adminActor();
        switch ($action) {
            case 'status':
                $status = strtolower(trim((string) ($_POST['status'] ?? '')));
                if (!in_array($status, ['open','in_progress','waiting','resolved','closed'], true)) {
                    http_response_code(400); $this->error_page('Invalid Support ticket status.');
                }
                if ($status !== (string) $ticket['status']) {
                    $model->setStatus($ticket, $status, $actor);
                    $ticket['status'] = $status;
                    $this->sendStatusNotification($model, $ticket);
                }
                break;
            case 'tier':
                $tier = strtolower(trim((string) ($_POST['tier'] ?? '')));
                if ($tier === 'dev') { $tier = 't5'; }
                if (admin_access::tier() === 1 && $tier !== 't2') {
                    http_response_code(403);
                    $this->error_page('General Support escalates to Advanced Support.');
                }
                if (!in_array($tier, ['t1','t2','t3','t4','t5'], true)) {
                    http_response_code(400); $this->error_page('Invalid Support tier.');
                }
                $note = trim((string) ($_POST['note'] ?? ''));
                $oldTier = strtolower((string) ($ticket['tier'] ?? 't1'));
                if ($oldTier === 'dev') { $oldTier = 't5'; }
                /* [AI:GPT-6 | 2026-10-29 UTC]
                 * Escalation targets the receiving tier queue, never a person.
                 * Preserve the original ticket and both audit events.
                 */
                $model->escalateTicket($ticket, $tier, 'tier:' . $tier, $actor, $note !== '' ? $note : null);
                /* [End AI:GPT-6] */
                if ($tier !== $oldTier) {
                    $ticket['tier'] = $tier;
                    $this->sendTierNotification($model, $ticket, $actor, $note !== '' ? $note : null);
                    $this->sendRequesterTierNotification($model, $ticket);
                }
                break;
            case 'assign':
                // [AI:GPT-6 | 2026-10-29 UTC] Assignment selects an operational
                // tier destination; it cannot select or impersonate a person.
                $target = strtolower(trim((string) ($_POST['destination'] ?? '')));
                if (!in_array($target, ['t1', 't2', 't3', 't4', 't5'], true)) {
                    http_response_code(400);
                    $this->error_page('Invalid Support destination tier.');
                }
                if (admin_access::tier() === 1 && $target !== 't2') {
                    http_response_code(403);
                    $this->error_page('General Support assigns escalations to Tier 2.');
                }
                $priorTier = (string) ($ticket['tier'] ?? 't1');
                if ($priorTier === 'dev') { $priorTier = 't5'; }
                $model->escalateTicket($ticket, $target, 'tier:' . $target, $actor, null);
                $ticket['tier'] = $target;
                $ticket['assigned_to'] = 'tier:' . $target;
                if ($target !== $priorTier) {
                    $this->sendTierNotification($model, $ticket, $actor, null);
                    $this->sendRequesterTierNotification($model, $ticket);
                }
                // [End AI:GPT-6]
                break;
            case 'reply':
                $message = trim((string) ($_POST['message'] ?? ''));
                if ($message === '') { http_response_code(400); $this->error_page('A Support reply cannot be empty.'); }
                $isInternal = isset($_POST['is_internal']);
                if (!$isInternal) {
                    $this->requireTicket($ticket, 'requester_email');
                }
                $model->addReply((int)$ticket['id'], $actor, $message, $isInternal);
                if (!$isInternal) { $this->sendReplyNotification($model, $ticket, $actor, $message); }
                break;
            default:
                http_response_code(400); $this->error_page('Invalid Support ticket action.');
        }
    }

    private function sendNewTicketNotifications(support_model $model, array $ticket): void
    {
        $id=(string)$ticket['ticket_id']; $subject=(string)$ticket['subject'];
        $identity=$this->supportIdentity($model);
        if ($identity['email'] !== '') {
            $this->sendMail($model, $identity['email'], $identity['name'], '[Support #'.$id.'] '.$subject,
                '<h2>New Support Ticket #'.$this->escapeHtml($id).'</h2><p><strong>From:</strong> '.$this->escapeHtml((string)$ticket['name']).' ('.$this->escapeHtml((string)$ticket['email']).')</p><p><strong>Type:</strong> '.$this->escapeHtml(strtoupper((string)$ticket['type'])).'</p><p><strong>Subject:</strong> '.$this->escapeHtml($subject).'</p><p>'.$this->escapeHtml((string)$ticket['description']).'</p><p><a href="'.URLROOT.'/admin/support/'.rawurlencode($id).'">Open ticket in Admin</a></p>');
        }
        $this->sendMail($model, (string)$ticket['email'], (string)$ticket['name'], '[Support #'.$id.'] Ticket received',
            '<p>Hello '.$this->escapeHtml((string)$ticket['name']).',</p><p>Your Support ticket <strong>#'.$this->escapeHtml($id).'</strong> has been received.</p><p><a href="'.URLROOT.'/support/ticket/'.rawurlencode($id).'">View your Support ticket</a></p>');
    }

    private function sendReplyNotification(support_model $model, array $ticket, string $actor, string $message): void
    {
        if (!admin_access::support((string) $ticket['type'], 'requester_email')) {
            return;
        }
        $id=(string)$ticket['ticket_id'];
        $this->sendMail($model,(string)$ticket['email'],(string)$ticket['name'],'[Support #'.$id.'] Support response',
            '<p>Hello '.$this->escapeHtml((string)$ticket['name']).',</p><p>'.$this->escapeHtml($actor).' responded to your Support ticket.</p><p>'.$this->escapeHtml($message).'</p><p><a href="'.URLROOT.'/support/ticket/'.rawurlencode($id).'">View ticket #'.$this->escapeHtml($id).'</a></p>');
    }

    private function sendTierNotification(support_model $model, array $ticket, string $actor, ?string $note): void
    {
        $id=(string)$ticket['ticket_id']; $identity=$this->supportIdentity($model);
        if ($identity['email'] === '') { return; }
        $tier=$this->tierLabel((string)$ticket['tier']);
        $body='<p>Ticket <strong>#'.$this->escapeHtml($id).'</strong> was moved to <strong>'.$this->escapeHtml($tier).'</strong> by '.$this->escapeHtml($actor).'.</p>';
        if ($note !== null) { $body.='<p><strong>Note:</strong> '.$this->escapeHtml($note).'</p>'; }
        $body.='<p><a href="'.URLROOT.'/admin/support/'.rawurlencode($id).'">Open ticket in Admin</a></p>';
        $this->sendMail($model,$identity['email'],$identity['name'],'[Support #'.$id.'] Tier changed to '.$tier,$body);
    }

    /** Public escalation notice: no internal findings, assignee or staff notes. */
    private function sendRequesterTierNotification(support_model $model, array $ticket): void
    {
        // [AI:GPT-6 | 2026-10-29 UTC] Ticket lifecycle mail is automatic.
        $id = (string) $ticket['ticket_id'];
        $tier = $this->tierLabel((string) $ticket['tier']);
        $this->sendMail(
            $model,
            (string) $ticket['email'],
            (string) $ticket['name'],
            '[Support #'.$id.'] Ticket transferred',
            '<p>Hello '.$this->escapeHtml((string) $ticket['name']).',</p>'
            .'<p>Your Support ticket <strong>#'.$this->escapeHtml($id)
            .'</strong> has been transferred to '.$this->escapeHtml($tier).'.</p>'
            .'<p><a href="'.URLROOT.'/support/ticket/'.rawurlencode($id)
            .'">View your ticket</a></p>'
        );
        // [End AI:GPT-6]
    }

    private function sendStatusNotification(support_model $model, array $ticket): void
    {
        // [AI:GPT-6 | 2026-10-29 UTC] Automated ticket-state notices
        // reflect a permitted state change, not an agent's right to send
        // manual requester correspondence. Never include internal notes.
        $id=(string)$ticket['ticket_id']; $status=strtoupper(str_replace('_',' ',(string)$ticket['status']));
        $this->sendMail($model,(string)$ticket['email'],(string)$ticket['name'],'[Support #'.$id.'] '.$status,
            '<p>Hello '.$this->escapeHtml((string)$ticket['name']).',</p><p>Your Support ticket <strong>#'.$this->escapeHtml($id).'</strong> is now <strong>'.$this->escapeHtml($status).'</strong>.</p><p><a href="'.URLROOT.'/support/ticket/'.rawurlencode($id).'">View your Support ticket</a></p>');
    }

    private function sendUpdateNotification(support_model $model, array $ticket, string $update): void
    {
        if (!admin_access::support((string) $ticket['type'], 'requester_email')) {
            return;
        }
        $id=(string)$ticket['ticket_id'];
        $this->sendMail($model,(string)$ticket['email'],(string)$ticket['name'],'[Support #'.$id.'] '.$update,
            '<p>Hello '.$this->escapeHtml((string)$ticket['name']).',</p><p>'.$this->escapeHtml($update).' for ticket <strong>#'.$this->escapeHtml($id).'</strong>.</p>');
    }

    private function sendMail(support_model $model, string $address, string $name, string $subject, string $body): void
    {
        try {
            $mailObj=new mailer(); $mail=$mailObj->create();
            $identity=$this->supportIdentity($model);
            if ($identity['email'] !== '') { $mail->setFrom($identity['email'], $identity['name']); }
            $mail->addAddress($address,$name); $mail->isHTML(true); $mail->Subject=$subject; $mail->Body=$body; $mail->send();
        } catch (Throwable $e) {
            error_log('Support email delivery failed: ' . $e->getMessage());
        }
    }

    private function supportIdentity(support_model $model): array
    {
        $config=$model->getConfig(); $mail=is_array($config['mail']??null)?$config['mail']:[]; $site=$this->siteIdentity();
        $name=trim((string)($mail['from_name']??'')); $email=trim((string)($mail['from_email']??''));
        if ($name==='') { $name=$site['name'] !== '' ? $site['name'] : 'Support'; }
        if ($email==='') { $email=$site['email']; }
        return ['name'=>$name,'email'=>$email];
    }

    private function siteIdentity(): array
    {
        global $SITE;
        $site=is_array($SITE??null)?$SITE:[];
        $name=trim((string)($site['name']??$site['site_name']??$site['title']??''));
        $email=trim((string)($site['email']??$site['site_email']??$site['contact_email']??''));
        return ['name'=>$name,'email'=>filter_var($email,FILTER_VALIDATE_EMAIL)!==false?$email:''];
    }

    private function tierLabel(string $tier): string
    {
        $tier=strtolower($tier); if($tier==='dev'){$tier='t5';}
        return ['t1'=>'Tier 1','t2'=>'Tier 2','t3'=>'Tier 3','t4'=>'Tier 4','t5'=>'Tier 5 — Support Manager'][$tier]??strtoupper($tier);
    }

    private function escapeHtml(string $value): string { return htmlspecialchars($value,ENT_QUOTES,'UTF-8'); }

    private function adminActor(): string
    {
        $displayName=trim((string)($_SESSION['display_name']??'')); if($displayName!==''){return $displayName;}
        $username=trim((string)($_SESSION['username']??'')); return $username!==''?$username:'Admin';
    }
}

/* [End AI:GPT-6] */
