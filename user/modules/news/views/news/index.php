<?php require APPROOT . '/views/inc/head.php'; ?>

<main class="container">

  <!-- News Header -->
  <section>
    <h1><?= htmlspecialchars((string)($data['title'] ?? 'News')) ?></h1>

    <p class="hero-lead">
      <?= htmlspecialchars(
        (string)($data['sub_title'] ?? 'Engineering updates, research, releases, and developments from across STN-LABZ.')
      ) ?>
    </p>
  </section>


  <!-- News Feed -->
  <?php if (!empty($data['items'])): ?>

    <div class="grid">

      <?php foreach ($data['items'] as $it): ?>

        <?php
          $title   = (string)($it['title'] ?? 'Untitled');
          $date    = (string)($it['date'] ?? '');
          $summary = (string)($it['summary'] ?? '');
          $link    = (string)($it['link'] ?? '');
          $pinned  = !empty($it['pinned']);

          $tags = [];

          if (!empty($it['tags'])) {
              $tags = is_array($it['tags'])
                  ? $it['tags']
                  : explode(',', (string)$it['tags']);
          }
        ?>

        <article class="card announce-item<?= $pinned ? ' pinned' : '' ?>">

          <header class="announce-head">

            <?php if ($pinned): ?>
              <p>
                <strong>Featured Update</strong>
              </p>
            <?php endif; ?>

            <h3>
              <?php if ($link !== ''): ?>
                <a href="<?= htmlspecialchars($link) ?>">
                  <?= htmlspecialchars($title) ?>
                </a>
              <?php else: ?>
                <?= htmlspecialchars($title) ?>
              <?php endif; ?>
            </h3>

            <?php if ($date !== ''): ?>
              <time datetime="<?= htmlspecialchars($date) ?>">
                <?= htmlspecialchars(date('F j, Y', strtotime($date))) ?>
              </time>
            <?php endif; ?>

          </header>


          <?php if ($summary !== ''): ?>
            <p>
              <?= htmlspecialchars($summary) ?>
            </p>
          <?php endif; ?>


          <?php if (!empty($tags)): ?>

            <ul class="announce-tags">

              <?php foreach ($tags as $tag): ?>

                <?php $tag = trim((string)$tag); ?>

                <?php if ($tag !== ''): ?>
                  <li><?= htmlspecialchars($tag) ?></li>
                <?php endif; ?>

              <?php endforeach; ?>

            </ul>

          <?php endif; ?>


          <?php if ($link !== ''): ?>
            <p>
              <a class="btn" href="<?= htmlspecialchars($link) ?>">
                Read More
              </a>
            </p>
          <?php endif; ?>

        </article>

      <?php endforeach; ?>

    </div>

  <?php else: ?>

    <section>
      <p class="muted">
        No recent updates available.
      </p>
    </section>

  <?php endif; ?>

</main>

<?php require APPROOT . '/views/inc/foot.php'; ?>