<?php
/* [AI:GPT-6 | 2026-10-29 UTC] */
if (!theme::render('head', get_defined_vars())) { require APPROOT . '/views/inc/head.php'; }
$esc = static fn ($x): string => htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8');
?>
<main class="container py-4">
<h1>Certifications Registry</h1>
<p>Certificates are linked to employee and AI accounts. This registry does not independently grant Mission Control access.</p>
<?php if (!$data['ready']): ?>
<div class="alert alert-warning">Certifications schema not installed.</div>
<form method="post"><?= $this->csrf_field(); ?><input type="hidden" name="action" value="install_sql">
<button type="submit" class="btn btn-primary">Install SQL</button></form>
<?php else: ?>
<?php if (empty($data['systems_ready'])): ?>
<div class="alert alert-warning">System catalog update required before issuing certificates.</div>
<form method="post"><?= $this->csrf_field(); ?><input type="hidden" name="action" value="update_sql"><button class="btn btn-primary" type="submit">Update Module SQL</button></form>
<?php else: ?>
<section class="card mb-4"><div class="card-body"><h2 class="h5">Registered Systems / Products</h2>
<form method="post"><?= $this->csrf_field(); ?><input type="hidden" name="action" value="add_system">
<label for="system-name">Display name</label><input id="system-name" name="display_name" class="form-control mb-2" maxlength="150" required>
<label for="system-slug">System identifier</label><input id="system-slug" name="system_slug" class="form-control mb-2" maxlength="63" pattern="[a-z][a-z0-9_-]*" required>
<button class="btn btn-secondary" type="submit">Add System / Product</button></form>
<ul class="list-group mt-3"><?php foreach ($data['systems'] as $system): ?>
<li class="list-group-item d-flex justify-content-between align-items-center">
<span><?= $esc($system['display_name']); ?> <code><?= $esc($system['system_slug']); ?></code> <?= !empty($system['active']) ? '' : '(Inactive)'; ?></span>
<form method="post"><?= $this->csrf_field(); ?><input type="hidden" name="action" value="system_status"><input type="hidden" name="system_slug" value="<?= $esc($system['system_slug']); ?>">
<input type="hidden" name="active" value="<?= !empty($system['active']) ? '0' : '1'; ?>">
<button class="btn btn-outline-secondary btn-sm" type="submit"><?= !empty($system['active']) ? 'Disable' : 'Enable'; ?></button></form></li>
<?php endforeach; ?></ul></div></section>
<section class="card mb-4"><div class="card-body"><h2 class="h5">Issue Certificate</h2>
<form method="post"><?= $this->csrf_field(); ?><input type="hidden" name="action" value="issue">
<label for="cert-holder">Certificate holder</label>
<select id="cert-holder" class="form-select mb-3" name="account_id" required>
<?php foreach ($data['accounts'] as $account): ?>
<option value="<?= (int) $account['id']; ?>"><?= $esc($account['username']); ?> (<?= $esc($account['role']); ?>, Level <?= (int) $account['user_level']; ?>)</option>
<?php endforeach; ?></select>
<label for="cert-system">System / product identifier</label>
<select id="cert-system" name="system_slug" class="form-select mb-3" required>
<option value="">Select a registered system or product</option>
<?php foreach ($data['systems'] as $system): if (empty($system['active'])) { continue; } ?>
<option value="<?= $esc($system['system_slug']); ?>"><?= $esc($system['display_name']); ?></option>
<?php endforeach; ?></select>
<label for="cert-standard">Certification standard reference</label>
<input id="cert-standard" name="standard_reference" class="form-control mb-3" maxlength="255" required>
<label for="cert-evidence">Verified evidence reference</label>
<input id="cert-evidence" name="evidence_reference" class="form-control mb-3" maxlength="255" required>
<label for="cert-issued">Issued on</label>
<input id="cert-issued" type="date" name="issued_on" value="<?= gmdate('Y-m-d'); ?>" class="form-control mb-3" required>
<label for="cert-expires">Expires on (optional)</label>
<input id="cert-expires" type="date" name="expires_on" class="form-control mb-3">
<button type="submit" class="btn btn-primary">Issue Certificate</button></form></div></section>
<section><h2 class="h5">Certificate records</h2>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>ID</th><th>Holder</th><th>System</th><th>Issued</th><th>Expires</th><th>Status</th><th>Manage</th></tr></thead><tbody>
<?php foreach ($data['records'] as $record): ?>
<tr><td><code><?= $esc($record['certificate_id']); ?></code></td><td><?= $esc($record['holder_name']); ?></td><td><?= $esc($record['system_slug']); ?></td><td><?= $esc($record['issued_on']); ?></td><td><?= $esc($record['expires_on'] ?? '—'); ?></td><td><?= $esc($record['status']); ?></td>
<td><form method="post"><?= $this->csrf_field(); ?><input type="hidden" name="action" value="status"><input type="hidden" name="certificate_id" value="<?= $esc($record['certificate_id']); ?>">
<select class="form-select form-select-sm" name="status"><?php foreach (['active','suspended','revoked'] as $status): ?><option value="<?= $status; ?>" <?= $record['status'] === $status ? 'selected' : ''; ?>><?= ucfirst($status); ?></option><?php endforeach; ?></select><button class="btn btn-outline-secondary btn-sm mt-1" type="submit">Update</button></form></td></tr>
<?php endforeach; ?></tbody></table></div></section>
<?php endif; ?><?php endif; ?></main>
<?php if (!theme::render('foot', get_defined_vars())) { require APPROOT . '/views/inc/foot.php'; } ?>
/* [End AI:GPT-6] */
