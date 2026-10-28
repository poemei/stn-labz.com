<?php

/**
 * STN-LABZ Certification Framework
 *
 * Public explanation of STN-LABZ certification domains,
 * credential records, and certification lifecycle.
 *
 * @package MVC
 * @category Certification
 */

require APPROOT . '/views/inc/head.php';

/* [AI:GPT-5.6 Sol | 2026-08-21 19:08:00 UTC] */

$certifications = $data['certifications'] ?? [];
$recordFields = $data['record_fields'] ?? [];
$lifecycle = $data['lifecycle'] ?? [];
?>

<style>
    .certifications-page {
        max-width: 1050px;
        margin: 0 auto;
    }

    .certification-principle {
        border-left: 4px solid #777;
        padding: 1rem;
        margin: 1.5rem 0;
        font-weight: bold;
    }

    .certification-domain-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
        margin: 2rem 0;
    }

    .certification-domain-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 1.25rem;
    }

    .certification-domain-card h3 {
        margin-top: 0;
    }

    .certification-lifecycle {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .certification-state {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 0.55rem 0.8rem;
    }

    @media (max-width: 800px) {
        .certification-domain-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="certifications-page">

    <h1>Certification Framework</h1>

    <p>
        STN-LABZ certification establishes demonstrated competency
        against a defined certification standard.
    </p>

    <div class="certification-principle">
        <?= htmlspecialchars((string) ($data['principle'] ?? '')); ?>
    </div>

    <section>
        <h2>Certification Domains</h2>

        <div class="certification-domain-grid">

            <?php foreach ($certifications as $item): ?>

                <article class="certification-domain-card">

                    <h3>
                        <?= htmlspecialchars(
                            (string) ($item['name'] ?? '')
                        ); ?>
                    </h3>

                    <p>
                        <strong>Domain:</strong>
                        <?= htmlspecialchars(
                            (string) ($item['domain'] ?? '')
                        ); ?>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <?= htmlspecialchars(
                            (string) ($item['status'] ?? '')
                        ); ?>
                    </p>

                </article>

            <?php endforeach; ?>

        </div>
    </section>

    <section>
        <h2>Certification Records</h2>

        <p>
            Issued certifications maintain a record of the standard,
            evidence, decision, and credential under which certification
            was awarded.
        </p>

        <ul>
            <?php foreach ($recordFields as $field): ?>
                <li><?= htmlspecialchars((string) $field); ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section>
        <h2>Certification Lifecycle</h2>

        <div class="certification-lifecycle">
            <?php foreach ($lifecycle as $state): ?>
                <div class="certification-state">
                    <?= htmlspecialchars((string) $state); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section>
        <h2>Certification and Access</h2>

        <p>
            Certification demonstrates competency for applicable positions
            and missions.
        </p>

        <p>
            Certification does not itself provide operational access.
            Mission assignment provides mission access.
        </p>
    </section>

    <p>
        <a href="<?= URLROOT; ?>/certification">
            Back to Certification
        </a>
    </p>

</div>

<!-- [End AI:GPT-5.6 Sol] -->

<?php require APPROOT . '/views/inc/foot.php'; ?>