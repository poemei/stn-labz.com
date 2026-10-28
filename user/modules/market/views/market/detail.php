<?php require APPROOT . '/views/inc/head.php'; ?>

<div class="container">
<section>
    <h2><?= htmlspecialchars($data['product']['title'] ?? '') ?></h2>

    <?php if (!empty($data['product']['image'])): ?>
        <img src="<?= URLROOT ?>/assets/market/<?= htmlspecialchars($data['product']['image']) ?>" class="img-fluid mb-3">
    <?php endif; ?>

    <p>
        <?= htmlspecialchars($data['product']['description'] ?? '') ?>
    </p>

    <div class="mt-3">
        <strong class="text-warning">
            $<?= number_format((float)($data['product']['price'] ?? 0), 2) ?>
        </strong>
    </div>
    
    <?php if (!empty($data['certified'])): ?>
    <div class="mb-2">
      <span class="badge bg-success">✔ STN Certified</span><br>
      <p>Verified to meet the STN-Labz standards.</p>
    </div>
    <?php endif; ?>
    
    <div id="card-element" class="mb-3"></div>

      <button id="pay-btn" class="btn btn-success">
        Buy Now
     </button>

    <div id="payment-status" class="mt-2 text-muted small"></div>
</section>
<section>
    <div>
        <a href="/market" class="btn btn-secondary">Back</a>
    </div>
    
    <div>
      <h2>STN Certified</h2>
      <p>STN Certified products meet the standards of the STN-Labz Standards of Agnostic, Decentralized, Sovereign Applications..</p>
    </div>
</section>
</div>

<script src="https://js.stripe.com/v3/"></script>

<script>
const stripe = Stripe("<?= htmlspecialchars($data['config']['stripe_public'] ?? '') ?>");

const elements = stripe.elements();
const card = elements.create("card");
card.mount("#card-element");

document.getElementById("pay-btn").addEventListener("click", async () => {

    const statusEl = document.getElementById("payment-status");
    statusEl.innerText = "Processing payment...";

    try {
        const res = await fetch("/payment/intent/<?= $data['product']['id'] ?>");
        const data = await res.json();

        const result = await stripe.confirmCardPayment(data.clientSecret, {
            payment_method: {
                card: card
            }
        });

        if (result.error) {
            statusEl.innerText = result.error.message;
            return;
        }

        // SUCCESS → move forward
        if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
            window.location.href = "/market/success/<?= $data['product']['id'] ?>";
        } else {
            statusEl.innerText = "Payment processing...";
        }

    } catch (e) {
        statusEl.innerText = "Something went wrong. Try again.";
    }
});
</script>

<?php require APPROOT . '/views/inc/foot.php'; ?>
