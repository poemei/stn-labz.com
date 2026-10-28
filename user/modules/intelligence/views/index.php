<?php
/* [AI:GPT-5.6 Sol | 2026-10-06 UTC] */

if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}

$records = is_array($data['records'] ?? null)
    ? $data['records']
    : [];
?>

<main class="container py-5">

    <h1>
        <?= htmlspecialchars(
            (string) ($data['title'] ?? 'Intelligence'),
            ENT_QUOTES,
            'UTF-8'
        ); ?>
    </h1>

    <p class="lead">
        <?= htmlspecialchars(
            (string) (
                $data['sub_title']
                ?? 'STN-LABZ Persistent Intelligence'
            ),
            ENT_QUOTES,
            'UTF-8'
        ); ?>
    </p>

    <p>
        Persistent intelligence records maintained by STN-LABZ.
        Intelligence records are retained independently of the
        operational systems that collected them.
    </p>

    <?php if ($records === []) : ?>

        <div class="alert alert-secondary" role="status">
            No intelligence records are currently available.
        </div>

    <?php else : ?>
        <div class="row g-4">
            <?php foreach ($records as $record) :
                $id = trim((string) ($record['intelligence_id'] ?? ''));
                $full = trim((string) ($record['content'] ?? ''));
                $excerpt = mb_strimwidth(preg_replace('/\s+/u', ' ', $full) ?? '', 0, 260, '…');
            ?>
                <div class="col-md-6">
                    <article class="card h-100">
                        <div class="card-body">
                            <div class="text-muted small mb-2">
                                <?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>
                                · <?= htmlspecialchars((string) ($record['origin'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                · <?= htmlspecialchars((string) ($record['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <h2 class="h5"><?= htmlspecialchars((string) ($record['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                            <p><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ($id !== '') : ?>
                                <a class="btn btn-outline-primary" href="/intelligence/<?= rawurlencode($id) ?>">Read Full Intelligence →</a>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}

/* [End AI:GPT-5.6 Sol] */
