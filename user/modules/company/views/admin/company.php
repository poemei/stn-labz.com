<?php
declare(strict_types=1);

require APPROOT . '/views/inc/head.php';

$escape = static fn($value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$saved = (string)($_GET['saved'] ?? '');
?>

<div class="container company-admin">
    <p>
        <a href="/admin">Admin</a> &gt; Company
    </p>

    <h1>Company</h1>

    <p>
        Manage Careers, Acknowledgments, and the canonical security.txt file.
    </p>

    <?php if ($error !== ''): ?>
        <p role="alert">
            <?= $escape($error); ?>
        </p>
    <?php elseif ($saved !== ''): ?>
        <p role="status">
            Changes saved.
        </p>
    <?php endif; ?>

    <?php foreach ($pages as $slug): ?>
        <?php
        $document = $contents[$slug] ?? [
            'title' => '',
            'content' => '',
        ];
        ?>

        <section>
            <h2><?= $escape(ucfirst($slug)); ?></h2>

            <p>
                <a href="/company/page/<?= $escape($slug); ?>">
                    View published page
                </a>
            </p>

            <form action="/admin/company" method="post">
                <?= $this->csrf_field(); ?>

                <input
                    type="hidden"
                    name="action"
                    value="save_page"
                >

                <input
                    type="hidden"
                    name="page"
                    value="<?= $escape($slug); ?>"
                >

                <p>
                    <label for="<?= $escape($slug); ?>-title">
                        Title
                    </label>

                    <input
                        id="<?= $escape($slug); ?>-title"
                        name="title"
                        type="text"
                        value="<?= $escape($document['title'] ?? ''); ?>"
                        required
                    >
                </p>

                <p>
                    <label for="<?= $escape($slug); ?>-content">
                        Content (HTML)
                    </label>

                    <textarea
                        id="<?= $escape($slug); ?>-content"
                        name="content"
                        rows="16"
                    ><?= $escape($document['content'] ?? ''); ?></textarea>
                </p>

                <button type="submit">
                    Save <?= $escape(ucfirst($slug)); ?>
                </button>
            </form>
        </section>
    <?php endforeach; ?>

    <section>
        <h2>Security.txt</h2>

        <p>
            Edit the canonical public file:
            <code>/.well-known/security.txt</code>
        </p>

        <p>
            <a href="/.well-known/security.txt" target="_blank" rel="noopener">
                View published security.txt
            </a>
        </p>

        <form action="/admin/company" method="post">
            <?= $this->csrf_field(); ?>

            <input
                type="hidden"
                name="action"
                value="save_security"
            >

            <p>
                <label for="security-text">
                    security.txt
                </label>

                <textarea
                    id="security-text"
                    name="security_text"
                    rows="18"
                    spellcheck="false"
                    required
                ><?= $escape($security_text); ?></textarea>
            </p>

            <button type="submit">
                Save security.txt
            </button>
        </form>
    </section>
</div>

<style>
.company-admin section {
    margin-block: 2rem;
}

.company-admin label {
    display: block;
    margin-bottom: .4rem;
}

.company-admin input:not([type="hidden"]),
.company-admin textarea {
    width: 100%;
    box-sizing: border-box;
}

.company-admin textarea {
    resize: vertical;
}
</style>

<?php require APPROOT . '/views/inc/foot.php'; ?>