<?php

/**
 * STN-LABZ Mission Access
 *
 * Public explanation of the STN-LABZ relationship between
 * qualification, certification, mission assignment, and access.
 *
 * @package MVC
 * @category Certification
 */

require APPROOT . '/views/inc/head.php';

/* [AI:GPT-5.6 Sol | 2026-08-21 19:08:00 UTC] */

$qualifiedExample = $data['qualified_example'] ?? [];
$assignedExample = $data['assigned_example'] ?? [];
?>

<style>
    .missions-page {
        max-width: 950px;
        margin: 0 auto;
    }

    .mission-principle {
        text-align: center;
        font-size: 1.4rem;
        font-weight: bold;
        margin: 2rem 0;
    }

    .mission-examples {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .mission-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 1.5rem;
    }

    .mission-card h2 {
        margin-top: 0;
    }

    .mission-row {
        margin-bottom: 0.7rem;
    }

    @media (max-width: 800px) {
        .mission-examples {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="missions-page">

    <h1>Mission Access</h1>

    <p>
        STN-LABZ separates competency from operational assignment.
        A person may be trained, certified, and qualified for a system
        without currently having access to that system.
    </p>

    <div class="mission-principle">
        <?= htmlspecialchars((string) ($data['principle'] ?? '')); ?>
    </div>

    <div class="mission-examples">

        <article class="mission-card">

            <h2>Qualified, No Mission</h2>

            <?php foreach ($qualifiedExample as $label => $value): ?>

                <div class="mission-row">
                    <strong>
                        <?= htmlspecialchars((string) $label); ?>:
                    </strong>

                    <?= htmlspecialchars((string) $value); ?>
                </div>

            <?php endforeach; ?>

        </article>

        <article class="mission-card">

            <h2>Mission Assigned</h2>

            <?php foreach ($assignedExample as $label => $value): ?>

                <div class="mission-row">
                    <strong>
                        <?= htmlspecialchars((string) $label); ?>:
                    </strong>

                    <?= htmlspecialchars((string) $value); ?>
                </div>

            <?php endforeach; ?>

        </article>

    </div>

    <section>
        <h2>Mission Assignment</h2>

        <p>
            Mission assignment is what establishes operational access
            within STN-LABZ.
        </p>

        <p>
            When a mission ends, the access associated with that mission
            ends. Position, certification, and qualification remain separate
            records of organizational placement and demonstrated competency.
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