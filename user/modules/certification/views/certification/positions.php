<?php
/* [AI:GPT-6 | 2026-10-29 04:17:13 UTC] */


/**
 * STN-LABZ Positions and Tiers
 *
 * Public explanation of the STN-LABZ tiered position framework.
 *
 * @package MVC
 * @category Certification
 */

require APPROOT . '/views/inc/head.php';

/* [AI:GPT-5.6 Sol | 2026-08-21 19:08:00 UTC] */

$flow = $data['flow'] ?? [];
$fields = $data['position_fields'] ?? [];
?>

<style>
    .positions-page {
        max-width: 950px;
        margin: 0 auto;
    }

    .positions-principle {
        border-left: 4px solid #777;
        padding: 1rem;
        margin: 1.5rem 0;
        font-weight: bold;
    }

    .positions-flow {
        margin: 2rem 0;
        text-align: center;
    }

    .positions-flow div {
        margin: 0.45rem 0;
    }

    .positions-fields {
        columns: 2;
        margin-top: 1.5rem;
    }

    @media (max-width: 700px) {
        .positions-fields {
            columns: 1;
        }
    }
</style>

<div class="positions-page">

    <h1>Positions &amp; Tiers</h1>

    <table class="table"><thead><tr><th>Tier</th><th>Position</th></tr></thead><tbody>
    <?php foreach (array_reverse($data['tiers'] ?? [], true) as $tier => $position): ?>
        <tr><td><?= (int) $tier; ?></td><td><?= htmlspecialchars($position, ENT_QUOTES, 'UTF-8'); ?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <p>Tier 9 preserves unrestricted Administrator / TSC authority. Other tiers require applicable mission assignments. Technical investigation requires the matching product certification and responsibility-specific qualification.</p>

    <p>
        STN-LABZ positions define organizational responsibility.
        Position tiers determine whether an employee may be considered
        for particular systems, operational domains, and mission roles.
    </p>

    <div class="positions-principle">
        <?= htmlspecialchars((string) ($data['principle'] ?? '')); ?>
    </div>

    <section>
        <h2>Position Structure</h2>

        <div class="positions-flow">

            <?php foreach ($flow as $index => $item): ?>

                <div>
                    <strong>
                        <?= htmlspecialchars((string) $item); ?>
                    </strong>
                </div>

                <?php if ($index < count($flow) - 1): ?>
                    <div>↓</div>
                <?php endif; ?>

            <?php endforeach; ?>

        </div>
    </section>

    <section>
        <h2>Position Definition</h2>

        <p>
            Controlled STN-LABZ positions are defined using a consistent
            set of organizational and operational fields.
        </p>

        <ul class="positions-fields">
            <?php foreach ($fields as $field): ?>
                <li><?= htmlspecialchars((string) $field); ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section>
        <h2>What Position Tier Means</h2>

        <p>
            A Position Tier establishes eligibility for consideration.
            It does not itself provide access to Digit, Ezra, Sentinel, Mission Control, or any other
            controlled operational domain.
        </p>

        <p>
            Required certification and qualification must still be met,
            and operational access exists when a mission is assigned.
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

<!-- [End AI:GPT-6] -->
