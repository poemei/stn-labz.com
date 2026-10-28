</div>
<div class="container">
<footer class="foot-row" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-top: 1px solid #333; font-size: 0.9rem;">
    <div style="flex: 1; text-align: left;">
        <p style="margin-left: 20px;">© <?= date('Y'); ?>
            <?= htmlspecialchars(
                $SITE['copyright_name'] ?? 'Chaos MVC',
                ENT_QUOTES,
                'UTF-8'
            ); ?></p>
    </div>
    
    <div style="flex: 1; text-align: center">
      <small>
      <strong>Responsible Disclosure:</strong>
      Report security issues responsibly via our
      <a href="/security.txt">security.txt</a> file.  
      See our <a href="/security">Security Policy</a> and encrypt messages using our
      <a href="/security/">PGP OR RSA keys</a>.
	  <br>
	  <a href="/rss" class="stn-footer-icons">
		<img src="<?= theme::assetUrl('img/rss.png'); ?>"alt="<?= $SITE['name'];?> RSS Feed" class="stn-footer-rss-icon" aria-hidden="true">
	  </a>
	  <a href="/ror.xml" class="stn-footer-icons">
		<img src="<?= theme::assetUrl('img/ror.png'); ?>"alt="<?= $SITE['name'];?> RoR Feed" class="stn-footer-ror-icon" aria-hidden="true">
	  </a>
	  <a href="/announcements" class="stn-footer-icons">
		<img src="<?= theme::assetUrl('img/announcements.png'); ?>"alt="<?= $SITE['name'];?> Announcements" class="stn-footer-announcements-icon" aria-hidden="true">
	  </a>
	  <a href="/sitemap.xml" class="stn-footer-icons">
		<img src="<?= theme::assetUrl('img/sitemap.png'); ?>"alt="<?= $SITE['name'];?> Sitemap" class="stn-footer-sitemap-icon" aria-hidden="true">
	  </a>
	  
    </small>
    </div>


    <div style="flex: 1; text-align: right;">
	    <a href="/contact" style="text-decoration: none; color: inherit; margin-left: 10px;">Contact</a>
	    <a href="/legal/privacy" style="text-decoration: none; color: inherit; margin-left: 10px;">Privacy</a>
        <a href="/legal/terms" style="text-decoration: none; color: inherit; margin-left: 10px;">Terms</a>
        <a href="/security" style="text-decoration: none; color: inherit; margin-left: 10px; margin-right: 10px;">Security</a>
    </div>

<!-- Close the container -->
</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="<?= htmlspecialchars(theme::assetUrl('js/site.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
