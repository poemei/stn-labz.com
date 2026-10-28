<?php
/* [AI:GPT-5.6 Sol | 2026-10-06 UTC] */

if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}

$activeTab = trim((string) ($_GET['tab'] ?? 'intelligence'));

$allowedTabs = [
    'intelligence',
    'assignments',
    'database',
];

if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'intelligence';
}

$action = trim((string) ($_GET['action'] ?? ''));

$records = is_array($data['records'] ?? null)
    ? $data['records']
    : [];

$assignments = is_array($data['assignments'] ?? null)
    ? $data['assignments']
    : [];

$databaseState = (string) ($data['database_state'] ?? 'missing');

/*
 * Database lifecycle takes precedence over normal Admin navigation.
 * When the required Intelligence schema is missing, Admin enters the
 * Database tab so the explicit Install SQL operation is available.
 */
if ($databaseState === 'missing') {
    $activeTab = 'database';
    $action = '';
}

$showIntelligenceForm = (
    $activeTab === 'intelligence'
    && $action === 'add'
);

$showAssignmentForm = (
    $activeTab === 'assignments'
    && $action === 'add'
);
?>

<main class="container py-4">

    <h1><?= htmlspecialchars(
        (string) ($data['title'] ?? 'Intelligence Administration'),
        ENT_QUOTES,
        'UTF-8'
    ); ?></h1>

    <p>
        <?= htmlspecialchars(
            (string) (
                $data['sub_title']
                ?? 'STN-LABZ Persistent Intelligence Repository'
            ),
            ENT_QUOTES,
            'UTF-8'
        ); ?>
    </p>

    <div class="alert alert-danger" role="alert">
        <h2 class="h5">
            CRITICAL — PERMANENT INTELLIGENCE REPOSITORY
        </h2>

        <p class="mb-2">
            <strong>
                DATA CLASS: PERMANENT / NON-DISPOSABLE
            </strong>
        </p>

        <p class="mb-2">
            This module contains persistent STN-LABZ intelligence,
            evidence history, observations, and operational assignments.
        </p>

        <p class="mb-0">
            <strong>
                This module and its data must never be uninstalled,
                nuked, deleted, reset, or otherwise destroyed.
            </strong>
            Rictus may be rebuilt or replaced. The intelligence
            repository must remain.
        </p>
    </div>

    <ul class="nav nav-tabs mb-4">

        <li class="nav-item">
            <a
                class="nav-link <?= $activeTab === 'intelligence'
                    ? 'active'
                    : ''; ?>"
                href="/admin/intelligence?tab=intelligence"
            >
                Intelligence
            </a>
        </li>

        <li class="nav-item">
            <a
                class="nav-link <?= $activeTab === 'assignments'
                    ? 'active'
                    : ''; ?>"
                href="/admin/intelligence?tab=assignments"
            >
                Assignments
            </a>
        </li>

        <li class="nav-item">
            <a
                class="nav-link <?= $activeTab === 'database'
                    ? 'active'
                    : ''; ?>"
                href="/admin/intelligence?tab=database"
            >
                Database
            </a>
        </li>

    </ul>

    <?php if ($activeTab === 'intelligence') : ?>

        <section>

            <?php if (!empty($data['created'])) : ?>

                <div class="alert alert-success" role="status">
                    Intelligence record
                    <strong>
                        <?= htmlspecialchars(
                            (string) $data['created'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </strong>
                    created.
                </div>

            <?php endif; ?>

            <?php if ($showIntelligenceForm) : ?>

                <div class="card mb-4">
                    <div class="card-body">

                        <div
                            class="d-flex justify-content-between
                            align-items-center mb-3"
                        >
                            <h2 class="h4 mb-0">
                                Add Intelligence
                            </h2>

                            <a
                                href="/admin/intelligence?tab=intelligence"
                                class="btn btn-outline-secondary"
                            >
                                Cancel
                            </a>
                        </div>

                        <p>
                            Add intelligence directly to the permanent
                            STN-LABZ intelligence repository.
                        </p>

                        <form method="post" action="/admin/intelligence">

                            <?= $this->csrf_field(); ?>

                            <input
                                type="hidden"
                                name="action"
                                value="create_intelligence"
                            >

                            <div class="mb-3">
                                <label
                                    for="intelligence-title"
                                    class="form-label"
                                >
                                    Title
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="intelligence-title"
                                    name="title"
                                    maxlength="255"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label
                                    for="intelligence-content"
                                    class="form-label"
                                >
                                    Intelligence
                                </label>

                                <textarea
                                    class="form-control"
                                    id="intelligence-content"
                                    name="content"
                                    rows="8"
                                    required
                                ></textarea>
                            </div>

                            <div class="mb-3">
                                <label
                                    for="intelligence-source"
                                    class="form-label"
                                >
                                    Source
                                </label>

                                <textarea
                                    class="form-control"
                                    id="intelligence-source"
                                    name="source"
                                    rows="3"
                                ></textarea>
                            </div>

                            <div class="mb-3">
                                <label
                                    for="intelligence-status"
                                    class="form-label"
                                >
                                    Status
                                </label>

                                <select
                                    class="form-select"
                                    id="intelligence-status"
                                    name="status"
                                >
                                    <option value="ACTIVE">
                                        ACTIVE
                                    </option>

                                    <option value="WATCHING">
                                        WATCHING
                                    </option>

                                    <option value="CLOSED">
                                        CLOSED
                                    </option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Add Intelligence
                                </button>

                                <a
                                    href="/admin/intelligence?tab=intelligence"
                                    class="btn btn-outline-secondary"
                                >
                                    Cancel
                                </a>

                            </div>

                        </form>

                    </div>
                </div>

            <?php else : ?>

                <div
                    class="d-flex justify-content-between
                    align-items-center mb-3"
                >
                    <h2 class="h4 mb-0">
                        Intelligence Records
                    </h2>

                    <div class="d-flex align-items-center gap-3">

                        <span>
                            <?= count($records); ?>
                            record<?= count($records) === 1 ? '' : 's'; ?>
                        </span>

                        <a
                            href="/admin/intelligence
                            ?tab=intelligence&action=add"
                            class="btn btn-primary"
                        >
                            Add Intelligence
                        </a>

                    </div>
                </div>

                <?php if ($records === []) : ?>

                    <p>
                        No intelligence records are currently stored.
                    </p>

                <?php else : ?>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        Intelligence ID
                                    </th>
                                    <th scope="col">
                                        Title
                                    </th>
                                    <th scope="col">
                                        Origin
                                    </th>
                                    <th scope="col">
                                        Status
                                    </th>
                                    <th scope="col">
                                        SRT
                                    </th>
                                    <th scope="col">
                                        Created
                                    </th>
                                    <th scope="col">
                                        Updated
                                    </th>
                                </tr>
                            </thead>

                            <tbody>

                            <?php foreach ($records as $record) : ?>

                                <tr>
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                (string) (
                                                    $record[
                                                        'intelligence_id'
                                                    ] ?? ''
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $record['title'] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $record['origin'] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $record['status'] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php if (
                                            trim(
                                                (string) (
                                                    $record['srt_id'] ?? ''
                                                )
                                            ) !== ''
                                        ) : ?>

                                            <?= htmlspecialchars(
                                                (string) $record['srt_id'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>

                                        <?php else : ?>

                                            &mdash;

                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $record['created_at'] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $record['updated_at'] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

            <?php endif; ?>

        </section>

    <?php elseif ($activeTab === 'assignments') : ?>

        <section>

            <?php if (!empty($data['assigned'])) : ?>

                <div class="alert alert-success" role="status">
                    Assignment
                    <strong>
                        <?= htmlspecialchars(
                            (string) $data['assigned'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </strong>
                    created for Rictus.
                </div>

            <?php endif; ?>

            <?php if ($showAssignmentForm) : ?>

                <div class="card mb-4">
                    <div class="card-body">

                        <div
                            class="d-flex justify-content-between
                            align-items-center mb-3"
                        >
                            <h2 class="h4 mb-0">
                                Assign Intelligence to Rictus
                            </h2>

                            <a
                                href="/admin/intelligence?tab=assignments"
                                class="btn btn-outline-secondary"
                            >
                                Cancel
                            </a>
                        </div>

                        <?php if ($records === []) : ?>

                            <p>
                                Create an intelligence record before
                                assigning intelligence to Rictus.
                            </p>

                            <a
                                href="/admin/intelligence
                                ?tab=intelligence&action=add"
                                class="btn btn-primary"
                            >
                                Add Intelligence
                            </a>

                        <?php else : ?>

                            <form
                                method="post"
                                action="/admin/intelligence"
                            >

                                <?= $this->csrf_field(); ?>

                                <input
                                    type="hidden"
                                    name="action"
                                    value="create_assignment"
                                >

                                <div class="mb-3">
                                    <label
                                        for="assignment-intelligence"
                                        class="form-label"
                                    >
                                        Intelligence
                                    </label>

                                    <select
                                        class="form-select"
                                        id="assignment-intelligence"
                                        name="intelligence_id"
                                        required
                                    >
                                        <option value="">
                                            Select Intelligence
                                        </option>

                                        <?php foreach (
                                            $records as $record
                                        ) : ?>

                                            <option
                                                value="<?= htmlspecialchars(
                                                    (string) (
                                                        $record[
                                                            'intelligence_id'
                                                        ] ?? ''
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>"
                                            >
                                                <?= htmlspecialchars(
                                                    (string) (
                                                        (
                                                            $record[
                                                                'intelligence_id'
                                                            ] ?? ''
                                                        )
                                                        . ' — '
                                                        . (
                                                            $record[
                                                                'title'
                                                            ] ?? ''
                                                        )
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label
                                        for="assignment-type"
                                        class="form-label"
                                    >
                                        Assignment Type
                                    </label>

                                    <select
                                        class="form-select"
                                        id="assignment-type"
                                        name="assignment_type"
                                        required
                                    >
                                        <option value="WATCH">
                                            WATCH
                                        </option>

                                        <option value="INVESTIGATE">
                                            INVESTIGATE
                                        </option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label
                                        for="assignment-instructions"
                                        class="form-label"
                                    >
                                        Instructions
                                    </label>

                                    <textarea
                                        class="form-control"
                                        id="assignment-instructions"
                                        name="instructions"
                                        rows="5"
                                    ></textarea>
                                </div>

                                <div class="d-flex gap-2">

                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        Assign to Rictus
                                    </button>

                                    <a
                                        href="/admin/intelligence
                                        ?tab=assignments"
                                        class="btn btn-outline-secondary"
                                    >
                                        Cancel
                                    </a>

                                </div>

                            </form>

                        <?php endif; ?>

                    </div>
                </div>

            <?php else : ?>

                <div
                    class="d-flex justify-content-between
                    align-items-center mb-3"
                >
                    <h2 class="h4 mb-0">
                        Rictus Assignments
                    </h2>

                    <div class="d-flex align-items-center gap-3">

                        <span>
                            <?= count($assignments); ?>
                            assignment<?= count($assignments) === 1
                                ? ''
                                : 's'; ?>
                        </span>

                        <a
                            href="/admin/intelligence
                            ?tab=assignments&action=add"
                            class="btn btn-primary"
                        >
                            Assign Intelligence
                        </a>

                    </div>
                </div>

                <?php if ($assignments === []) : ?>

                    <p>
                        No intelligence assignments currently exist.
                    </p>

                <?php else : ?>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        Assignment ID
                                    </th>
                                    <th scope="col">
                                        Intelligence ID
                                    </th>
                                    <th scope="col">
                                        Type
                                    </th>
                                    <th scope="col">
                                        Status
                                    </th>
                                    <th scope="col">
                                        Created
                                    </th>
                                    <th scope="col">
                                        Acknowledged
                                    </th>
                                    <th scope="col">
                                        Completed
                                    </th>
                                </tr>
                            </thead>

                            <tbody>

                            <?php foreach (
                                $assignments as $assignment
                            ) : ?>

                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $assignment[
                                                    'assignment_id'
                                                ] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $assignment[
                                                    'intelligence_id'
                                                ] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $assignment[
                                                    'assignment_type'
                                                ] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $assignment['status'] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $assignment[
                                                    'created_at'
                                                ] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $assignment[
                                                    'acknowledged_at'
                                                ] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) (
                                                $assignment[
                                                    'completed_at'
                                                ] ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

            <?php endif; ?>

        </section>

    <?php elseif ($activeTab === 'database') : ?>

        <section>

            <h2 class="h4">Database</h2>

            <p>
                Persistent repository database status and ownership.
            </p>

            <?php if (!empty($data['installed'])) : ?>

                <div class="alert alert-success" role="status">
                    Intelligence database installed successfully.
                </div>

            <?php endif; ?>

            <?php if ($databaseState === 'missing') : ?>

                <div class="alert alert-warning" role="alert">
                    <strong>
                        Database installation required.
                    </strong>
                    The permanent Intelligence repository has not
                    yet been initialized.
                </div>

                <form
                    method="post"
                    action="/admin/intelligence"
                    class="mb-4"
                >
                    <?= $this->csrf_field(); ?>

                    <input
                        type="hidden"
                        name="action"
                        value="install_sql"
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Install Database
                    </button>
                </form>

            <?php endif; ?>

            <table class="table">
                <tbody>

                    <tr>
                        <th scope="row">
                            Repository State
                        </th>
                        <td>
                            <strong>
                                <?= htmlspecialchars(
                                    strtoupper($databaseState),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </strong>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Intelligence Records
                        </th>
                        <td>
                            <?= count($records); ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Assignments
                        </th>
                        <td>
                            <?= count($assignments); ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Data Classification
                        </th>
                        <td>
                            <strong>
                                PERMANENT / NON-DISPOSABLE
                            </strong>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Database Tables
                        </th>
                        <td>
                            <code>intelligence_records</code><br>
                            <code>intelligence_observations</code><br>
                            <code>intelligence_assignments</code>
                        </td>
                    </tr>

                </tbody>
            </table>

            <div class="alert alert-warning" role="alert">
                <strong>
                    No destructive database operations are provided.
                </strong>
                Intelligence database removal, reset, truncation,
                and lifecycle deletion are intentionally unavailable.
            </div>

        </section>

    <?php endif; ?>

</main>

<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}

/* [End AI:GPT-5.6 Sol] */