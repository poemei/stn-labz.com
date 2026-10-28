<?php require_once APPROOT . '/views/inc/head.php'; ?>
<dic class="conatainer">
<style>
.market-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.market-card {
    flex: 0 0 calc(33.333% - 16px);
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 12px;
    background: #fff;
}

.market-card img {
    width: 100%;
    height: auto;
    margin-bottom: 10px;
    border-radius: 4px;
}

.market-card h4 {
    margin: 0 0 6px 0;
}

.market-card small {
    color: #666;
}

.market-card form {
    margin-top: 10px;
}

.market-card input,
.market-card textarea,
.market-card select {
    width: 100%;
    margin-bottom: 6px;
}
</style>

<h2>Marketplace Admin</h2>

<div>
    <strong>Orders:</strong> <?= $stats['total_orders'] ?? 0 ?><br>
    <strong>Revenue:</strong> $<?= number_format(($stats['total_revenue'] ?? 0) / 100, 2) ?>
</div>

<hr>

<h3>Stripe Configuration</h3>

<form method="post" action="/market/save_config">

    <label><b>Public Key</b>:</label>
    <input type="text" name="stripe_public"
        value="<?= htmlspecialchars($data['config']['stripe_public']) ?>">
    <br>
    <label><b>Secret Key</b>:</label>
    <input type="text" name="stripe_secret"
        value="<?= htmlspecialchars($data['config']['stripe_secret']) ?>">
    <br>
    <label><b>Webhook Key</b>:</label>
    <input type="text" name="stripe_secret"
        value="<?= htmlspecialchars($data['config']['webhook_secret']) ?>">
    <br>
    <label><b>Currency<b>:</label>
    <input type="text" name="currency"
        value="<?= htmlspecialchars($data['config']['currency']) ?>">

    <button type="submit">Save</button>

</form>
<hr>
<h3>Categories</h3>

<form method="post" action="/market/add_category">
    <input type="text" name="name" placeholder="Category name">
    <button>Add</button>
</form>

<ul>
<?php foreach ($categories as $c): ?>
    <li>
        <?= htmlspecialchars($c['name']) ?>
        <a href="/market/delete_category/<?= $c['id'] ?>">Delete</a>
    </li>
<?php endforeach; ?>
</ul>

<hr>

<h3>Add Product</h3>

<form method="post" action="/market/add_product" enctype="multipart/form-data">
    <input name="title" placeholder="Title"><br>
    <input name="file_name" placeholder="announcements-mvc-1.3.0.zip"><br>
    <input name="price" placeholder="Price"><br>

    <select name="category">
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
    </select><br>

    <input name="image" placeholder="/assets/market/image.png"><br>
    <input type="file" name="image"><br>

    <textarea name="description" placeholder="Description"></textarea><br>

    <label>
        Certified
        <input type="checkbox" name="certified" value="1">
    </label><br>

    <button>Add Product</button>
</form>

<hr>

<h3>Products</h3>

<div class="market-grid">
<?php foreach ($products as $p): ?>
    <div class="market-card">

        <?php if (!empty($p['image'])): ?>
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="">
        <?php endif; ?>

        <h4><?= htmlspecialchars($p['title']) ?></h4>
        <small><?= htmlspecialchars($p['category_name']) ?></small>

        <p>$<?= number_format($p['price'], 2) ?></p>

        <a href="/market/delete_product/<?= $p['id'] ?>">Delete</a>

        <form method="post" action="/market/edit_product" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">

            <input name="title" value="<?= htmlspecialchars($p['title']) ?>">
            <input name="file_name" value="<?= htmlspecialchars($p['file_name']) ?>">
            <input name="price" value="<?= htmlspecialchars($p['price']) ?>">

            <select name="category">
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($c['id'] == $p['category']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input name="image" value="<?= htmlspecialchars($p['image']) ?>">
            <input type="file" name="image">

            <textarea name="description"><?= htmlspecialchars($p['description']) ?></textarea>

            <label>
                Certified
                <input type="checkbox" name="certified" value="1" <?= !empty($p['certified']) ? 'checked' : '' ?>>
            </label>

            <button>Update</button>
        </form>

    </div>
<?php endforeach; ?>
</div>
</div>
<?php require_once APPROOT . '/views/inc/foot.php'; ?>
