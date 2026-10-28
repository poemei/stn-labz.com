<?php

declare(strict_types=1);

/* [AI:GPT-5.6 | 2026-09-19 17:15:00 UTC] */
if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}

$editItem = is_array($data['edit_item'] ?? null)
    ? $data['edit_item']
    : null;
$items = is_array($data['items'] ?? null)
    ? $data['items']
    : [];
$isEditing = $editItem !== null;
$databaseState = (string) ($data['database_state'] ?? 'missing');
$hasActiveData = !empty($data['has_active_data']);
$hasRecoverableData = !empty($data['has_recoverable_data']);

$escape = static fn ($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
?>

<p>
    <small>
        <a href="/admin">Admin</a>
        &gt;&gt;
        <strong>Announcements</strong>
    </small>
</p>

<main class="container my-4" aria-labelledby="announcements-admin-title">
    <?php if (!empty($data['installed'])) : ?>
        <p role="status">Announcements database installed.</p>
    <?php endif; ?>

    <?php if (!empty($data['updated'])) : ?>
        <p role="status">Announcements database updated.</p>
    <?php endif; ?>

    <?php if (!empty($data['deleted_data'])) : ?>
        <p role="status">Announcement data permanently deleted.</p>
    <?php endif; ?>

    <?php if (!empty($data['reset_data'])) : ?>
        <p role="status">
            Announcement data reset and preserved for recovery.
        </p>
    <?php endif; ?>

    <?php if (!empty($data['recovered_data'])) : ?>
        <p role="status">Announcement data recovered.</p>
    <?php endif; ?>

    <?php if ($databaseState === 'missing') : ?>
        <section class="card card-body mb-4">
            <h1 class="h4" id="announcements-admin-title">
                Database installation required
            </h1>

            <p>
                Install the Announcements database before managing
                announcements.
            </p>

            <form action="/admin/announcements" method="post">
                <?= $this->csrf_field(); ?>
                <input
                    type="hidden"
                    name="action"
                    value="install_sql"
                >
                <button class="btn btn-primary" type="submit">
                    Install Announcements database
                </button>
            </form>
        </section>
    <?php elseif ($databaseState === 'update') : ?>
        <section class="card card-body mb-4">
            <h1 class="h4" id="announcements-admin-title">
                Database update required
            </h1>

            <p>
                Update the Announcements database before managing
                announcements.
            </p>

            <form action="/admin/announcements" method="post">
                <?= $this->csrf_field(); ?>
                <input
                    type="hidden"
                    name="action"
                    value="update_sql"
                >
                <button class="btn btn-primary" type="submit">
                    Update Announcements database
                </button>
            </form>
        </section>
    <?php elseif ($databaseState === 'invalid') : ?>
        <section class="card card-body mb-4">
            <h1 class="h4" id="announcements-admin-title">
                Database lifecycle error
            </h1>

            <p>
                The installed Announcements schema does not match a
                supported lifecycle state. No data operation has been
                performed.
            </p>
        </section>
    <?php else : ?>
        <header
            class="d-flex flex-wrap justify-content-between
                align-items-center gap-3 mb-4"
        >
            <div>
                <h1 id="announcements-admin-title">Announcements</h1>
                <p class="text-body-secondary mb-0">
                    Create, edit, publish, and manage site announcements.
                </p>
            </div>

            <?php if ($isEditing) : ?>
                <a
                    class="btn btn-outline-secondary"
                    href="/admin/announcements"
                >
                    Cancel Edit
                </a>
            <?php endif; ?>
        </header>

        <section
            class="card card-body mb-4"
            aria-labelledby="announcement-form-title"
        >
            <h2 class="h4" id="announcement-form-title">
                <?= $isEditing
                    ? 'Edit Announcement'
                    : 'Create Announcement'; ?>
            </h2>

            <form action="/admin/announcements" method="post">
                <?= $this->csrf_field(); ?>

                <input
                    type="hidden"
                    name="action"
                    value="<?= $isEditing ? 'update' : 'create'; ?>"
                >

                <?php if ($isEditing) : ?>
                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) ($editItem['id'] ?? 0); ?>"
                    >
                <?php endif; ?>

                <div class="mb-3">
                    <label
                        class="form-label"
                        for="announcement-title"
                    >
                        Title
                    </label>
                    <input
                        class="form-control"
                        id="announcement-title"
                        name="title"
                        maxlength="255"
                        required
                        value="<?= $escape(
                            $editItem['title'] ?? ''
                        ); ?>"
                    >
                </div>

                <div class="mb-3">
                    <label
                        class="form-label"
                        for="announcement-body"
                    >
                        Announcement
                    </label>
                    <textarea
                        class="form-control"
                        id="announcement-body"
                        name="body"
                        rows="7"
                        required
                    ><?= $escape($editItem['body'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label
                        class="form-label"
                        for="announcement-content-type"
                    >
                        Content Format
                    </label>

                    <?php
                    $selectedType = (string) (
                        $editItem['content_type'] ?? 'markdown'
                    );
                    ?>

                    <select
                        class="form-select"
                        id="announcement-content-type"
                        name="content_type"
                        required
                    >
                        <option
                            value="markdown"
                            <?= $selectedType === 'markdown'
                                ? 'selected'
                                : ''; ?>
                        >
                            Markdown
                        </option>
                        <option
                            value="html"
                            <?= $selectedType === 'html'
                                ? 'selected'
                                : ''; ?>
                        >
                            HTML
                        </option>
                        <option
                            value="text"
                            <?= $selectedType === 'text'
                                ? 'selected'
                                : ''; ?>
                        >
                            Plain text
                        </option>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input
                        class="form-check-input"
                        id="announcement-published"
                        name="published"
                        type="checkbox"
                        value="1"
                        <?= (int) ($editItem['published'] ?? 1) === 1
                            ? 'checked'
                            : ''; ?>
                    >
                    <label
                        class="form-check-label"
                        for="announcement-published"
                    >
                        Published
                    </label>
                </div>

                <button class="btn btn-primary" type="submit">
                    <?= $isEditing
                        ? 'Save Changes'
                        : 'Create Announcement'; ?>
                </button>
            </form>
        </section>

        <section aria-labelledby="announcement-list-title">
            <h2 class="h4" id="announcement-list-title">
                Existing Announcements
            </h2>

            <?php if ($items === []) : ?>
                <p>No active announcements found.</p>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Title</th>
                                <th scope="col">Announcement</th>
                                <th scope="col">Format</th>
                                <th scope="col">Status</th>
                                <th scope="col">Created</th>
                                <th scope="col" class="text-end">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item) : ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?= $escape(
                                                $item['title'] ?? ''
                                            ); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?= nl2br(
                                            $escape($item['body'] ?? '')
                                        ); ?>
                                    </td>
                                    <td>
                                        <?= $escape(
                                            $item['content_type'] ?? ''
                                        ); ?>
                                    </td>
                                    <td>
                                        <?= (int) (
                                            $item['published'] ?? 0
                                        ) === 1
                                            ? 'Published'
                                            : 'Draft'; ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <?= $escape(
                                            $item['created_at'] ?? ''
                                        ); ?>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div
                                            class="d-flex justify-content-end
                                                gap-2"
                                        >
                                            <a
                                                class="btn btn-sm
                                                    btn-outline-primary"
                                                href="/admin/announcements/edit/<?= (int) (
                                                    $item['id'] ?? 0
                                                ); ?>"
                                            >
                                                Edit
                                            </a>

                                            <form
                                                action="/admin/announcements"
                                                method="post"
                                                class="d-inline"
                                                onsubmit="return confirm(
                                                    'Delete this announcement?'
                                                );"
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
                                                    value="<?= (int) (
                                                        $item['id'] ?? 0
                                                    ); ?>"
                                                >
                                                <button
                                                    class="btn btn-sm
                                                        btn-outline-danger"
                                                    type="submit"
                                                >
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section
            class="mt-4"
            aria-labelledby="announcements-data-title"
        >
            <h2 class="h4" id="announcements-data-title">
                Module Data
            </h2>

            <div class="d-flex flex-wrap gap-2">
                <?php if ($hasActiveData) : ?>
                    <form
                        action="/admin/announcements"
                        method="post"
                        onsubmit="return confirm(
                            'Reset active announcement data? '
                            + 'It will be preserved for recovery.'
                        );"
                    >
                        <?= $this->csrf_field(); ?>
                        <input
                            type="hidden"
                            name="action"
                            value="reset_data"
                        >
                        <button
                            class="btn btn-outline-warning"
                            type="submit"
                        >
                            Reset Announcement Data
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($hasRecoverableData) : ?>
                    <form
                        action="/admin/announcements"
                        method="post"
                    >
                        <?= $this->csrf_field(); ?>
                        <input
                            type="hidden"
                            name="action"
                            value="recover_data"
                        >
                        <button
                            class="btn btn-outline-success"
                            type="submit"
                        >
                            Recover Announcement Data
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($hasActiveData || $hasRecoverableData) : ?>
                    <form
                        action="/admin/announcements"
                        method="post"
                        onsubmit="return confirm(
                            'Permanently delete every announcement, '
                            + 'including recoverable data? '
                            + 'This cannot be undone.'
                        );"
                    >
                        <?= $this->csrf_field(); ?>
                        <input
                            type="hidden"
                            name="action"
                            value="delete_data"
                        >
                        <button
                            class="btn btn-outline-danger"
                            type="submit"
                        >
                            Delete Announcement Data
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (!$hasActiveData && !$hasRecoverableData) : ?>
                <p class="text-body-secondary mb-0">
                    No announcement data is currently stored.
                </p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section
        class="mt-4"
        aria-labelledby="announcements-module-title"
    >
        <h2 class="h4" id="announcements-module-title">Module</h2>

        <form
            method="post"
            action="/admin/uninstall"
            onsubmit="return confirm(
                'Nuke Announcements? Core will remove the module '
                + 'and its declared database table.'
            );"
        >
            <?= $this->csrf_field(); ?>
            <input
                type="hidden"
                name="module"
                value="announcements"
            >
            <button class="btn btn-danger" type="submit">
                Nuke Announcements
            </button>
        </form>
    </section>
</main>

<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}
/* [End AI:GPT-5.6] */
