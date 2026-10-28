<?php require APPROOT . '/views/inc/head.php'; ?>
<p><small><a href="/">Home</a> >> <strong>PGP Key</strong></small></p>
<h1>STN-Labz Security: PGP</h1>
<div>
<h2><?= htmlspecialchars($data['title']); ?></h2>
  <p>
    Use the following pgp public key to encrypt all vulnerability reports and sensitive correspondence.
  </p>
  <hr>
  <p>
    <strong>office@stn-labz.com</strong>
  </p>
  <p>
    <pre>
		<?= htmlspecialchars($data['content']); ?>
    </pre>
  </p>
</div>
<?php require APPROOT . '/views/inc/foot.php'; ?>