<?php
/*
 * STN-LABZ
 * Security Research Administration
 *
 * app/views/admin/srt.php
 */

require_once APPROOT . '/views/inc/head.php';

$srts = $data['srts'] ?? [];
?>

<style>
.srt-admin-note {
    border-left: 3px solid var(--teal);
    padding-left: 1rem;
}

.srt-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem 1.5rem;
}

.srt-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.srt-field.full {
    grid-column: 1 / -1;
}

.srt-field label {
    font-weight: 600;
    color: var(--ink);
}

.srt-field input,
.srt-field select,
.srt-field textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 0.75rem;
    background: var(--panel-light);
    color: var(--ink);
    border: 1px solid var(--line);
    border-radius: 8px;
    font: inherit;
}

.srt-field input:focus,
.srt-field select:focus,
.srt-field textarea:focus {
    outline: none;
    border-color: var(--teal);
    box-shadow: var(--glow);
}

.srt-field textarea {
    min-height: 150px;
    resize: vertical;
}

.srt-status {
    display: inline-block;
    padding: 0.25rem 0.6rem;
    border: 1px solid var(--line);
    border-radius: 999px;
    color: var(--teal);
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.3px;
}

.srt-meta {
    font-size: 0.9rem;
    color: var(--muted);
}

.srt-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.srt-admin-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.srt-admin-card form {
    margin-top: 0.5rem;
}

