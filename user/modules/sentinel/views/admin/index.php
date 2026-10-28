<?php
// path: /user/modules/sentinel/views/admin/index.php
theme::render('head', get_defined_vars());
$ready = !empty($data['schema_available']);
$databaseState = (string) ($data['database_state'] ?? 'invalid');
$config = $data['config_map'] ?? [];
$events = $data['events'] ?? [];
$whitelist = $data['whitelist'] ?? [];
$esc = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="container py-5">
    <h2>Sentinel Security</h2>
    <p class="text-muted">Local threat enforcement and STN-LABZ intelligence API</p>
    <?php if (!empty($data['notice']['message'])): ?>
        <div class="alert <?= ($data['notice']['type'] ?? '') === 'error' ? 'alert-danger' : 'alert-success' ?>" role="status"><?= $esc($data['notice']['message']) ?></div>
    <?php endif; ?>
    <?php if (!empty($data['error'])): ?>
        <div class="alert alert-danger"><?= $esc($data['error']) ?></div>
    <?php endif; ?>
    <div class="mb-3">Database State: <strong><?= $esc($databaseState) ?></strong></div>
    <?php if (!$ready): ?>
        <?php if ($databaseState === 'missing'): ?>
            <p>Sentinel database schema is not installed.</p>
            <form method="post" action="/admin/sentinel">
                <?= $this->csrf_field() ?>
                <input type="hidden" name="action" value="install_sql">
                <button type="submit" class="btn btn-warning">Install SQL</button>
            </form>
        <?php elseif ($databaseState === 'update'): ?>
            <p>Sentinel has a supported legacy database schema. Update SQL will back up existing configuration, consolidate duplicate settings while keeping the latest value for each key, and enforce unique setting names.</p>
            <form method="post" action="/admin/sentinel" onsubmit="return confirm('Back up the entire database before migrating Sentinel SQL. Continue with the schema update?');">
                <?= $this->csrf_field() ?>
                <input type="hidden" name="action" value="update_sql">
                <button type="submit" class="btn btn-warning">Update SQL</button>
            </form>
        <?php else: ?>
            <p class="text-danger">Sentinel schema state is invalid. No administration mutations will run until the schema is reviewed.</p>
        <?php endif; ?>
    <?php else: ?>
        <form method="post" action="/admin/sentinel">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="action" value="update_config">
            <div class="mb-3">
                <label class="form-label">Site ID</label>
                <input class="form-control" name="settings[site_id]" value="<?= $esc($config['site_id'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">STN-LABZ API Key</label>
                <input type="password" class="form-control" name="settings[api_key]" autocomplete="new-password" placeholder="Leave empty to keep the existing key">
            </div>
            <div class="mb-3">
                <label class="form-label">Project Honey Pot http:BL Key (optional plugin)</label>
                <input type="password" class="form-control" name="settings[httpbl_key]" autocomplete="new-password" placeholder="Leave blank to preserve existing key">
                <div class="form-text">Stored in Sentinel configuration. An absent key disables only http:BL lookup, not Sentinel protection.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Malicious URI Patterns (comma-separated)</label>
                <textarea class="form-control" name="settings[malicious_patterns]" rows="4"><?= $esc($config['malicious_patterns'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
        <hr>
        <form method="post" action="/admin/sentinel" class="mb-3">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="action" value="sync_intelligence">
            <button type="submit" class="btn btn-secondary">Sync STN-LABZ Intelligence</button>
        </form>
        <p>Last sync: <?= !empty($config['last_sync_time']) ? $esc(gmdate('Y-m-d H:i:s', (int) $config['last_sync_time']) . ' UTC') : 'Never' ?></p>
        <form method="post" action="/admin/sentinel" class="mb-4">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="action" value="block_ip">
            <label class="form-label">Block IP address</label>
            <div class="input-group">
                <input class="form-control" name="ip" required>
                <button type="submit" class="btn btn-danger">Block</button>
            </div>
        </form>
        <h3>IP Whitelist</h3>
        <p class="text-muted">Locally whitelisted IP addresses bypass Sentinel blocking and URI pattern enforcement. Threat records are preserved.</p>
        <form method="post" action="/admin/sentinel" class="mb-3">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="action" value="whitelist_add">
            <label class="form-label" for="sentinel-whitelist-ip">IP address</label>
            <div class="input-group">
                <input class="form-control" id="sentinel-whitelist-ip" name="ip" required placeholder="192.0.2.10 or 2001:db8::10">
                <button type="submit" class="btn btn-success">Add to Whitelist</button>
            </div>
        </form>
        <?php if (!$whitelist): ?>
            <p class="text-muted">No whitelisted addresses.</p>
        <?php else: ?>
            <div class="table-responsive mb-4">
                <table class="table table-striped">
                    <thead><tr><th>IP address</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($whitelist as $ip): ?>
                        <tr>
                            <td><code><?= $esc($ip) ?></code></td>
                            <td>
                                <form method="post" action="/admin/sentinel">
                                    <?= $this->csrf_field() ?>
                                    <input type="hidden" name="action" value="whitelist_remove">
                                    <input type="hidden" name="ip" value="<?= $esc($ip) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <h3>Project Honey Pot http:BL</h3>
        <p class="text-muted">Manual IPv4 reputation lookup only. Uses the optional key saved in Sentinel configuration. Never blocks automatically. REMOTE_ADDR may identify a proxy instead of the original client.</p>
        <form method="post" action="/admin/sentinel" class="mb-3">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="action" value="lookup_httpbl">
            <label class="form-label" for="sentinel-httpbl-ip">IPv4 address to investigate</label>
            <div class="input-group">
                <input class="form-control" id="sentinel-httpbl-ip" name="ip" required placeholder="198.51.100.25">
                <button type="submit" class="btn btn-secondary">Check http:BL</button>
            </div>
        </form>
        <?php if (!empty($data['httpbl'])): $result = $data['httpbl']; ?>
            <div class="border rounded p-3 mb-4">
                <strong>Lookup:</strong> <?= $esc($result['ip'] ?? '') ?> · <?= $esc($result['time_utc'] ?? '') ?> UTC
                <p class="mb-1"><strong>Resolver assessment:</strong> <?= $esc($result['status'] ?? '') ?></p>
                <?php if (($result['status'] ?? '') === 'LISTED'): ?>
                    <p class="mb-1">Response: <?= $esc($result['answer'] ?? '') ?>; Days since activity: <?= $esc($result['days'] ?? '') ?>; Threat score: <?= $esc($result['score'] ?? '') ?>; Type bits: <?= $esc($result['type_bits'] ?? '') ?></p>
                <?php endif; ?>
                <small class="text-muted">An empty response or resolution failure does not establish that an IP is safe. Reputation does not prove an attack or compromise.</small>
            </div>
        <?php endif; ?>
        <h3>Recent Events</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Time</th><th>IP</th><th>Category</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($events as $event): ?>
                    <tr><td><?= $esc($event['timestamp'] ?? '') ?></td><td><?= $esc($event['ip_address'] ?? '') ?></td><td><?= $esc($event['threat_category'] ?? '') ?></td><td><?= $esc($event['status'] ?? '') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <form method="post" action="/admin/sentinel" onsubmit="return confirm('Permanently delete local Sentinel threat records? Configuration is preserved.');">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="action" value="delete_data">
            <button type="submit" class="btn btn-outline-danger">Delete Data</button>
        </form>
        <form method="post" action="/admin/sentinel" class="mt-2" onsubmit="return confirm('Reset local mutable threat records? Sentinel config and schema are preserved.');">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="action" value="reset_data">
            <button type="submit" class="btn btn-outline-warning">Data Reset</button>
        </form>
    <?php endif; ?>
</div>
<?php
theme::render('footer', get_defined_vars());
