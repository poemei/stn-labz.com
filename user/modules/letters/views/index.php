<?php

/** Public Newsletter Subscription View. */

/* [AI:GPT-5.6 | 2026-09-01 02:15:00 UTC] */
if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}

$errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];
$values = is_array($data['values'] ?? null) ? $data['values'] : [];
$escape = static fn ($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
?>
<main class="container my-5" aria-labelledby="letters-title">
    <section class="mx-auto" style="max-width: 720px;">
        <header class="text-center mb-4">
            <p class="text-uppercase small text-body-secondary mb-2">News from <?= htmlspecialchars($SITE['name'] ?? 'Chaos MVC', ENT_QUOTES, 'UTF-8'); ?></p>
            <h1 id="letters-title">Join the Newsletter</h1>
            <p class="lead text-body-secondary">
                Occasional project news, release notes, and messages from <?= htmlspecialchars($SITE['name'] ?? 'Chaos MVC', ENT_QUOTES, 'UTF-8'); ?>.
            </p>
        </header>

        <?php if (!empty($data['subscribed'])) : ?>
            <div class="alert alert-light border" role="status">
                You’re on the list. Thank you for subscribing.
            </div>
        <?php else : ?>
            <?php if ($errors !== []) : ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error) : ?>
                            <li><?= $escape($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="/letters" class="card card-body shadow-sm">
                <?= $this->csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label" for="letters-name">Name</label>
                    <input class="form-control" id="letters-name" name="name"
                        maxlength="120" autocomplete="name" required
                        value="<?= $escape($values['name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="letters-email">Email address</label>
                    <input class="form-control" id="letters-email" name="email"
                        type="email" maxlength="254" autocomplete="email" required
                        value="<?= $escape($values['email'] ?? ''); ?>">
                </div>

                <div class="visually-hidden" aria-hidden="true">
                    <label for="letters-website">Website</label>
                    <input id="letters-website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" id="letters-consent" name="consent"
                        type="checkbox" value="1" required>
                    <label class="form-check-label" for="letters-consent">
                        I want to receive the <?= htmlspecialchars($SITE['name'] ?? 'Chaos MVC', ENT_QUOTES, 'UTF-8'); ?> newsletter by email.
                    </label>
                </div>

                <p class="small text-body-secondary">
                    You can unsubscribe using the link included in every letter.
                </p>
                <button class="btn btn-primary" type="submit">Subscribe</button>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}
/* [End AI:GPT-5.6] */
