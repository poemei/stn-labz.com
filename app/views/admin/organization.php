<?php
/* [AI:GPT-6 | 2026-10-29 04:17:13 UTC] */
require APPROOT . '/views/inc/head.php';
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$id = (int) $data['account_id'];
$records = $data['records'];
?>
<main class="container py-4">
<p><a href="/admin/accounts">Accounts</a> / Organization</p>
<h1>Organization and mission access</h1>
<p>Position, certification and qualification establish responsibility and eligibility. An active mission establishes access to its named module and system. Only the Administrator manages these records.</p>
<?php if ($data['error']): ?><p role="alert" class="alert alert-danger"><?= $escape($data['error']); ?></p><?php endif; ?>
<?php if (!$data['ready']): ?>
<p>Organization storage is not installed. Installation adds tables and preserves existing accounts and module allowances.</p>
<form method="post"><?= $this->csrf_field(); ?><input type="hidden" name="action" value="install_organization"><button class="btn btn-primary">Install organization SQL</button></form>
<?php else: ?>
<form method="get" class="my-4"><label>Person <select name="account_id" required>
<option value="">Select an account</option>
<?php foreach ($data['accounts'] as $account): ?><option value="<?= (int) $account['id']; ?>" <?= $id === (int) $account['id'] ? 'selected' : ''; ?>><?= $escape($account['display_name'] . ' (' . $account['username'] . ')'); ?></option><?php endforeach; ?>
</select></label> <button>Inspect</button></form>
<?php if ($id > 0): ?>
<h2>Position</h2>
<p>Current record: <?= $escape($records['position']['position_name'] ?? 'No explicit position'); ?> — <?= $escape($data['tiers'][$records['position']['tier'] ?? 0] ?? 'Existing account classification applies'); ?></p>
<p>Tier 9 remains controlled by the existing Administrator account classification. All other staff require explicit missions and applicable credentials; legacy module allowances remain stored for reference.</p>
<form method="post" class="mb-4">
<?= $this->csrf_field(); ?><input type="hidden" name="account_id" value="<?= $id; ?>"><input type="hidden" name="action" value="position">
<label>Position name <input name="position_name" maxlength="150" required value="<?= $escape($records['position']['position_name'] ?? ''); ?>"></label>
<label>Tier <select name="tier"><?php foreach ($data['tiers'] as $tier => $label): if ($tier === 9) { continue; } ?><option value="<?= $tier; ?>" <?= (int) ($records['position']['tier'] ?? 0) === $tier ? 'selected' : ''; ?>><?= $tier; ?> — <?= $escape($label); ?></option><?php endforeach; ?></select></label>
<button>Save position</button></form>

<h2>Certifications and qualifications</h2>
<p>Record an approved standard and evidence separately for each credential. These records do not grant access. Expiration dates are inclusive UTC dates.</p>
<div class="table-responsive"><table class="table"><thead><tr><th>Kind / system</th><th>Standard / evidence</th><th>Status / expiration</th><th>Authority</th><th>Change status</th></tr></thead><tbody>
<?php foreach ($records['credentials'] as $record): ?>
<tr><td><?= $escape($record['kind']); ?><?= $record['kind'] === 'qualification' ? ' (Tier ' . (int) $record['qualification_tier'] . ')' : ''; ?> / <?= $escape($record['system_slug']); ?></td><td><?= $escape($record['standard_reference']); ?><br><?= $escape($record['evidence_reference']); ?></td><td><?= $escape($record['status']); ?> / <?= $escape($record['expires_on'] ?? 'None'); ?></td><td>Account <?= (int) $record['changed_by']; ?><br><?= $escape($record['updated_at']); ?></td><td>
<form method="post"><?= $this->csrf_field(); ?><input type="hidden" name="account_id" value="<?= $id; ?>"><input type="hidden" name="record_id" value="<?= (int) $record['id']; ?>"><input type="hidden" name="action" value="credential_status"><select name="status" aria-label="Credential status"><?php foreach (['pending','active','suspended','expired','revoked'] as $status): ?><option <?= $status === $record['status'] ? 'selected' : ''; ?>><?= $status; ?></option><?php endforeach; ?></select><button>Save</button></form>
</td></tr><?php endforeach; ?></tbody></table></div>
<form method="post" class="mb-4"><?= $this->csrf_field(); ?><input type="hidden" name="account_id" value="<?= $id; ?>"><input type="hidden" name="action" value="credential">
<label>Kind <select name="kind"><option value="certification">Certification</option><option value="qualification">Qualification</option></select></label>
<label>Qualification responsibility (qualifications only) <select name="qualification_tier"><?php foreach ($data['tiers'] as $tier => $label): if ($tier === 9) { continue; } ?><option value="<?= $tier; ?>"><?= $tier; ?> — <?= $escape($label); ?></option><?php endforeach; ?></select></label>
<label>System <input name="system_slug" placeholder="chaos-mvc" pattern="[a-z][a-z0-9_-]{0,62}" required></label>
<label>Approved standard <input name="standard_reference" maxlength="255" required></label>
<label>Evidence <input name="evidence_reference" maxlength="255" required></label>
<label>Expires <input type="date" name="expires_on"></label><button>Record active credential</button></form>

<h2>Mission assignments</h2>
<p>Support uses the ticket topic identifier as its system scope (for example, digit). Tiers 3, 4 and 7 additionally require active certification and qualification for the same system. Tiers 1–5 use Support operations; assigning them another module does not grant its administrative actions. Tier 6–8 module assignments authorize that module's existing administrative interface, subject to its own stricter checks.</p>
<div class="table-responsive"><table class="table"><thead><tr><th>Module / system</th><th>Mission authority reference</th><th>Active / expiration</th><th>Changed by</th><th>Withdraw</th></tr></thead><tbody>
<?php foreach ($records['missions'] as $record): ?><tr><td><?= $escape($record['module_slug']); ?> / <?= $escape($record['system_slug']); ?></td><td><?= $escape($record['mission_reference']); ?></td><td><?= $record['active'] ? 'Yes' : 'No'; ?> / <?= $escape($record['expires_on'] ?? 'None'); ?></td><td>Account <?= (int) $record['changed_by']; ?><br><?= $escape($record['updated_at']); ?></td><td>
<?php if ($record['active']): ?><form method="post"><?= $this->csrf_field(); ?><input type="hidden" name="account_id" value="<?= $id; ?>"><input type="hidden" name="record_id" value="<?= (int) $record['id']; ?>"><input type="hidden" name="action" value="withdraw"><button>Withdraw access</button></form><?php endif; ?>
</td></tr><?php endforeach; ?></tbody></table></div>
<form method="post"><?= $this->csrf_field(); ?><input type="hidden" name="account_id" value="<?= $id; ?>"><input type="hidden" name="action" value="mission">
<label>Module <select name="module_slug"><?php foreach ($data['modules'] as $module): ?><option><?= $escape($module); ?></option><?php endforeach; ?></select></label>
<label>System <input name="system_slug" pattern="[a-z][a-z0-9_-]{0,62}" required></label>
<label>Mission authority reference <input name="mission_reference" maxlength="255" required></label>
<label>Expires <input type="date" name="expires_on"></label><button>Assign mission</button></form>
<?php endif; endif; ?>
</main>
<?php require APPROOT . '/views/inc/foot.php'; ?>
<!-- [End AI:GPT-6] -->
