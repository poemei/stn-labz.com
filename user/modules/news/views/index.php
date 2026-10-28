<?php
declare(strict_types=1);

// path: /user/modules/news/views/index.php

/**
 * ChAoS MVC News
 *
 * Public news listing.
 *
 * [AI:GPT-6 | 2026-10-22 UTC]
 */

if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}

$articles = is_array($data['articles'] ?? null)
    ? $data['articles']
    : [];
?>

<main class="container my-4">

    <nav aria-label="Breadcrumb">
        <small>
            <a href="/">Home</a>
            &gt;&gt;
            <strong>News</strong>
        </small>
    </nav>

    <header class="mb-4">
        <h1>ChAoS MVC News</h1>

        <p class="lead">
            Official announcements, framework releases,
            development updates, and security advisories
            from the ChAoS Foundation.
        </p>
    </header>

    <?php if (empty($articles)): ?>

        <section class="my-5">
            <p>
                No news has been published yet.
            </p>
        </section>

    <?php else: ?>

        <section aria-label="Latest News">

            <?php foreach ($articles as $article): ?>

                <?php
                $title = (string) ($article['title'] ?? '');
                $slug = (string) ($article['slug'] ?? '');
                $category = (string) ($article['category'] ?? '');
                $summary = (string) ($article['summary'] ?? '');
                $published = (string) (
                    $article['published_at'] ?? ''
                );

                $url = '/news/' . rawurlencode($slug);

                $timestamp = $published !== ''
                    ? strtotime($published)
                    : false;
                ?>

                <article class="card mb-4">

                    <div class="card-body">

                        <div class="d-flex flex-wrap gap-2 mb-2">

                            <span class="badge bg-secondary">
                                <?= htmlspecialchars(
                                    ucfirst($category),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </span>

                            <?php if ($timestamp !== false): ?>

                                <time
                                    class="text-muted"
                                    datetime="<?= htmlspecialchars(
                                        date('Y-m-d', $timestamp),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>"
                                >
                                    <?= htmlspecialchars(
                                        date('F j, Y', $timestamp),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </time>

                            <?php endif; ?>

                        </div>

                        <h2 class="card-title h4">

                            <a
                                href="<?= htmlspecialchars(
                                    $url,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                                class="text-decoration-none"
                            >
                                <?= htmlspecialchars(
                                    $title,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </a>

                        </h2>

                        <?php if ($summary !== ''): ?>

                            <p class="card-text">
                                <?= htmlspecialchars(
                                    $summary,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </p>

                        <?php endif; ?>

                        <a
                            href="<?= htmlspecialchars(
                                $url,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            class="btn btn-outline-primary btn-sm"
                            aria-label="Read <?= htmlspecialchars(
                                $title,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                        >
                            Read Article
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</main>

<?php

if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}

/* [End AI:GPT-6 | 2026-10-22] */
