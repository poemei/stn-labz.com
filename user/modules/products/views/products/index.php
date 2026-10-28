<?php 
/*
 * Products
 * app/views/products/index.php
 */

require APPROOT . '/views/inc/head.php';
?>

<div class="container">

  <!-- Page Header -->
  <section>
    <h1><?= htmlspecialchars($data['title']) ?></h1>

    <p class="hero-lead">
      <?= htmlspecialchars($data['sub_title']) ?>
    </p>
  </section>


  <!-- Product Grid -->
  <div class="grid">

    <div class="card">
      <h3>Digit</h3>

      <p>
        Autonomous Non LLM driven AI with built in Development and Intelligence gathering 
      </p>

      <a class="btn" href="/products/digit<div class="card">
      <h3>Digit</h3>

      <p>
        Security engineering focused on resilient systems, integrity,
        monitoring, and trusted operation.
      </p>

      <a class="btn" href="/products/digit">
        Read More
      </a>
  
    <div class="card">
      <h3>Module Builder</h3>

      <p>
        The Module Builder for the ChAoS MVC
      </p>

      <a class="btn" href="/products/module_builder">
        Read More
      </a>
    </div>

    <div class="card">
      <h3>DCT_WIN</h3>

      <p>
        The STN-LABZ Developers Compression Tool for Windows.
      </p>

      <a class="btn" href="/products/dct_win">
        Read More
      </a>
    </div>


    <div class="card">
      <h3>Space</h3>

      <p>
        Earth, space, and interstellar systems, research, and engineering
        implementations.
      </p>

      <a class="btn" href="/products/space">
        Read More
      </a>
    </div>


    <div class="card">
      <h3>Sentinel</h3>

      <p>
        Security engineering focused on resilient systems, integrity,
        monitoring, and trusted operation.
      </p>

      <a class="btn" href="/products/sentinel">
        Read More
      </a>
    </div>

  </div>

</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>
