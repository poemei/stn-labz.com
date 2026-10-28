<?php
/** Public Security posture. */
/* [AI:GPT-5.6 Sol | 2026-09-02 UTC] */
if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}
$settings = is_array($data['settings'] ?? null) ? $data['settings'] : [];
$sentinelInstalled = !empty($data['sentinel_installed']);
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$pgpAvailable = trim((string) ($settings['pgp_public_key'] ?? '')) !== '';
$rsaAvailable = trim((string) ($settings['rsa_public_key'] ?? '')) !== '';
?>
<main class="container py-5" aria-labelledby="security-title">
    <header class="mb-5">
        <h1 id="security-title">Security</h1>
        <p class="lead">Public cryptographic identity and observable site security capabilities.</p>
    </header>

    <?php if ($sentinelInstalled) : ?>
        <section class="card card-body border-success mb-4" aria-labelledby="sentinel-title">
            <h2 class="h4" id="sentinel-title">Sentinel Monitoring</h2>
            <p class="mb-0">This site is monitored by the installed Sentinel security module.</p>
        </section>
    <?php endif; ?>

    <section class="mb-5" aria-labelledby="identity-title">
        <h2 class="h3" id="identity-title">Cryptographic Identity</h2>

        <?php if (!$pgpAvailable && !$rsaAvailable) : ?>
            <p>No public keys are currently published.</p>
        <?php endif; ?>

        <?php if ($pgpAvailable) : ?>
            <article class="card card-body mb-4" aria-labelledby="pgp-title">
                <h3 class="h4" id="pgp-title">PGP Public Key</h3>
                <?php if (trim((string) ($settings['pgp_label'] ?? '')) !== '') : ?>
                    <p><?= $escape($settings['pgp_label']); ?></p>
                <?php endif; ?>
                <details>
                    <summary>View PGP public key</summary>
                    <pre class="border rounded p-3 mt-3 overflow-auto"><code><?= $escape($settings['pgp_public_key']); ?></code></pre>
                </details>
            </article>
        <?php endif; ?>

        <?php if ($rsaAvailable) : ?>
            <article class="card card-body mb-4" aria-labelledby="rsa-title">
                <h3 class="h4" id="rsa-title">RSA Public Key</h3>
                <?php if (trim((string) ($settings['rsa_label'] ?? '')) !== '') : ?>
                    <p><?= $escape($settings['rsa_label']); ?></p>
                <?php endif; ?>
                <details>
                    <summary>View RSA public key</summary>
                    <pre class="border rounded p-3 mt-3 overflow-auto"><code><?= $escape($settings['rsa_public_key']); ?></code></pre>
                </details>
            </article>
        <?php endif; ?>
    </section>

    <?php if (trim((string) ($settings['security_txt_url'] ?? '')) !== '') : ?>
        <section aria-labelledby="disclosure-title">
            <h2 class="h3" id="disclosure-title">Responsible Disclosure</h2>
            <p>Vulnerability reporting instructions are published in the site's
                <a href="<?= $escape($settings['security_txt_url']); ?>">security.txt file</a>.
            </p>
        </section>
    <?php endif; ?>
</main>
<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}
/* [End AI:GPT-5.6 Sol] */
