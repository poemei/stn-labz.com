<?php require APPROOT . '/views/inc/head.php'; ?>

<div class="container mt-4">
    <h3>Payment Successful</h3>
    <p>Your payment was received.</p>

    <?php if (!empty($product_id)): ?>
        <a href="/market/download/<?= htmlspecialchars($product_id) ?>" class="btn btn-success">
            Download
        </a>
    <?php else: ?>
        <p>No product data</p>
    <?php endif; ?>
</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>
