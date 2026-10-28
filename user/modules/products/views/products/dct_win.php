<?php
/**
 * STN-LABZ
 * Product: DCT_WIN
 * Developers Compression Tool for Windows
 */

require APPROOT . '/views/inc/head.php';
?>

<div class="container">

  <!-- Product Hero -->
  <section>
    <h1>DCT_WIN</h1>

    <p class="lead">
      The STN-LABZ Developers Compression Tool for Windows
    </p>

    <p>
      DCT_WIN is a command-line compression utility designed to provide
      developers with a simple and predictable way to create compressed
      project archives on Windows.
    </p>

    <p>
      <strong>Release Status: Release Candidate</strong>
    </p>
  </section>


  <!-- Release Candidate Notice -->
  <section>
    <h2>Release Candidate</h2>

    <p>
      DCT_WIN is currently a release candidate. The software has reached a
      functional stage suitable for general evaluation and testing, but has
      not yet been designated as a final production release.
    </p>

    <p>
      Users are encouraged to validate generated archives and report any
      unexpected behavior encountered while using the release candidate.
    </p>
  </section>


  <!-- What It Does -->
  <section>
    <h2>What DCT_WIN Does</h2>

    <p>
      DCT_WIN provides archive compression functionality directly from the
      Windows command line. It is designed for development workflows where
      creating a compressed copy of a project should be quick, repeatable,
      and uncomplicated.
    </p>

    <p>
      The Windows implementation performs its compression workflow without
      requiring users to separately install and manage the external
      compression utilities previously required by DCT.
    </p>
  </section>


  <!-- Installation -->
  <section>
    <h2>Installation</h2>

    <p>
      DCT_WIN is distributed using a Windows installer.
    </p>

    <p>
      Run the installer and complete the installation process. Once installed,
      the <code>dct</code> command is available system-wide and can be invoked
      from Command Prompt, PowerShell, or another Windows terminal.
    </p>

    <p>
      There is no need to copy the DCT executable into individual project
      directories.
    </p>
  </section>


  <!-- Usage -->
  <section>
    <h2>Using DCT_WIN</h2>

    <p>
      After installation, navigate to the location containing the project you
      want to compress and invoke DCT with the <code>-src</code> option.
    </p>

    <p>
      Example:
    </p>

    <pre><code>dct -src &lt;project&gt;</code></pre>

    <p>
      Replace <code>&lt;project&gt;</code> with the project or source directory
      to be processed.
    </p>

    <p>
      For example:
    </p>

    <pre><code>dct -src my-project</code></pre>

    <p>
      DCT processes the specified source and creates the compressed project
      archive using its compression workflow.
    </p>
  </section>


  <!-- Command Requirements -->
  <section>
    <h2>Command Requirements</h2>

    <p>
      DCT requires a source to be supplied using the
      <code>-src</code> option.
    </p>

    <p>
      Running DCT without a source produces an error:
    </p>

    <pre><code>dct
ERR: missing -src</code></pre>

    <p>
      This behavior prevents DCT from guessing which project or directory
      should be processed.
    </p>
  </section>


  <!-- Download -->
  <section>
    <h2>Download DCT_WIN</h2>

    <p>
      The current DCT_WIN release candidate is available for Windows.
    </p>

    <p>
      <a class="btn primary"
         href="/public/packages/DCT-1.0-Setup.exe">
        Download DCT_WIN Release Candidate
      </a>
    </p>

    <p>
      <small>
        This is release candidate software. Users should verify generated
        archives before relying on them for archival, backup, or release
        purposes.
      </small>
    </p>
  </section>


  <!-- Project Information -->
  <section>
    <h2>Project Information</h2>

    <ul>
      <li>
        <strong>Product:</strong>
        DCT_WIN
      </li>

      <li>
        <strong>Product Name:</strong>
        STN-LABZ Developers Compression Tool for Windows
      </li>

      <li>
        <strong>Command:</strong>
        <code>dct</code>
      </li>

      <li>
        <strong>Platform:</strong>
        Windows
      </li>

      <li>
        <strong>Release Stage:</strong>
        Release Candidate
      </li>

      <li>
        <strong>Developer:</strong>
        STN-LABZ
      </li>
    </ul>
  </section>


  <!-- Support -->
  <section>
    <h2>Testing and Feedback</h2>

    <p>
      DCT_WIN is being made available as a release candidate so that it can
      receive broader testing across Windows development environments.
    </p>

    <p>
      If you encounter unexpected behavior, installation problems, or archive
      generation issues, please submit a report so the issue can be evaluated
      before the final release.
    </p>
  </section>

</div>

<?php require APPROOT . '/views/inc/foot.php'; ?>