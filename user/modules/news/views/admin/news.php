<?php require APPROOT . '/views/inc/head.php'; ?>

<p><small><a href="/admin">Admin</a> >> <strong>News</strong></small></p>

<div class="container mt-4">

<h2>News Management</h2>

<?php if(isset($_SESSION['msg'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type']; ?>">
        <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
    </div>
<?php endif; ?>

<style>
.news-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.news-card {
    border: 1px solid #ccc;
    padding: 1rem;
    width: 320px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.news-card h4 {
    margin: 0 0 0.5rem 0;
}

.news-meta {
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
}

.news-actions {
    margin-top: 0.5rem;
}

.news-actions a,
.news-actions button {
    margin-right: 0.5rem;
}
</style>

<!-- ===================== -->
<!-- NEWS LIST -->
<!-- ===================== -->

<div class="news-grid">

<?php if (!empty($data['items'])): ?>
<?php foreach ($data['items'] as $n): ?>

<div class="news-card">

    <div>
        <h4><?= htmlspecialchars($n['title']) ?></h4>

        <div class="news-meta">
            <?= htmlspecialchars($n['date']) ?>
            <?= $n['pinned'] ? ' | <strong>Pinned</strong>' : '' ?>
        </div>

        <p><?= htmlspecialchars($n['summary']) ?></p>
    </div>

    <div class="news-actions">
        <a href="<?= URLROOT ?>/admin/news/edit/<?= $n['id'] ?>">Edit</a>

        <form method="POST" action="<?= URLROOT ?>/admin/news/delete/<?= $n['id'] ?>" style="display:inline;">
            <button type="submit">Delete</button>
        </form>
    </div>

</div>

<?php endforeach; ?>
<?php else: ?>
<p>No news entries found.</p>
<?php endif; ?>

</div>

<hr>

<!-- ===================== -->
<!-- EDIT FORM -->
<!-- ===================== -->

<?php if (!empty($data['edit'])): $e = $data['edit']; ?>

<h3>Edit News</h3>

<form method="POST" action="<?= URLROOT ?>/admin/news/update">

<input type="hidden" name="id" value="<?= $e['id'] ?>">

<div class="mb-2">
<label>Title</label>
<input type="text" name="title" value="<?= htmlspecialchars($e['title']) ?>" required>
</div>

<div class="mb-2">
<label>Slug</label>
<input type="text" name="slug" value="<?= htmlspecialchars($e['slug']) ?>">
</div>

<div class="mb-2">
<label>Summary</label>
<input type="text" name="summary" value="<?= htmlspecialchars($e['summary']) ?>">
</div>

<div class="mb-2">
<label>Content</label>
<textarea name="content_body"><?= htmlspecialchars($e['content_body']) ?></textarea>
</div>

<div class="mb-2">
<label>Link</label>
<input type="text" name="link" value="<?= htmlspecialchars($e['link']) ?>">
</div>

<div class="mb-2">
<label>Tags</label>
<input type="text" name="tags" value="<?= htmlspecialchars($e['tags']) ?>">
</div>

<div class="mb-2">
<label>Date</label>
<input type="date" name="date" value="<?= $e['date'] ?>">
</div>

<div class="mb-3">
<label>
<input type="checkbox" name="pinned" <?= $e['pinned'] ? 'checked' : '' ?>> Pinned
</label>
</div>

<button type="submit">Update</button>

</form>

<hr>

<?php endif; ?>

<!-- ===================== -->
<!-- CREATE FORM -->
<!-- ===================== -->

<h3>Create News</h3>

<form method="POST" action="<?= URLROOT ?>/admin/news/create">

<div class="mb-2">
<label>Title</label>
<input type="text" name="title" required>
</div>

<div class="mb-2">
<label>Slug</label>
<input type="text" name="slug">
</div>

<div class="mb-2">
<label>Summary</label>
<input type="text" name="summary">
</div>

<div class="mb-2">
<label>Content</label>
<textarea name="content_body"></textarea>
</div>

<div class="mb-2">
<label>Link</label>
<input type="text" name="link">
</div>

<div class="mb-2">
<label>Tags</label>
<input type="text" name="tags" placeholder="tag1, tag2">
</div>

<div class="mb-2">
<label>Date</label>
<input type="date" name="date" required>
</div>

<div class="mb-3">
<label>
<input type="checkbox" name="pinned"> Pinned
</label>
</div>

<button type="submit">Create</button>

</form>

</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>
