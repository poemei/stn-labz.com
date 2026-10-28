<?php
/*
 * Security Research Targets
 * app/views/srt/index.php
 */

require APPROOT . '/views/inc/head.php';
?>

<div class="container">

  <!-- Page Header -->
  <section>
    <h1><?= htmlspecialchars($data['title']) ?></h1>

    <p class="hero-lead">
      <?= htmlspecialchars($data['sub_title']) ?>
    </p>
  </section>


  <!-- SRT Grid -->
  <?php if (!empty($data['srts'])): ?>

    <div class="grid">

      <?php foreach ($data['srts'] as $srt): ?>

        <div class="card">

          <h3>
            <?= htmlspecialchars($srt['srt_id']) ?>
          </h3>

          <p>
            <strong><?= htmlspecialchars($srt['title']) ?></strong>
          </p>

          <?php if (!empty($srt['authorized_at'])): ?>
            <p>
              <?= htmlspecialchars(
                  date('F j, Y', strtotime($srt['authorized_at']))
              ) ?>
            </p>
          <?php endif; ?>

          <?php if (!empty($srt['summary'])): ?>
            <p>
              <?= htmlspecialchars($srt['summary']) ?>
            </p>
          <?php endif; ?>

          <?php if (!empty($srt['status'])): ?>
            <p>
              Status:
              <strong><?= htmlspecialchars($srt['status']) ?></strong>
            </p>
          <?php endif; ?>

          <a
            class="btn"
            href="/srt/view/<?= rawurlencode($srt['srt_id']) ?>"
          >
            View SRT
          </a>

        </div>

      <?php endforeach; ?>

    </div>

  <?php else: ?>

    <section>
      <p class="muted">
        No confirmed Security Research Targets are currently available.
      </p>
    </section>

  <?php endif; ?>

</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>