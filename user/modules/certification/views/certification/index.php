<?php

/**
 * STN-LABZ Certification Overview
 *
 * Public overview of the STN-LABZ position, certification,
 * qualification, mission, and access structure.
 *
 * @package MVC
 * @category Certification
 */

require APPROOT . '/views/inc/head.php';

/* [AI:GPT-5.6 Sol | 2026-08-21 19:08:00 UTC] */

$flow = $data['flow'] ?? [];
$sections = $data['sections'] ?? [];
?>

<style>
    .certification-page {
        max-width: 1050px;
        margin: 0 auto;
    }

    .certification-hero {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .certification-principle {
        font-size: 1.2rem;
        font-weight: bold;
        margin: 1.5rem 0;
    }

    .certification-flow {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 0.65rem;
        margin: 2rem 0;
    }

    .certification-flow-item {
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 0.75rem 1rem;
    }

    .certification-flow-arrow {
        font-weight: bold;
    }

    .certification-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .certification-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 1.5rem;
    }

    .certification-card h2 {
        margin-top: 0;
    }

    @media (max-width: 800px) {
        .certification-grid {
            grid-template-columns: 1fr;
        }

        .certification-flow {
            flex-direction: column;
        }

        .certification-flow-arrow {
            transform: rotate(90deg);
        }
    }
</style>

<div class="certification-page">

    <header class="certification-hero">
        <h1>STN-LABZ Certification</h1>

        <p>
            STN-LABZ uses a structured relationship between position,
            position tier, certification, qualification, and mission
            assignment.
        </p>

        <div class="certification-principle">
            <?= htmlspecialchars((string) ($data['principle'] ?? '')); ?>
        </div>
    </header>

    <section>
        <h2>How It Fits Together</h2>

        <div class="certification-flow">
            <?php foreach ($flow as $index => $item): ?>

                <div class="certification-flow-item">
                    <?= htmlspecialchars((string) $item); ?>
                </div>

                <?php if ($index < count($flow) - 1): ?>
                    <div class="certification-flow-arrow">→</div>
                <?php endif; ?>

            <?php endforeach; ?>
        </div>
    </section>

    <section>
        <h2>Explore the Framework</h2>

        <div class="certification-grid">

            <?php foreach ($sections as $section): ?>

                <article class="certification-card">

                    <h2>
                        <?= htmlspecialchars(
                            (string) ($section['title'] ?? '')
                        ); ?>
                    </h2>

                    <p>
                        <?= htmlspecialchars(
                            (string) ($section['description'] ?? '')
                        ); ?>
                    </p>

                    <p>
                        <a href="<?= URLROOT . ($section['url'] ?? ''); ?>">
                            Learn More
                        </a>
                    </p>

                </article>

            <?php endforeach; ?>

        </div>
    </section>

</div>

<!-- [End AI:GPT-5.6 Sol] -->

<?php require APPROOT . '/views/inc/foot.php'; ?>