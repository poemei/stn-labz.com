<?php
// path: /user/modules/announcements/views/public/announcements/index.php

require APPROOT . '/views/inc/head.php';

$items = is_array($data['items'] ?? null)
    ? $data['items']
    : [];
?>

<section class="container my-4" aria-labelledby="announcements-title">
    <h1 id="announcements-title">Announcements</h1>

    <?php if ($items === []) : ?>
        <p>No announcements are available.</p>
    <?php else : ?>
        <?php foreach ($items as $item) : ?>
            <article class="mb-4">
                <h2 class="h4">
                    <?= htmlspecialchars(
                        (string) ($item['title'] ?? ''),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </h2>

                <?php if (!empty($item['created_at'])) : ?>
                    <p class="text-muted small">
                        <?= htmlspecialchars(
                            (string) $item['created_at'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </p>
                <?php endif; ?>

                <div>
                    <?= nl2br(
                        htmlspecialchars(
                            (string) ($item['body'] ?? ''),
                            ENT_QUOTES,
                            'UTF-8'
                        )
                    ); ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php require APPROOT . '/views/inc/foot.php'; ?>
