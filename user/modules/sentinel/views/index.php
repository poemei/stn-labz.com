<?php
declare(strict_types=1);

// path: /user/modules/sentinel/views/index.php
if (!theme::render('head', get_defined_vars())) {
    require APPROOT . '/views/inc/head.php';
}
?>
<div class="container py-4">
  <h1>Sentinel MVC</h1>
    <p>
	  Sentinel Currently has no public interface
	</p>
</div>
<?php
if (!theme::render('foot', get_defined_vars())) {
    require APPROOT . '/views/inc/foot.php';
}
