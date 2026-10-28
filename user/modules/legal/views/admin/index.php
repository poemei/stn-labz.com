<?php
declare(strict_types=1);

// path: /user/modules/legal/views/admin/legal.php

/*
 * [AI:GPT-5.6 Sol | 2026-09-09 UTC]
 */

if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}

$documents = $documents ?? [];
?>

<style>
    .container form input[type="text"],
    .container form textarea {
        width: 100%;
        box-sizing: border-box;
    }

    .container form textarea {
        min-height: 24rem;
        resize: vertical;
    }
</style>

<p>
    <small>
        <a href="/admin">Admin</a> &gt;&gt;
        <strong>Legal</strong>
    </small>
</p>

<div class="container my-3">
    <h1>Legal</h1>

    <p>
        Manage the legal documents published by the Legal module.
    </p>

    <?php foreach ($documents as $slug => $document): ?>
        <section>
            <h2>
                <?= htmlspecialchars(
                    $document['title'] !== ''
                        ? $document['title']
                        : ucfirst((string) $slug),
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </h2>

            <form action="/admin/legal" method="POST">
                <?= $this->csrf_field(); ?>

                <input
                    type="hidden"
                    name="action"
                    value="save_document"
                >

                <input
                    type="hidden"
                    name="document"
                    value="<?= htmlspecialchars(
                        (string) $slug,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                >

                <p>
                    <label for="<?= htmlspecialchars(
                        (string) $slug,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>-title">
                        Title
                    </label>
                </p>

                <p>
                    <input
                        id="<?= htmlspecialchars(
                            (string) $slug,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>-title"
                        type="text"
                        name="title"
                        value="<?= htmlspecialchars(
                            (string) $document['title'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                        required
                    >
                </p>

                <p>
                    <label for="<?= htmlspecialchars(
                        (string) $slug,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>-content">
                        Content
                    </label>
                </p>

                <p>
                    <textarea
                        id="<?= htmlspecialchars(
                            (string) $slug,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>-content"
                        name="content"
                        rows="24"
                        required
                    ><?= htmlspecialchars(
                        (string) $document['content'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?></textarea>
                </p>

                <p>
                    <button type="submit">
                        Save <?= htmlspecialchars(
                            $document['title'] !== ''
                                ? $document['title']
                                : ucfirst((string) $slug),
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </button>
                </p>
            </form>
        </section>
    <?php endforeach; ?>
</div>

<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}

/* [End AI:GPT-5.6 Sol] */