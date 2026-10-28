<?php

/** Letters Administration View. */

/* [AI:GPT-5.6 | 2026-09-01 02:15:00 UTC] */
if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}

$state = (string) ($data['database_state'] ?? 'missing');
$letters = is_array($data['letters'] ?? null) ? $data['letters'] : [];
$subscribers = is_array($data['subscribers'] ?? null) ? $data['subscribers'] : [];
$errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];
$values = is_array($data['letter_values'] ?? null) ? $data['letter_values'] : [];
$escape = static fn ($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
?>
<p><small><a href="/admin">Admin</a> &gt;&gt; <strong>Letters</strong></small></p>

<main class="container my-4" aria-labelledby="letters-admin-title">
    <header class="mb-4">
        <h1 id="letters-admin-title">Letters</h1>
        <p class="text-body-secondary mb-0">Manage opt-in subscribers and broadcasts.</p>
    </header>

    <?php if ($state === 'missing' || $state === 'update') : ?>
        <div class="alert alert-warning" role="status">
            <h2 class="h5">Database <?= $state === 'missing' ? 'installation' : 'update'; ?> required</h2>
            <p>
                <?= $state === 'missing'
                    ? 'The module-owned newsletter tables are not installed.'
                    : 'Preserve the letter archive and add the standalone subscriber list.'; ?>
            </p>

            <form method="post" action="/admin/letters">
                <?= $this->csrf_field(); ?>
                <input
                    type="hidden"
                    name="action"
                    value="<?= $state === 'missing' ? 'install_sql' : 'update_sql'; ?>"
                >
                <button
                    class="btn btn-warning"
                    type="submit"
                    onclick="return confirm('<?= $state === 'missing'
                        ? 'Install the Letters database schema?'
                        : 'Apply the Letters database update? Existing letter history will be preserved.'; ?>');"
                >
                    <?= $state === 'missing' ? 'Install SQL' : 'Update SQL'; ?>
                </button>
            </form>
        </div>
    <?php else : ?>
        <?php if (($data['database_action'] ?? '') === 'installed') : ?>
            <div class="alert alert-light border" role="status">Letters database installed.</div>
        <?php elseif (($data['database_action'] ?? '') === 'updated') : ?>
            <div class="alert alert-light border" role="status">Letters database updated.</div>
        <?php endif; ?>
        <?php if (!empty($data['saved'])) : ?>
            <div class="alert alert-light border" role="status">Letters updated.</div>
        <?php endif; ?>
        <?php if ($data['broadcast'] !== null) : ?>
            <div class="alert alert-light border" role="status">
                Letter sent to <?= (int) $data['broadcast']; ?> subscriber(s).
            </div>
        <?php endif; ?>
        <?php if ($errors !== []) : ?>
            <div class="alert alert-danger" role="alert"><ul class="mb-0">
                <?php foreach ($errors as $error) : ?><li><?= $escape($error); ?></li><?php endforeach; ?>
            </ul></div>
        <?php endif; ?>

        <section class="card card-body mb-5" aria-labelledby="compose-title">
            <div class="d-flex justify-content-between gap-2 mb-3">
                <h2 class="h4 mb-0" id="compose-title">Compose Letter</h2>
                <span class="text-body-secondary"><?= (int) ($data['active_count'] ?? 0); ?> active</span>
            </div>
            <form method="post" action="/admin/letters">
                <?= $this->csrf_field(); ?>
                <input type="hidden" name="action" value="broadcast">
                <div class="mb-3">
                    <label class="form-label" for="letter-subject">Subject</label>
                    <input class="form-control" id="letter-subject" name="subject"
                        maxlength="255" required value="<?= $escape($values['subject'] ?? 'News'); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="letter-body">Message</label>
                    <textarea class="form-control" id="letter-body" name="body" rows="12"
                        required><?= $escape($values['body'] ?? ''); ?></textarea>
                    <div class="form-text">Plain text and trusted HTML are supported.</div>
                </div>
                <button class="btn btn-primary" type="submit"
                    onclick="return confirm('Send this letter to every active subscriber?');">
                    Save and Broadcast
                </button>
            </form>
        </section>

        <section class="mb-5" aria-labelledby="subscribers-title">
            <h2 class="h4" id="subscribers-title">Subscribers</h2>
            <?php if ($subscribers === []) : ?>
                <p>No subscribers have opted in.</p>
            <?php else : ?>
                <div class="table-responsive"><table class="table align-middle">
                    <thead><tr><th>Subscriber</th><th>Consent date</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($subscribers as $subscriber) : ?>
                        <?php $id = (int) ($subscriber['id'] ?? 0); $active = ($subscriber['status'] ?? '') === 'active'; ?>
                        <tr>
                            <td><strong><?= $escape($subscriber['name'] ?? ''); ?></strong>
                                <div class="small text-body-secondary"><?= $escape($subscriber['email'] ?? ''); ?></div></td>
                            <td><?= $escape($subscriber['consent_at'] ?? ''); ?></td>
                            <td><?= $active ? 'Active' : 'Unsubscribed'; ?></td>
                            <td class="text-end text-nowrap">
                                <form method="post" action="/admin/letters" class="d-inline">
                                    <?= $this->csrf_field(); ?>
                                    <input type="hidden" name="action" value="<?= $active ? 'deactivate_subscriber' : 'activate_subscriber'; ?>">
                                    <input type="hidden" name="id" value="<?= $id; ?>">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit"><?= $active ? 'Deactivate' : 'Activate'; ?></button>
                                </form>
                                <form method="post" action="/admin/letters" class="d-inline"
                                    onsubmit="return confirm('Delete this subscriber?');">
                                    <?= $this->csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete_subscriber">
                                    <input type="hidden" name="id" value="<?= $id; ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            <?php endif; ?>
        </section>

        <section aria-labelledby="history-title">
            <h2 class="h4" id="history-title">Letter History</h2>
            <?php if ($letters === []) : ?>
                <p>No letters have been sent.</p>
            <?php else : ?>
                <div class="table-responsive"><table class="table align-middle">
                    <thead><tr><th>Date</th><th>Subject</th><th>Recipients</th><th>Preview</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($letters as $letter) : ?>
                        <tr>
                            <td><?= $escape($letter['created_at'] ?? ''); ?></td>
                            <td><?= $escape($letter['subject'] ?? ''); ?></td>
                            <td><?= (int) ($letter['recipient_count'] ?? 0); ?></td>
                            <td><?= $escape(substr(strip_tags((string) ($letter['body'] ?? '')), 0, 90)); ?>…</td>
                            <td class="text-end"><form method="post" action="/admin/letters"
                                onsubmit="return confirm('Delete this letter from history?');">
                                <?= $this->csrf_field(); ?>
                                <input type="hidden" name="action" value="delete_letter">
                                <input type="hidden" name="id" value="<?= (int) ($letter['id'] ?? 0); ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>

<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}
/* [End AI:GPT-5.6] */
