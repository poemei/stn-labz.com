<?php require APPROOT . '/views/inc/head.php'; ?>

<div class="container mt-4">
  <section>
    <?php 
        // Safe current category handling
        $current = $data['current_cat'] ?? '';
        $current = is_array($current) ? ($current[0] ?? '') : $current;
    ?>

    <div class="d-flex flex-row flex-wrap justify-content-center border-bottom pb-3 mb-4">
        <a href="/market" class="btn <?= empty($current) ? 'btn-secondary' : 'btn-outline-secondary' ?> m-1">All</a>
        
        <?php foreach ($data['categories'] as $topic): 
            $name = $topic['name'] ?? 'Module';
        ?>
            <a href="/market/index/<?= htmlspecialchars($name) ?>" 
               class="btn <?= ($current == $name) ? 'btn-primary' : 'btn-outline-primary' ?> m-1 rounded-pill">
               <?= htmlspecialchars($name) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <?php foreach ($data['products'] as $item): ?>
            <div class="col-md-6 mb-4">
    <div class="card border h-100">
        <div class="row g-0 align-items-center">
            
            <!-- Image (left) -->
            <div class="col-4">
                <?php if (!empty($item['image'])): ?>
                    <img src="<?= URLROOT ?>/assets/market/<?= htmlspecialchars($item['image']) ?>" 
                         class="img-fluid rounded-start" 
                         alt="<?= htmlspecialchars($item['title'] ?? '') ?>">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                        No Image
                    </div>
                <?php endif; ?>
            </div>

            <!-- Content (right) -->
            <div class="col-8">
                <div class="card-body">
                    <h5 class="card-title mb-1">
                        <?= htmlspecialchars($item['title'] ?? '') ?>
                    </h5>

                    <p class="card-text small mb-2">
                        <?= htmlspecialchars($item['description'] ?? '') ?>
                    </p>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">
                            $<?= number_format((float)($item['price'] ?? 0), 2) ?>
                        </span>
                        <?php if (!empty($item['certified'])): ?>
                            <span class="badge bg-success">STN Certified</span>
                        <?php endif; ?>

                        <a href="/market/detail/<?= urlencode($item['title'] ?? '') ?>" 
                           class="btn btn-sm btn-outline-primary">
                            View Info
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
        <?php endforeach; ?>
    </div>
  </section>
</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>
