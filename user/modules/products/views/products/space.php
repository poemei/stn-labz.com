<?php require APPROOT . '/views/inc/head.php'; ?>

<div class="container">
  <section>
  <div class="row">
    <h1><?= htmlspecialchars($data['title']) ?></h1>
    <p><em><?= htmlspecialchars($data['sub_title']) ?></em>.</p>
    <hr>
  </div>
  <div class="row">
    <h2>Implementation</h2>
    <p>Sentinel is a lightweight, deterministic, host-based intrusion prevention and policy enforcement daemon for controlled Linux environments. It is designed for offline operation, minimal dependencies, and mission-assurance scenarios where predictability and control are critical.
    <ul>
      <li>Deterministic policy enforcement at the host level</li>
      <li>File and execution-based intrusion prevention</li>
      <li>Local evidence capture and audit logging</li>
      <li>Autonomous operation without external dependencies</li>
      <li>Designed for edge, lab, and mission-support systems</li>
      <li>Integration with the STN-Labz ecosystem (Digit / Sentinel / Threat API)</li>
    </ul>
    </p>
    <p>See the <a href="/docs/whitepaper.pdf">Whitepaper</a> for this project.</p>
  </div>
  <hr>
  <div class="row">
    <h3>Possibilities</h2>
      <ul>
      <li>Nasa</li>
      <li>SpaceX</li>
      <li>Blue Origin</li>
      <li>Lockheed Martin</li>
    </ul>
    <hr>
    <p><a href="/products">All Products</a></p>
  </div>
  <div class="row">
    <h3>Sentinel S1 Funding Round One</h3>
    <p>The Sentinel Daemon S1 Mission, is currently seeking funding for its round one mission, please see our <a href="https://gofund.me/7981a46a5" target="_blank">Funding Campaign</a></p>
    <p>Every little bit helps us get closer to our goal to put a cube satellite into space to test Sentinel Daemon in a very volitile environment.</p>
  </div>
</section>
</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>
