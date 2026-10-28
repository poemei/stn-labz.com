<?php

/**
 * STN-LABZ Operational Domains
 *
 * Public overview of controlled STN-LABZ operational domains.
 *
 * @package MVC
 * @category Certification
 */

require APPROOT . '/views/inc/head.php';

/* [AI:GPT-5.6 Sol | 2026-08-21 19:08:00 UTC] */

$domains = $data['domains'] ?? [];
?>

<style>
    .domains-page {
        max-width: 1050px;
        margin: 0 auto;
    }

    .domains-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .domain-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 1.5rem;
    }

    .domain-card h2 {
        margin-top: 0;
    }

    @media (max-width: 800px) {
        .domains-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="domains-page">

    <h1>Operational Domains</h1>

    <p>
        STN-LABZ organizes controlled operational work into defined
        domains. Each domain may establish its own position, tier,
        certification, qualification, and mission requirements.
    </p>

    <div class="domains-grid">

        <?php foreach ($domains as $domain): ?>

            <article class="domain-card">

                <h2>
                    <?= htmlspecialchars(
                        (string) ($domain['name'] ?? '')
                    ); ?>
                </h2>

                <p>
                    <?= htmlspecialchars(
                        (string) ($domain['description'] ?? '')
                    ); ?>
                </p>

            </article>

        <?php endforeach; ?>

    </div>

    <p>
        <a href="<?= URLROOT; ?>/certification">
            Back to Certification
        </a>
    </p>

</div>

<!-- [End AI:GPT-5.6 Sol] -->

<?php require APPROOT . '/views/inc/foot.php'; ?>