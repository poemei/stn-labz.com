<?php
if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}
$record = is_array($data['record'] ?? null) ? $data['record'] : [];
$escape = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<main class="container py-5">
    <p><a href="/intelligence">&larr; Back to Intelligence</a></p>
    <h1><?= $escape($record['title'] ?? 'Intelligence') ?></h1>
    <p class="text-muted">
        <?= $escape($record['intelligence_id'] ?? '') ?> ·
        <?= $escape($record['origin'] ?? '') ?> ·
        <?= $escape($record['status'] ?? '') ?>
    </p>
    <section class="my-4">
        <h2 class="h4">Intelligence Report</h2>
        <div><?= nl2br($escape($record['content'] ?? '')) ?></div>
    </section>
    <?php if (trim((string) ($record['source'] ?? '')) !== '') : ?>
        <section class="my-4"><h2 class="h4">Source</h2><p><?= nl2br($escape($record['source'])) ?></p></section>
    <?php endif; ?>
    <?php if (trim((string) ($record['srt_id'] ?? '')) !== '') : ?>
        <p>SRT: <?= $escape($record['srt_id']) ?></p>
    <?php endif; ?>
</main>
<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}
