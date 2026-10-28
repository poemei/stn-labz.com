<?php
declare(strict_types=1);

// path: /user/modules/news/views/article.php

/**
 * ChAoS MVC News
 *
 * Public article view.
 * Renders stored Markdown through the Core Markdown engine.
 *
 * [AI:GPT-6 | 2026-10-22 UTC]
 */

$article = is_array($data['article'] ?? null)
    ? $data['article']
    : [];

$title = (string) ($article['title'] ?? '');
$slug = (string) ($article['slug'] ?? '');
$category = (string) ($article['category'] ?? '');
$summary = (string) ($article['summary'] ?? '');
$markdown = (string) ($article['body_markdown'] ?? '');

$published = (string) ($article['published_at'] ?? '');
$updated = (string) ($article['updated_at'] ?? '');

$publishedTimestamp = $published !== ''
    ? strtotime($published)
    : false;

$updatedTimestamp = $updated !== ''
    ? strtotime($updated)
    : false;

$articleUrl = URLROOT . '/news/' . rawurlencode($slug);

$description = $summary !== ''
    ? $summary
    : mb_substr(
        trim(preg_replace(
            '/\s+/',
            ' ',
            strip_tags($markdown)
        ) ?? ''),
        0,
        160
    );

/*
 * Supply page metadata to the established theme/head system.
 */
$og = [
    'title' => $title . ' | ChAoS MVC News',
    'desc' => $description,
    'url' => $articleUrl,
    'type' => 'article',
];

/*
 * Google-compatible structured article data.
 * JSON-LD is separate from the Markdown content.
 */
$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $title,
    'description' => $description,
    'url' => $articleUrl,
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $articleUrl,
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'ChAoS Foundation',
        'url' => 'https://www.chaos-mvc.org/',
    ],
];

if ($publishedTimestamp !== false) {
    $structuredData['datePublished'] = date(
        DATE_ATOM,
        $publishedTimestamp
    );
}

if ($updatedTimestamp !== false) {
    $structuredData['dateModified'] = date(
        DATE_ATOM,
        $updatedTimestamp
    );
}

/*
 * Existing ChAoS MVC theme rendering.
 */
if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}
?>

<main class="container my-4">

    <nav aria-label="Breadcrumb" class="mb-4">
        <small>
            <a href="/">Home</a>
            &gt;&gt;
            <a href="/news">News</a>
            &gt;&gt;
            <strong>
                <?= htmlspecialchars(
                    $title,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </strong>
        </small>
    </nav>

    <article class="news-article">

        <header class="mb-4">

            <div class="mb-2">

                <span class="badge bg-secondary">
                    <?= htmlspecialchars(
                        ucfirst($category),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </span>

            </div>

            <h1>
                <?= htmlspecialchars(
                    $title,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </h1>

            <?php if ($summary !== ''): ?>

                <p class="lead">
                    <?= htmlspecialchars(
                        $summary,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </p>

            <?php endif; ?>

            <div class="text-muted small">

                <?php if ($publishedTimestamp !== false): ?>

                    Published

                    <time datetime="<?= htmlspecialchars(
                        date(DATE_ATOM, $publishedTimestamp),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">
                        <?= htmlspecialchars(
                            date('F j, Y', $publishedTimestamp),
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </time>

                <?php endif; ?>

                <?php if (
                    $updatedTimestamp !== false
                    && $publishedTimestamp !== false
                    && $updatedTimestamp > $publishedTimestamp
                ): ?>

                    <span class="mx-2">&middot;</span>

                    Updated

                    <time datetime="<?= htmlspecialchars(
                        date(DATE_ATOM, $updatedTimestamp),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">
                        <?= htmlspecialchars(
                            date('F j, Y', $updatedTimestamp),
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </time>

                <?php endif; ?>

            </div>

        </header>

        <div class="news-content mb-5">

            <?php
            /*
             * Core Markdown renderer.
             *
             * Do not escape the returned HTML.
             * The renderer already escapes raw source HTML
             * and validates generated links.
             */
            echo $render_md->markdown($markdown);
            ?>

        </div>

        <footer class="border-top pt-3 mt-4">

            <a href="/news" class="text-decoration-none">
                &larr; Back to News
            </a>

        </footer>

    </article>

</main>

<?php
/*
 * Structured data is encoded as JSON, not hand-built HTML.
 * Escape characters that could break script context.
 */
$jsonLd = json_encode(
    $structuredData,
    JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
    | JSON_UNESCAPED_SLASHES
    | JSON_INVALID_UTF8_SUBSTITUTE
);

if ($jsonLd !== false):
?>
<script type="application/ld+json">
<?= $jsonLd; ?>
</script>
<?php endif; ?>

<?php

if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}

/* [End AI:GPT-6 | 2026-10-22] */
