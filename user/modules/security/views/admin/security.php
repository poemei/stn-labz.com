<?php
/** Security administration. */
/* [AI:GPT-5.6 Sol | 2026-09-02 UTC] */
if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}
$state = (string) ($data['database_state'] ?? 'missing');
$errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];
$values = is_array($data['values'] ?? null) ? $data['values'] : [];
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<p><small><a href="/admin">Admin</a> &gt;&gt; <strong>Security</strong></small></p>
<main class="container my-4" aria-labelledby="security-admin-title">
    <header class="mb-4">
        <h1 id="security-admin-title">Security</h1>
        <p class="text-body-secondary">Publish site-owned public keys and inspect automatically detected protection.</p>
    </header>

    <?php if ($state === 'missing') : ?>
        <div class="alert alert-warning" role="status">
            <h2 class="h5">Database installation required</h2>
            <p>The module-owned Security settings table is not installed.</p>
            <form method="post" action="/admin/security">
                <?= $this->csrf_field(); ?>
                <input type="hidden" name="action" value="install_sql">
                <button class="btn btn-primary" type="submit">Install SQL</button>
            </form>
        </div>
    <?php else : ?>
        <?php if (!empty($data['installed'])) : ?><div class="alert alert-light border">Security SQL installed.</div><?php endif; ?>
        <?php if (!empty($data['saved'])) : ?><div class="alert alert-light border">Security settings saved.</div><?php endif; ?>
        <?php if (!empty($data['deleted_data'])) : ?><div class="alert alert-light border">Security data deleted. Schema preserved.</div><?php endif; ?>
        <?php if ($errors !== []) : ?>
            <div class="alert alert-danger" role="alert"><ul class="mb-0">
                <?php foreach ($errors as $error) : ?><li><?= $escape($error); ?></li><?php endforeach; ?>
            </ul></div>
        <?php endif; ?>

        <section class="card card-body mb-4" aria-labelledby="sentinel-status-title">
            <h2 class="h4" id="sentinel-status-title">Sentinel Detection</h2>
            <?php if (!empty($data['sentinel_installed'])) : ?>
                <p class="text-success mb-0"><strong>Installed.</strong> The public Security page displays Sentinel monitoring.</p>
            <?php else : ?>
                <p class="mb-0"><strong>Not installed.</strong> No Sentinel claim is displayed publicly.</p>
            <?php endif; ?>
        </section>

        <form method="post" action="/admin/security" class="mb-5">
            <?= $this->csrf_field(); ?>
            <input type="hidden" name="action" value="save_settings">
            <section class="card card-body mb-4" aria-labelledby="pgp-settings-title">
                <h2 class="h4" id="pgp-settings-title">PGP Public Key</h2>
                <label class="form-label" for="pgp-label">Label</label>
                <input class="form-control mb-3" id="pgp-label" name="pgp_label" maxlength="160" value="<?= $escape($values['pgp_label'] ?? ''); ?>" placeholder="Poe — security contact">
                <label class="form-label" for="pgp-public-key">Public key</label>
                <textarea class="form-control font-monospace" id="pgp-public-key" name="pgp_public_key" rows="12"><?= $escape($values['pgp_public_key'] ?? ''); ?></textarea>
            </section>

            <section class="card card-body mb-4" aria-labelledby="rsa-settings-title">
                <h2 class="h4" id="rsa-settings-title">RSA Public Key</h2>
                <label class="form-label" for="rsa-label">Label</label>
                <input class="form-control mb-3" id="rsa-label" name="rsa_label" maxlength="160" value="<?= $escape($values['rsa_label'] ?? ''); ?>" placeholder="Poemei.com release signing">
                <label class="form-label" for="rsa-public-key">Public key</label>
                <textarea class="form-control font-monospace" id="rsa-public-key" name="rsa_public_key" rows="12"><?= $escape($values['rsa_public_key'] ?? ''); ?></textarea>
            </section>

            <section class="card card-body mb-4" aria-labelledby="disclosure-settings-title">
                <h2 class="h4" id="disclosure-settings-title">Responsible Disclosure</h2>
                <label class="form-label" for="security-txt-url">Security.txt URL</label>
                <input class="form-control" id="security-txt-url" name="security_txt_url" maxlength="2048" value="<?= $escape($values['security_txt_url'] ?? '/.well-known/security.txt'); ?>">
            </section>
            <button class="btn btn-primary" type="submit">Save Security Settings</button>
        </form>

        <section class="card card-body border-danger" aria-labelledby="lifecycle-title">
            <h2 class="h4" id="lifecycle-title">Data Lifecycle</h2>
            <p>Delete Data clears the public keys and settings while preserving the schema and module. Core owns Nuke.</p>
            <div class="d-flex flex-wrap gap-2">
                <form method="post" action="/admin/security" onsubmit="return confirm('Delete all Security data? The table and module will remain installed.');">
                    <?= $this->csrf_field(); ?><input type="hidden" name="action" value="delete_data">
                    <button class="btn btn-outline-danger" type="submit">Delete Data</button>
                </form>
                <form method="post" action="/admin/uninstall" onsubmit="return confirm('Nuke Security? Core will remove the module and its verified table.');">
                    <?= $this->csrf_field(); ?><input type="hidden" name="module" value="security">
                    <button class="btn btn-danger" type="submit">Nuke</button>
                </form>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}
/* [End AI:GPT-5.6 Sol] */
