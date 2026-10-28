<?php require APPROOT . '/views/inc/head.php'; 
$featuredAnnouncement = $data['featured_announcement'] ?? false;
?>

<div class="container">

  <!-- Hero -->
  <div class="hero-wrap">
    <div>
      <!-- Existing STN-LABZ logo can go here -->
    </div>

    <div>
      <h1 class="hero-title">STN-LABZ</h1>

      <p class="hero-tag">
        Engineering systems worthy of trust when trust matters most.
      </p>

      <p class="hero-lead">
        Systems Engineering &bull;
        Software Engineering &bull;
        Cyber Security &bull;
        Space Development
      </p>
    </div>
  </div>


  <!-- Engineering Principle -->
  <section>
    <h2>Determinism &ne; Probably</h2>

    <p>
      Engineering trust is not built on assumptions. At STN-LABZ,
      systems are designed for predictable behavior, validated through
      rigorous testing, and trusted only after they consistently
      demonstrate the required behavior.
    </p>
	
    <p>
      <strong>&ldquo;Probably&rdquo; is not an engineering standard.
      Evidence is.</strong>
    </p>
  </section>
  <section>
    <?php if (is_array($featuredAnnouncement)): ?>
	<h2>Latest Announcement</h2>
	<h3>
        <?=
          (string) ($featuredAnnouncement['title'] ?? '');
		?>
     </h3>
	 <p>
		<?= (string) ($featuredAnnouncement['rendered_body'] ?? ''); ?><br>
	 </p>
	 <p>
		<small>
                    Posted:
                    <?=
                        date(
                            'Y.m.d:H:m:s',
                            strtotime(
                                (string) $featuredAnnouncement['created_at']
                            )
                        );
					?>
					UTC
                </small>
	 </p>
	 <?php endif; ?>
  </section>


  <!-- Engineering Disciplines -->
  <div class="grid">

    <div class="card">
      <h3>Systems Engineering</h3>
      <p>
        Engineering systems from requirements through architecture,
        integration, validation, operation, and lifecycle management.
      </p>
    </div>

    <div class="card">
      <h3>Software Engineering</h3>
      <p>
        Developing predictable, maintainable, and auditable software
        with clearly defined behavior and engineering controls.
      </p>
    </div>

    <div class="card">
      <h3>Cyber Security</h3>
      <p>
        Researching and engineering security systems centered on
        evidence, operator authority, containment, and predictable
        behavior.
      </p>
    </div>

    <div class="card">
      <h3>Space Development</h3>
      <p>
        Research and development of resilient systems for spacecraft,
        autonomous operations, communications, and mission-critical
        computing.
      </p>
    </div>

  </div>


  <!-- Certification / Research -->
  <div class="grid">

    <div class="card">
      <h3>Engineering Certification</h3>
      <p>
        Engineering certification and qualification based on
        demonstrated competency, validation, and operational
        responsibility.
      </p>
    </div>

    <div class="card">
      <h3>Research</h3>
      <p>
        Ongoing research into security, autonomous systems,
        software engineering, and space systems.
      </p>
    </div>

  </div>

</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>