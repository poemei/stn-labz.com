<?php
declare(strict_types=1);

// path: /user/modules/legal/views/terms.php

/*
 * [AI:GPT-5.6 Sol | 2026-09-01 21:49:00 UTC]
 */

require APPROOT . '/views/inc/head.php';

$document = $document ?? [
    'title' => 'Terms of Use',
    'content' => '',
];
?>

<article>
    <h1>
        <?= htmlspecialchars(
            (string) $document['title'],
            ENT_QUOTES,
            'UTF-8'
        ); ?>
    </h1>

    <?= (string) $document['content']; ?>
</article>

<?php
require APPROOT . '/views/inc/foot.php';

/* [End AI:GPT-5.6 Sol] */