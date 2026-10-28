<?php

/** Newsletter Unsubscribe View. */

/* [AI:GPT-5.6 | 2026-09-01 02:15:00 UTC] */
if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}

$subscriber = is_array($data['subscriber'] ?? null) ? $data['subscriber'] : [];
$completed = (bool) ($data['completed'] ?? false);
$token = (string) ($data['token'] ?? '');
?>
<main class="container my-5" aria-labelledby="unsubscribe-title">
    <section class="mx-auto" style="max-width: 640px;">
        <h1 id="unsubscribe-title">Newsletter Subscription</h1>

        <?php if ($completed || ($subscriber['status'] ?? '') !== 'active') : ?>
            <div class="alert alert-light border" role="status">
                This email address is unsubscribed from Letters from <?= htmlspecialchars($SITE['name'] ?? 'Chaos MVC', ENT_QUOTES, 'UTF-8'); ?>.
            </div>
        <?php else : ?>
            <p>
                Stop newsletter delivery to
                <strong><?= htmlspecialchars(
                    (string) ($subscriber['email'] ?? ''),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ); ?></strong>?
            </p>
            <form method="post" action="/letters/unsubscribe/<?= htmlspecialchars(
                $token,
                ENT_QUOTES,
                'UTF-8'
            ); ?>">
                <?= $this->csrf_field(); ?>
                <button class="btn btn-outline-danger" type="submit">Unsubscribe</button>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}
/* [End AI:GPT-5.6] */
