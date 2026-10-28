<?php
declare(strict_types=1);

require APPROOT . '/views/inc/head.php';
?>

<article class="container">
    <h1>
        <?= htmlspecialchars(
            (string)$title,
            ENT_QUOTES,
            'UTF-8'
        ); ?>
    </h1>

    <?= (string)$content; ?>
</article>

<?php require APPROOT . '/views/inc/foot.php'; ?>