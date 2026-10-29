<?php
/* [AI:GPT-6 | 2026-10-29 04:17:13 UTC] */
 require APPROOT . '/views/inc/head.php'; ?>
<?php
$adminModules = is_array($data['admin_modules'] ?? null)
    ? $data['admin_modules']
    : [];
$moduleAccess = is_array($data['module_access'] ?? null)
    ? $data['module_access']
    : [];
?>
<p><small><a href="/admin">Admin</a> >> <strong>Accounts</strong></small></p>
<div class="container mt-4">

<h2>Account Management</h2>
<div class="alert alert-info"><strong>Mission Control assignments:</strong> Positions and mission assignments are managed separately from Account classification. <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars(URLROOT, ENT_QUOTES, 'UTF-8'); ?>/accounts/organization">Open Mission Control Assignments</a></div>
<p>Non-administrator access now requires explicit missions and applicable credentials. Existing module allowances are retained for reference and do not grant access on their own.</p>
<?php if(isset($_SESSION['msg'])): ?>
    <?php $messageType = in_array($_SESSION['msg_type'] ?? '', ['success', 'danger', 'warning', 'info'], true) ? $_SESSION['msg_type'] : 'info'; ?>
    <div class="alert alert-<?= $messageType; ?> mt-3">
        <?= htmlspecialchars((string) $_SESSION['msg'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['msg']); ?>
    </div>
<?php endif; ?>

<table class="table table-bordered">
<thead>
<tr>
<th>ID</th>
<th>Username</th>
<th>Name</th>
<th>Email</th>
<th>Classification and Legacy Module Allowances</th>
<th>Mission Control</th>
<th>Password</th>
<th>Delete</th>
</tr>
</thead>

<tbody>

<?php foreach ($data['accounts'] as $a): ?>

<tr>

<td><?php echo $a['id']; ?></td>

<td><?php echo htmlspecialchars($a['username']); ?></td>


<td><?php echo htmlspecialchars($a['display_name']); ?></td>

<td><!--<?php echo htmlspecialchars($a['email_address']); ?>-->

<form method="POST" action="<?php echo URLROOT; ?>/accounts/email/<?php echo $a['id']; ?>">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
<input type="email" name="email_address" placeholder="<?= htmlspecialchars($a['email_address'])?>" required>
<button type="submit">change</button>
</form>

</td>

<td>
<form method="POST" action="<?= htmlspecialchars(URLROOT, ENT_QUOTES, 'UTF-8'); ?>/admin/accounts">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
<input type="hidden" name="action" value="access">
<input type="hidden" name="account_id" value="<?= (int) $a['id']; ?>">

<?php $isCurrentAccount = (int) $a['id'] === (int) ($_SESSION['user_id'] ?? 0); ?>
<?php if ($isCurrentAccount): ?>
    <input type="hidden" name="role" value="admin">
    <strong>Administrator</strong>
<?php else: ?>
    <label>
        Classification
        <select name="role" class="form-control">
            <?php foreach (['user' => 'User', 'developer' => 'Developer', 'ai' => 'AI', 'admin' => 'Administrator'] as $roleValue => $roleLabel): ?>
                <option value="<?= $roleValue; ?>" <?= (string) $a['role'] === $roleValue ? 'selected' : ''; ?>><?= $roleLabel; ?></option>
            <?php endforeach; ?>
        </select>
    </label>
<?php endif; ?>

<p><small>Authority level: <?= (int) $a['user_level']; ?></small></p>

<?php if (!$isCurrentAccount): ?>
<details>
    <summary>Legacy module allowances (NOT mission assignments)</summary>
    <?php if ($adminModules === []): ?>
        <p><small>No assignable Admin modules are installed.</small></p>
    <?php endif; ?>
    <?php foreach ($adminModules as $adminModule): ?>
        <label style="display:block;">
            <input
                type="checkbox"
                name="modules[]"
                value="<?= htmlspecialchars((string) $adminModule, ENT_QUOTES, 'UTF-8'); ?>"
                <?= in_array((string) $adminModule, $moduleAccess[(int) $a['id']] ?? [], true) ? 'checked' : ''; ?>
            >
            <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $adminModule)), ENT_QUOTES, 'UTF-8'); ?>
        </label>
    <?php endforeach; ?>
</details>
<button type="submit">Save account classification / legacy allowances</button>
<?php endif; ?>
</form>
</td>

<td>
<a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars(URLROOT, ENT_QUOTES, 'UTF-8'); ?>/accounts/organization?account_id=<?= (int) $a['id']; ?>">Manage position &amp; missions</a>
<p><small>Use Organization to assign a tier and missions. Legacy module allowances do not create missions.</small></p>
</td>

<td>

<form method="POST" action="<?php echo URLROOT; ?>/accounts/password/<?php echo $a['id']; ?>">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
<input type="password" name="password" placeholder="new password" minlength="12" required>
<button type="submit">change</button>
</form>

</td>

<td>

<?php if ($a['id'] != ($_SESSION['user_id'] ?? 0)): ?>
    <form method="POST" action="<?php echo URLROOT; ?>/accounts/delete/<?php echo (int) $a['id']; ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">delete</button>
    </form>
<?php else: ?>
    <strong>(You)</strong>
<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<hr>

<h3>Create Account</h3>

<form method="POST" action="<?php echo URLROOT; ?>/accounts/create">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

<div class="mb-2">
<label>Username</label>
<input type="text" name="username" class="form-control" required>
</div>

<div class="mb-2">
<label>Password</label>
<input type="password" name="password" class="form-control" minlength="12" required>
</div>

<div class="mb-2">
<label>Email</label>
<input type="email" name="email_address" class="form-control" required>
</div>

<div class="mb-2">
<label>Name</label>
<input type="text" name="display_name" class="form-control">
</div>

<div class="mb-3">
<label>Classification</label>
<select name="role" class="form-control">
<option value="user">User</option>
<option value="developer">Developer</option>
<option value="ai">AI</option>
<option value="admin">Administrator</option>
</select>
<small>Developer and AI accounts retain Tier 7 classification. Record their approved credentials and mission assignments in Organization after creating the account.</small>
</div>

<button type="submit" class="btn btn-primary">Create Account</button>

</form>

</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>

<!-- [End AI:GPT-6] -->
