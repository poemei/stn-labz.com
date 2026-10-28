<?php require APPROOT . '/views/inc/head.php'; ?>

<div class="container">
<h2>Contact Messages</h2>

<table class="table table-bordered">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Subject</th>
<th>Date</th>
</tr>
</thead>

<tbody>
<?php foreach ($data['messages'] as $m): ?>
<tr>
<td><?= $m['id'] ?></td>
<td><?= htmlspecialchars($m['name']) ?></td>
<td><?= htmlspecialchars($m['email']) ?></td>
<td><?= htmlspecialchars($m['subject']) ?></td>
<td><?= $m['created_at'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>

</table>
</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>
