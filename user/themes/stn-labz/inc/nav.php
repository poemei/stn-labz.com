<header class="nav">
  <div class="container nav-row">
    <a class="brand" href="/">
    <img src="<?= htmlspecialchars(theme::assetUrl('img/stn-labz-32x32.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="STN-Labz logo" />
        <span>STN-Labz</span>
      </a>
      <nav class="nav-links">
        <a href="/">Home</a>
		<a href="/intelligence">Intel</a>
        <a href="/news">News</a>
        <a href="/company/page/strategic">Partner with Us</a>
		<a href="/certification">Certification</a>
        <a href="/products">Products</a>
		<a href="/letters">Subscribe</a>
		<a href="/srt">SRT</a>

      <?php if (isset($_SESSION['user_id'])): ?>
          <?php /* [AI:GPT-6 | 2026-10-29 04:17:13 UTC] */ ?>
          <?php if (admin_access::hasAdminNavigation()): ?>
               <a href="/admin" class="btn btn-sm btn-outline-primary">Admin</a>
          <?php endif; ?>
          <?php /* [End AI:GPT-6] */ ?>
          
          <?php
          if (empty($_SESSION['csrf_token'])) {
              $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
          }
          ?>
          <form action="/logout" method="POST" style="display: inline; margin: 0;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
              <button type="submit" style="background: none; border: 0; padding: 0; color: #666; text-decoration: none; cursor: pointer;">Logout</button>
          </form>
      <?php else: ?>
          <a href="/login">Login</a>
      <?php endif; ?>
      </nav>
  </div>
</header>
