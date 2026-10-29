<?php
/* [AI:GPT-6 | 2026-10-29 04:17:13 UTC] */


/* [AI:GPT-5.6 Sol | 2026-10-05 UTC] */

/**
 * Support administration queue, configuration, and lifecycle controls.
 *
 * @var array<string, mixed> $data
 */

if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}

$escape = static fn ($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$databaseState = (string) ($data['database_state'] ?? 'missing');
$tickets = $data['tickets'] ?? [];
$config = is_array($data['config'] ?? null) ? $data['config'] : [];
$mailConfig = is_array($config['mail'] ?? null) ? $config['mail'] : [];
$topics = is_array($config['topics'] ?? null) ? $config['topics'] : [];
$siteFromName = (string) ($data['site_from_name'] ?? '');
$siteFromEmail = (string) ($data['site_from_email'] ?? '');
?>

<main class="container py-4">
    <h1>Support</h1>

    <?php if (isset($_GET['installed'])): ?>
        <div class="alert alert-success">Support database installed.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Support data deleted.</div>
    <?php endif; ?>
    <?php if (isset($_GET['configured'])): ?>
        <div class="alert alert-success">Support configuration saved.</div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-4" id="supportAdminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button
                class="nav-link active"
                id="tickets-tab"
                data-bs-toggle="tab"
                data-bs-target="#tickets-pane"
                type="button"
                role="tab"
                aria-controls="tickets-pane"
                aria-selected="true"
            >Tickets</button>
        </li>
        <?php if (!empty($data['manage'])): ?>
        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                id="config-tab"
                data-bs-toggle="tab"
                data-bs-target="#config-pane"
                type="button"
                role="tab"
                aria-controls="config-pane"
                aria-selected="false"
            >Config</button>
        </li>
        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                id="lifecycle-tab"
                data-bs-toggle="tab"
                data-bs-target="#lifecycle-pane"
                type="button"
                role="tab"
                aria-controls="lifecycle-pane"
                aria-selected="false"
            >Lifecycle</button>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content" id="supportAdminTabContent">
        <div
            class="tab-pane fade show active"
            id="tickets-pane"
            role="tabpanel"
            aria-labelledby="tickets-tab"
            tabindex="0"
        >
            <?php if ($databaseState !== 'ready'): ?>
                <div class="alert alert-warning">
                    <strong>Database not installed.</strong>
                    Support cannot accept tickets until its database tables have been created.
                    Use the <strong>Lifecycle</strong> tab to install the schema.
                </div>
            <?php else: ?>
                <p>Manage support tickets, assignment, support tier, escalation, and lifecycle.</p>

                <?php if ($tickets === []): ?>
                    <div class="alert alert-info">
                        No tickets are available within your currently authorized Support mission scope.
                        Ticket visibility is determined by the ticket type and your active, matching system mission,
                        certification, and Support responsibility. An empty list does not mean that no tickets exist.
                    </div>
                <?php else: ?>
                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Tier</th>
                                    <th>Assigned</th>
                                    <th>Reported</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                                <?php
                                $tier = strtolower((string) ($ticket['tier'] ?? 't1'));
                                if ($tier === 'dev') {
                                    $tier = 't5';
                                }
                                $tierLabels = [
                                    't1' => 'Tier 1',
                                    't2' => 'Tier 2',
                                    't3' => 'Tier 3',
                                    't4' => 'Tier 4',
                                    't5' => 'Tier 5 — Support Manager',
                                ];
                                ?>
                                <tr>
                                    <td><a href="/admin/support/<?= $escape($ticket['ticket_id']); ?>">#<?= $escape($ticket['ticket_id']); ?></a></td>
                                    <td><?= $escape(ucfirst((string) $ticket['type'])); ?></td>
                                    <td><?= $escape($ticket['subject']); ?></td>
                                    <td><?= $escape(strtoupper(str_replace('_', ' ', (string) $ticket['status']))); ?></td>
                                    <td><?= $escape($tierLabels[$tier] ?? strtoupper($tier)); ?></td>
                                    <td><?= $escape($ticket['assigned_to'] ?? 'Unassigned'); ?></td>
                                    <td><?= $escape($ticket['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($data['manage'])): ?>
        <div
            class="tab-pane fade"
            id="config-pane"
            role="tabpanel"
            aria-labelledby="config-tab"
            tabindex="0"
        >
            <section class="border rounded p-4 mb-4">
                <h2 class="h4">Configuration</h2>
                <p>
                    Support inherits the site identity when an override is blank.
                    Use overrides when Support needs its own sender identity, such as
                    support@example.com.
                </p>

                <form method="post" action="/admin/support">
                    <?= $this->csrf_field(); ?>
                    <input type="hidden" name="action" value="save_config">

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="support-from-name">From Name</label>
                            <input
                                id="support-from-name"
                                class="form-control"
                                type="text"
                                name="from_name"
                                maxlength="150"
                                value="<?= $escape($mailConfig['from_name'] ?? ''); ?>"
                            >
                            <div class="form-text">
                                Inherited: <?= $escape($siteFromName !== '' ? $siteFromName : 'Not supplied by site.json'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="support-from-email">From Email</label>
                            <input
                                id="support-from-email"
                                class="form-control"
                                type="email"
                                name="from_email"
                                maxlength="254"
                                value="<?= $escape($mailConfig['from_email'] ?? ''); ?>"
                            >
                            <div class="form-text">
                                Inherited: <?= $escape($siteFromEmail !== '' ? $siteFromEmail : 'Not supplied by site.json'); ?>
                            </div>
                        </div>
                    </div>

                    <h3 class="h5">Topics</h3>
                    <p class="text-muted">
                        Topic values are stable ticket identifiers. Labels are what users see.
                        Disable a topic instead of renaming its value when historical tickets use it.
                    </p>

                    <div class="table-responsive mb-3">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Value</th>
                                    <th>Label</th>
                                    <th>Enabled</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($topics as $index => $topic): ?>
                                <?php if (!is_array($topic)) { continue; } ?>
                                <tr>
                                    <td>
                                        <input
                                            class="form-control"
                                            type="text"
                                            name="topics[<?= (int) $index; ?>][value]"
                                            value="<?= $escape($topic['value'] ?? ''); ?>"
                                            maxlength="64"
                                        >
                                    </td>
                                    <td>
                                        <input
                                            class="form-control"
                                            type="text"
                                            name="topics[<?= (int) $index; ?>][label]"
                                            value="<?= $escape($topic['label'] ?? ''); ?>"
                                            maxlength="100"
                                        >
                                    </td>
                                    <td>
                                        <input type="hidden" name="topics[<?= (int) $index; ?>][enabled]" value="0">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="topics[<?= (int) $index; ?>][enabled]"
                                            value="1"
                                            <?= !empty($topic['enabled']) ? 'checked' : ''; ?>
                                        >
                                    </td>
                                    <td>
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="topics[<?= (int) $index; ?>][remove]"
                                            value="1"
                                        >
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <tr>
                                <td>
                                    <input class="form-control" type="text" name="new_topic_value" maxlength="64" placeholder="new_topic">
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="new_topic_label" maxlength="100" placeholder="New Topic">
                                </td>
                                <td>
                                    <input class="form-check-input" type="checkbox" name="new_topic_enabled" value="1" checked>
                                </td>
                                <td><span class="text-muted">New</span></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Configuration</button>
                </form>
            </section>
        </div>

        <div
            class="tab-pane fade"
            id="lifecycle-pane"
            role="tabpanel"
            aria-labelledby="lifecycle-tab"
            tabindex="0"
        >
            <section class="border rounded p-4 mb-4">
                <h2 class="h4">Database Lifecycle</h2>
                <?php if ($databaseState === 'missing'): ?>
                    <div class="alert alert-warning">The Support database schema is not installed.</div>
                    <form method="post" action="/admin/support">
                        <?= $this->csrf_field(); ?>
                        <input type="hidden" name="action" value="install_sql">
                        <button type="submit" class="btn btn-primary">Install SQL</button>
                    </form>
                <?php elseif ($databaseState === 'update'): ?>
                    <div class="alert alert-warning">The Support database schema requires an update.</div>
                    <form method="post" action="/admin/support">
                        <?= $this->csrf_field(); ?>
                        <input type="hidden" name="action" value="update_sql">
                        <button type="submit" class="btn btn-primary">Update SQL</button>
                    </form>
                <?php elseif ($databaseState === 'ready'): ?>
                    <div class="alert alert-success">The Support database schema is current.</div>
                <?php else: ?>
                    <div class="alert alert-danger">The Support database state is invalid.</div>
                <?php endif; ?>
            </section>

            <section class="border rounded p-4 mb-4">
                <h2 class="h4">Data Lifecycle</h2>
                <p>Delete all Support tickets, replies, and lifecycle history while preserving the Support database schema.</p>
                <?php if ($databaseState === 'ready'): ?>
                    <form method="post" action="/admin/support" onsubmit="return confirm('Delete all Support data? This cannot be undone.');">
                        <?= $this->csrf_field(); ?>
                        <input type="hidden" name="action" value="delete_data">
                        <button type="submit" class="btn btn-warning">Delete Data</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">Delete Data requires a current Support database schema.</div>
                <?php endif; ?>
            </section>

            <section class="border rounded p-4">
                <h2 class="h4">Module Lifecycle</h2>
                <p>Module update and rollback are managed and enforced by ChAoS Core through Admin &rarr; Modules.</p>
                <form method="post" action="/admin/uninstall" onsubmit="return confirm('Nuke the Support module? This cannot be undone.');">
                    <?= $this->csrf_field(); ?>
                    <input type="hidden" name="module" value="support">
                    <button type="submit" class="btn btn-danger">Nuke Module</button>
                </form>
            </section>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}
/* [End AI:GPT-5.6 Sol] */

/* [End AI:GPT-6] */
