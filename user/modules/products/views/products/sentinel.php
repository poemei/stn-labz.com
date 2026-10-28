<?php require APPROOT . '/views/inc/head.php'; ?>

<div class="container">
  <section>
  <div class="row">
    <h1><?= htmlspecialchars($data['title']) ?></h1>
    <p><em><?= htmlspecialchars($data['sub_title']) ?></em>.</p>
    <hr>
  </div>
  <div class="row">
    <h2>Web / MVC</h2>
    <p>Autonomous threat intake and pattern analysis across application logs, system signals, and user activity.<br>
      Built for rapid denial, forensic traceability, and deterministic policy enforcement within controlled environments.
    </p>
	<p><a class="btn btn-outline-secondary" href="/market/detail/Sentinel-MVC" role="button">Get Sentinel MVC</a></p>
  </div>
  <div class="row">
   <h2>Systems Daemon</h2>
   <p>Sentinel is a lightweight, deterministic, host-based intrusion prevention and policy enforcement daemon for controlled Linux systems. It operates without external dependencies, enforcing expected behavior at the file and execution level for mission-assurance use cases.
   </p>
   <p>Read the <a href="/docs/whitepaper.pdf">White Paper</a> for this project<br>
   <a class="btn btn-outline-secondary" href="/market/detail/Sentinel_Daemon" role="button">Get Sentinel Daemon</a></p>
    <ul>
      <li>Host-based intrusion prevention (file + execution enforcement)</li>
      <li>Deterministic policy control (allow / block / trust)</li>
      <li>Real-time detection and containment of unauthorized activity</li>
      <li>Local forensic logging with persistent state tracking</li>
      <li>Offline-capable operation (no external dependency required)</li>
      <li>Modular deployment: Web, IPS, and HIPS implementations</li>
      <li>API-enabled web intelligence integration</li>
    </ul>
  </div>
  <div class="row">
    <h2>How It Works</h2>
    <p>
    Sentinel operates as a deterministic enforcement layer at the host level. It continuously evaluates system state against defined policy and takes immediate action when deviations occur.
    </p>
    <h3>Operational Flow</h3>
    <pre>
    [System Activity]
    ↓
(File Created / Modified OR Process Execution)
    ↓
[Sentinel Detection Layer]
    ↓
[Policy Engine]
  - allow
  - block
  - trust
    ↓
[Decision]
    ↓
[Action Engine]
  - quarantine file
  - log event
  - terminate process (optional)
    ↓
[Persistent State + Audit Log]
    </pre>
  </div>
  <div class="row">
    <p>Sentinel does not rely on external threat intelligence or signature updates. Instead, it enforces expected system behavior, ensuring that only authorized operations are permitted within the environment.</p>
    <ul>
      <li>All actions are locally enforced and logged</li>
      <li>No dependency on external connectivity or cloud services</li>
      <li>Behavior-driven enforcement rather than signature-based detection</li>
      <li>Designed for predictable, controlled system environments</li>
    </ul>
  </div>
  <hr>
  <div class="row">
  <p><em>Sentinel MVC Implementation Data</em></p>
    <?php
      require __DIR__ . '/widget.php';

      echo widget::render_sentinel_intel(
        'https://api.stn-labz.com/v1/intel',
        [
          'cache_ttl' => 30,
          'timeout'   => 3,
        ]
      );
    ?>
  </div>
  </hr>
  <p><a href="/products">All Products</a></p>
</section>
</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>
