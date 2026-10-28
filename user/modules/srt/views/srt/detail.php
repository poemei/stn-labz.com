<?php
/*
 * Security Research Target
 * app/views/srt/view.php
 */

require APPROOT . '/views/inc/head.php';

$srt = $data['srt'] ?? [];
?>

<div class="container">

  <!-- Page Header -->
  <section>
    <p>
      <a href="/srt">
        &larr; Security Research
      </a>
    </p>

    <h1>
      <?= htmlspecialchars($srt['title'] ?? 'Security Research Target') ?>
    </h1>

    <p class="hero-lead">
      <?= htmlspecialchars($srt['srt_id'] ?? '') ?>
    </p>
  </section>


  <!-- SRT Overview -->
  <div class="grid">

    <?php if (!empty($srt['status'])): ?>
      <div class="card">
        <h3>Status</h3>

        <p>
          <strong><?= htmlspecialchars($srt['status']) ?></strong>
        </p>
      </div>
    <?php endif; ?>


    <?php if (!empty($srt['classification'])): ?>
      <div class="card">
        <h3>Classification</h3>

        <p>
          <?= htmlspecialchars($srt['classification']) ?>
        </p>
      </div>
    <?php endif; ?>


    <?php if (!empty($srt['authorized_at'])): ?>
      <div class="card">
        <h3>Authorized</h3>

        <p>
          <?= htmlspecialchars(
              date('F j, Y', strtotime($srt['authorized_at']))
          ) ?>
        </p>
      </div>
    <?php endif; ?>

  </div>


  <!-- Summary -->
  <?php if (!empty($srt['summary'])): ?>
    <section>
      <h2>Summary</h2>

      <p>
        <?= nl2br(htmlspecialchars($srt['summary'])) ?>
      </p>
    </section>
  <?php endif; ?>


  <!-- Source Intelligence -->
  <?php if (!empty($srt['source_intelligence_id'])): ?>
    <section>
      <h2>Source Intelligence</h2>

      <p>
        <strong>Intelligence ID:</strong><br>
        <?= htmlspecialchars($srt['source_intelligence_id']) ?>
      </p>
    </section>
  <?php endif; ?>


  <!-- Research Authorization -->
  <section>
    <h2>Research Authorization</h2>

    <?php if (!empty($srt['authorized_by'])): ?>
      <p>
        <strong>Authorized By:</strong><br>
        <?= htmlspecialchars($srt['authorized_by']) ?>
      </p>
    <?php endif; ?>

    <?php if (!empty($srt['authorized_at'])): ?>
      <p>
        <strong>Authorized At:</strong><br>
        <?= htmlspecialchars(
            date('F j, Y H:i', strtotime($srt['authorized_at']))
        ) ?>
      </p>
    <?php endif; ?>
  </section>


  <!-- Record Information -->
  <section>
    <h2>Record Information</h2>

    <ul>

      <?php if (!empty($srt['srt_id'])): ?>
        <li>
          <strong>SRT ID:</strong>
          <?= htmlspecialchars($srt['srt_id']) ?>
        </li>
      <?php endif; ?>

      <?php if (!empty($srt['created_at'])): ?>
        <li>
          <strong>Created:</strong>
          <?= htmlspecialchars(
              date('F j, Y H:i', strtotime($srt['created_at']))
          ) ?>
        </li>
      <?php endif; ?>

      <?php if (!empty($srt['updated_at'])): ?>
        <li>
          <strong>Last Updated:</strong>
          <?= htmlspecialchars(
              date('F j, Y H:i', strtotime($srt['updated_at']))
          ) ?>
        </li>
      <?php endif; ?>

    </ul>
  </section>

</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>