@media (max-width: 768px) {
    .srt-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>


<div class="container">

  <!-- Header -->
  <section>

    <h1>
      <?= htmlspecialchars(
          (string)($data['title'] ?? 'Security Research Administration')
      ) ?>
    </h1>

    <p class="hero-lead">
      <?= htmlspecialchars(
          (string)(
              $data['sub_title']
              ?? 'Manage permanent STN-LABZ Security Research Target records.'
          )
      ) ?>
    </p>

    <p class="srt-admin-note">
      <strong>SRT records are permanent.</strong>
      Their lifecycle is managed through status changes. Records are never
      deleted.
    </p>

  </section>


  <!-- Create SRT -->
  <section>

    <h2>Create Security Research Target</h2>

    <form method="post" action="/admin/srt">
              <?= $this->csrf_field(); ?>
              <input type="hidden" name="action" value="create">

      <div class="srt-form-grid">

        <div class="srt-field">
          <label for="srt_id">SRT ID</label>

          <input
            type="text"
            id="srt_id"
            name="srt_id"
            placeholder="SRT-20260818-001"
            required
          >
        </div>


        <div class="srt-field">
          <label for="status">Status</label>

          <select id="status" name="status" required>
            <option value="ACTIVE">ACTIVE</option>
            <option value="HOLD">HOLD</option>
            <option value="CLOSED">CLOSED</option>
          </select>
        </div>


        <div class="srt-field full">
          <label for="title">Title</label>

          <input
            type="text"
            id="title"
            name="title"
            placeholder="Security Research Target title"
            required
          >
        </div>


        <div class="srt-field">
          <label for="classification">Classification</label>

          <input
            type="text"
            id="classification"
            name="classification"
            placeholder="Optional"
          >
        </div>


        <div class="srt-field">
          <label for="source_intelligence_id">
            Source Intelligence ID
          </label>

          <input
            type="text"
            id="source_intelligence_id"
            name="source_intelligence_id"
            placeholder="Optional"
          >
        </div>


        <div class="srt-field full">
          <label for="summary">Summary</label>

          <textarea
            id="summary"
            name="summary"
            placeholder="Describe the research target, scope, and reason for investigation."
            required
          ></textarea>
        </div>


        <div class="srt-field">
          <label for="authorized_by">Authorized By</label>

          <input
            type="text"
            id="authorized_by"
            name="authorized_by"
            required
          >
        </div>


        <div class="srt-field">
          <label for="authorized_at">Authorized At</label>

          <input
            type="datetime-local"
            id="authorized_at"
            name="authorized_at"
            required
          >
        </div>

      </div>


      <div class="srt-actions">

        <button class="btn primary" type="submit">
          Create SRT
        </button>

      </div>

    </form>

  </section>


  <!-- Existing Records -->
  <section>

    <h2>Security Research Targets</h2>

    <?php if (!empty($srts)): ?>

      <div class="grid">

        <?php foreach ($srts as $srt): ?>

          <div class="card srt-admin-card">

            <div>

              <h3>
                <?= htmlspecialchars(
                    (string)($srt['srt_id'] ?? 'Unknown SRT')
                ) ?>
              </h3>

              <span class="srt-status">
                <?= htmlspecialchars(
                    (string)($srt['status'] ?? 'UNKNOWN')
                ) ?>
              </span>

            </div>


            <div>

              <p>
                <strong>
                  <?= htmlspecialchars(
                      (string)($srt['title'] ?? '')
                  ) ?>
                </strong>
              </p>

              <?php if (!empty($srt['authorized_at'])): ?>

                <p class="srt-meta">
                  Authorized
                  <?= htmlspecialchars(
                      date(
                          'F j, Y H:i',
                          strtotime($srt['authorized_at'])
                      )
                  ) ?>
                </p>

              <?php endif; ?>

            </div>


            <form method="post" action="/admin/srt">
              <?= $this->csrf_field(); ?>
              <input type="hidden" name="action" value="update">

              <input
                type="hidden"
                name="srt_id"
                value="<?= htmlspecialchars(
                    (string)($srt['srt_id'] ?? '')
                ) ?>"
              >


              <div class="srt-form-grid">

                <div class="srt-field full">
                  <label>Title</label>

                  <input
                    type="text"
                    name="title"
                    value="<?= htmlspecialchars(
                        (string)($srt['title'] ?? '')
                    ) ?>"
                    required
                  >
                </div>


                <div class="srt-field">
                  <label>Status</label>

                  <select name="status" required>

                    <?php
                    $statuses = [
                        'ACTIVE',
                        'HOLD',
                        'CLOSED'
                    ];

                    foreach ($statuses as $status):
                    ?>

                      <option
                        value="<?= htmlspecialchars($status) ?>"
                        <?= (($srt['status'] ?? '') === $status)
                            ? 'selected'
                            : '' ?>
                      >
                        <?= htmlspecialchars($status) ?>
                      </option>

                    <?php endforeach; ?>

                  </select>
                </div>


                <div class="srt-field">
                  <label>Classification</label>

                  <input
                    type="text"
                    name="classification"
                    value="<?= htmlspecialchars(
                        (string)($srt['classification'] ?? '')
                    ) ?>"
                  >
                </div>


                <div class="srt-field full">
                  <label>Source Intelligence ID</label>

                  <input
                    type="text"
                    name="source_intelligence_id"
                    value="<?= htmlspecialchars(
                        (string)($srt['source_intelligence_id'] ?? '')
                    ) ?>"
                  >
                </div>


                <div class="srt-field full">
                  <label>Summary</label>

                  <textarea
                    name="summary"
                    required
                  ><?= htmlspecialchars(
                      (string)($srt['summary'] ?? '')
                  ) ?></textarea>
                </div>


                <div class="srt-field">
                  <label>Authorized By</label>

                  <input
                    type="text"
                    name="authorized_by"
                    value="<?= htmlspecialchars(
                        (string)($srt['authorized_by'] ?? '')
                    ) ?>"
                    required
                  >
                </div>


                <div class="srt-field">
                  <label>Authorized At</label>

                  <input
                    type="datetime-local"
                    name="authorized_at"
                    value="<?=
                      !empty($srt['authorized_at'])
                        ? htmlspecialchars(
                            date(
                                'Y-m-d\TH:i',
                                strtotime($srt['authorized_at'])
                            )
                        )
                        : ''
                    ?>"
                    required
                  >
                </div>

              </div>


              <div class="srt-actions">

                <button class="btn" type="submit">
                  Update SRT
                </button>

                <?php if (!empty($srt['srt_id'])): ?>

                  <a
                    href="/srt/detail/<?= rawurlencode(
                        (string)$srt['srt_id']
                    ) ?>"
                    target="_blank"
                    rel="noopener"
                  >
                    View Public Record
                  </a>

                <?php endif; ?>

              </div>

            </form>

          </div>

        <?php endforeach; ?>

      </div>

    <?php else: ?>

      <p class="muted">
        No Security Research Targets have been recorded.
      </p>

    <?php endif; ?>

  </section>

</div>

<?php require_once APPROOT . '/views/inc/foot.php'; ?>