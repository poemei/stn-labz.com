<?php
declare(strict_types=1);

// path: /user/modules/news/views/admin/index.php

/**
 * ChAoS MVC News Administration
 *
 * Module lifecycle and Markdown article management.
 *
 * [AI:GPT-6 | 2026-10-22 UTC]
 */

require APPROOT . '/views/inc/head.php';

$module = is_array($data['module'] ?? null)
    ? $data['module']
    : [];

$state = (string) ($data['database_state'] ?? 'invalid');

$articles = is_array($data['articles'] ?? null)
    ? $data['articles']
    : [];

$message = $data['message'] ?? null;
$error = $data['error'] ?? null;

$categories = [
    'release' => 'Release',
    'security' => 'Security',
    'development' => 'Development',
    'announcement' => 'Announcement',
];

$escape = static fn ($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>

<div class="container my-4">

    <nav aria-label="Breadcrumb" class="mb-3">
        <small>
            <a href="/admin">Admin</a>
            &gt;&gt;
            <strong>News</strong>
        </small>
    </nav>

    <header class="mb-4">
        <h1>News Administration</h1>
        <p class="text-muted">
            Manage official ChAoS MVC announcements,
            development news, releases, and security advisories.
        </p>
    </header>

    <?php if ($message): ?>
        <div class="alert alert-success" role="status">
            <?= $escape($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= $escape($error); ?>
        </div>
    <?php endif; ?>

    <!-- Module database lifecycle -->

    <section class="card mb-4">
        <div class="card-body">

            <h2 class="h5">Module Status</h2>

            <dl class="row mb-3">
                <dt class="col-sm-3">Module</dt>
                <dd class="col-sm-9">
                    <?= $escape($module['name'] ?? 'News'); ?>
                </dd>

                <dt class="col-sm-3">Version</dt>
                <dd class="col-sm-9">
                    <?= $escape($module['version'] ?? ''); ?>
                </dd>

                <dt class="col-sm-3">Schema Version</dt>
                <dd class="col-sm-9">
                    <?= $escape($module['schema_version'] ?? ''); ?>
                </dd>

                <dt class="col-sm-3">Database State</dt>
                <dd class="col-sm-9">
                    <strong><?= $escape($state); ?></strong>
                </dd>
            </dl>

            <?php if ($state === 'missing'): ?>

                <form method="post" action="/admin/news">
                    <?= $this->csrf_field(); ?>
                    <input type="hidden" name="action" value="install_sql">

                    <button type="submit" class="btn btn-primary">
                        Install News Database
                    </button>
                </form>

            <?php elseif ($state === 'update'): ?>

                <form method="post" action="/admin/news">
                    <?= $this->csrf_field(); ?>
                    <input type="hidden" name="action" value="update_sql">

                    <button type="submit" class="btn btn-primary">
                        Update News Database
                    </button>
                </form>

            <?php elseif ($state === 'invalid'): ?>

                <div class="alert alert-warning mb-0">
                    The News database is in an invalid state.
                    Article modifications are disabled until
                    the database state is corrected.
                </div>

            <?php endif; ?>

        </div>
    </section>

    <?php if ($state === 'current'): ?>

        <!-- Create article -->

        <section class="card mb-4">
            <div class="card-body">

                <h2 class="h4 mb-3">Create News Article</h2>

                <form method="post" action="/admin/news">

                    <?= $this->csrf_field(); ?>
                    <input type="hidden" name="action" value="create">

                    <div class="mb-3">
                        <label for="news-title" class="form-label">
                            Title
                        </label>

                        <input
                            type="text"
                            id="news-title"
                            name="title"
                            class="form-control"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="news-slug" class="form-label">
                            URL Slug
                        </label>

                        <input
                            type="text"
                            id="news-slug"
                            name="slug"
                            class="form-control"
                            maxlength="180"
                            pattern="[a-z0-9]+(-[a-z0-9]+)*"
                            placeholder="Leave blank to generate from title"
                        >

                        <div class="form-text">
                            Lowercase letters, numbers, and hyphens.
                            Leave blank for automatic generation.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="news-category" class="form-label">
                            Category
                        </label>

                        <select
                            id="news-category"
                            name="category"
                            class="form-select"
                        >
                            <?php foreach ($categories as $value => $label): ?>
                                <option
                                    value="<?= $escape($value); ?>"
                                    <?= $value === 'announcement' ? 'selected' : ''; ?>
                                >
                                    <?= $escape($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="news-summary" class="form-label">
                            Summary / SEO Description
                        </label>

                        <textarea
                            id="news-summary"
                            name="summary"
                            class="form-control"
                            rows="3"
                            maxlength="300"
                        ></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="news-markdown" class="form-label">
                            Article Content (Markdown)
                        </label>

                        <textarea
                            id="news-markdown"
                            name="body_markdown"
                            class="form-control font-monospace"
                            rows="16"
                            spellcheck="true"
                            placeholder="# Article heading&#10;&#10;Write your announcement here."
                        ></textarea>

                        <div class="form-text">
                            Markdown source is saved directly.
                            Public pages use the ChAoS MVC Markdown renderer.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="news-status" class="form-label">
                            Publication Status
                        </label>

                        <select
                            id="news-status"
                            name="status"
                            class="form-select"
                        >
                            <option value="draft" selected>
                                Draft
                            </option>
                            <option value="published">
                                Published
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Save Article
                    </button>

                </form>

            </div>
        </section>

        <!-- Existing articles -->

        <section class="mb-5">

            <h2 class="h4 mb-3">Manage Articles</h2>

            <?php if (empty($articles)): ?>

                <p class="text-muted">
                    No News articles have been created.
                </p>

            <?php else: ?>

                <?php foreach ($articles as $article): ?>

                    <?php
                    $id = (int) ($article['id'] ?? 0);
                    $status = (string) ($article['status'] ?? 'draft');
                    $category = (string) (
                        $article['category'] ?? 'announcement'
                    );
                    ?>

                    <article class="card mb-3">

                        <div class="card-body">

                            <div class="d-flex justify-content-between
                                        align-items-center flex-wrap gap-2 mb-3">

                                <h3 class="h5 mb-0">
                                    <?= $escape($article['title'] ?? 'Untitled'); ?>
                                </h3>

                                <span class="badge <?= $status === 'published'
                                    ? 'bg-success'
                                    : 'bg-secondary'; ?>">
                                    <?= $escape(ucfirst($status)); ?>
                                </span>

                            </div>

                            <form method="post" action="/admin/news">

                                <?= $this->csrf_field(); ?>

                                <input
                                    type="hidden"
                                    name="action"
                                    value="update"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $id; ?>"
                                >

                                <div class="mb-3">
                                    <label
                                        for="article-title-<?= $id; ?>"
                                        class="form-label"
                                    >
                                        Title
                                    </label>

                                    <input
                                        type="text"
                                        id="article-title-<?= $id; ?>"
                                        name="title"
                                        class="form-control"
                                        maxlength="150"
                                        value="<?= $escape($article['title'] ?? ''); ?>"
                                        required
                                    >
                                </div>

                                <div class="mb-3">
                                    <label
                                        for="article-slug-<?= $id; ?>"
                                        class="form-label"
                                    >
                                        Slug
                                    </label>

                                    <input
                                        type="text"
                                        id="article-slug-<?= $id; ?>"
                                        name="slug"
                                        class="form-control"
                                        maxlength="180"
                                        pattern="[a-z0-9]+(-[a-z0-9]+)*"
                                        value="<?= $escape($article['slug'] ?? ''); ?>"
                                        required
                                    >
                                </div>

                                <div class="mb-3">
                                    <label
                                        for="article-category-<?= $id; ?>"
                                        class="form-label"
                                    >
                                        Category
                                    </label>

                                    <select
                                        id="article-category-<?= $id; ?>"
                                        name="category"
                                        class="form-select"
                                    >
                                        <?php foreach ($categories as $value => $label): ?>
                                            <option
                                                value="<?= $escape($value); ?>"
                                                <?= $category === $value ? 'selected' : ''; ?>
                                            >
                                                <?= $escape($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label
                                        for="article-summary-<?= $id; ?>"
                                        class="form-label"
                                    >
                                        Summary / SEO Description
                                    </label>

                                    <textarea
                                        id="article-summary-<?= $id; ?>"
                                        name="summary"
                                        class="form-control"
                                        rows="3"
                                        maxlength="300"
                                    ><?= $escape($article['summary'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label
                                        for="article-markdown-<?= $id; ?>"
                                        class="form-label"
                                    >
                                        Markdown Content
                                    </label>

                                    <textarea
                                        id="article-markdown-<?= $id; ?>"
                                        name="body_markdown"
                                        class="form-control font-monospace"
                                        rows="14"
                                        spellcheck="true"
                                    ><?= $escape($article['body_markdown'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label
                                        for="article-status-<?= $id; ?>"
                                        class="form-label"
                                    >
                                        Status
                                    </label>

                                    <select
                                        id="article-status-<?= $id; ?>"
                                        name="status"
                                        class="form-select"
                                    >
                                        <option
                                            value="draft"
                                            <?= $status === 'draft' ? 'selected' : ''; ?>
                                        >
                                            Draft
                                        </option>

                                        <option
                                            value="published"
                                            <?= $status === 'published' ? 'selected' : ''; ?>
                                        >
                                            Published
                                        </option>
                                    </select>
                                </div>

                                <div class="d-flex flex-wrap gap-2">

                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        Update Article
                                    </button>

                                    <?php if ($status === 'published'): ?>
                                        <a
                                            href="/news/<?= rawurlencode(
                                                (string) ($article['slug'] ?? '')
                                            ); ?>"
                                            class="btn btn-outline-secondary"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            View Published Article
                                        </a>
                                    <?php endif; ?>

                                </div>

                            </form>

                            <hr>

                            <form
                                method="post"
                                action="/admin/news"
                                onsubmit="return confirm('Permanently delete this News article?');"
                            >
                                <?= $this->csrf_field(); ?>

                                <input
                                    type="hidden"
                                    name="action"
                                    value="delete"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $id; ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-outline-danger btn-sm"
                                >
                                    Delete Article
                                </button>
                            </form>

                        </div>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>

        </section>

    <?php endif; ?>

</div>

<?php

require APPROOT . '/views/inc/foot.php';

/* [End AI:GPT-6 | 2026-10-22] */
