<?php
require_once __DIR__ . '/../includes/head.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Checkout – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <h1 class="h4 mb-3">Checkout</h1>
  <div class="row g-4">
    <div class="col-12 col-lg-7">
      <form id="checkout-form" class="card p-3">
        <h2 class="h5">Shipping Details</h2>
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Full Name</label>
            <input class="form-control" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Phone</label>
            <input class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label">Address</label>
            <input class="form-control" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">City</label>
            <input class="form-control" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Postal Code</label>
            <input class="form-control" required>
          </div>
        </div>
        <hr>
        <h2 class="h5">Payment</h2>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Card Number</label>
            <input class="form-control" placeholder="4111 1111 1111 1111" required>
          </div>
          <div class="col-6">
            <label class="form-label">Expiry</label>
            <input class="form-control" placeholder="MM/YY" required>
          </div>
          <div class="col-6">
            <label class="form-label">CVV</label>
            <input class="form-control" placeholder="123" required>
          </div>
        </div>
        <div class="mt-3 text-end">
          <button class="btn btn-primary" type="submit">Place Order</button>
        </div>
      </form>
      <div id="success" class="alert alert-success mt-3 d-none">Order placed! Your order ID is <span id="oid"></span>. <a href="<?php echo build_path('/customer/tracking.php'); ?>">Track it here</a>.</div>
    </div>
    <div class="col-12 col-lg-5">
      <div class="card p-3">
        <h2 class="h5">Order Summary</h2>
        <div id="summary"></div>
        <hr>
        <div class="d-flex justify-content-between">
          <div>Total</div>
          <div class="fw-semibold" id="total">$0.00</div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
  function renderSummary(){
    const items = window.DeliverXCart.readCart();
    let html = '';
    let total = 0;
    for(const it of items){
      const subtotal = it.price * it.qty;
      total += subtotal;
      html += `<div class="d-flex justify-content-between"><div>${it.name} × ${it.qty}</div><div>${window.DeliverX.formatPrice(subtotal)}</div></div>`;
    }
    document.getElementById('summary').innerHTML = html || '<div class="text-muted">Your cart is empty.</div>';
    document.getElementById('total').textContent = window.DeliverX.formatPrice(total);
  }
  document.addEventListener('DOMContentLoaded', ()=>{
    renderSummary();
    document.getElementById('checkout-form').addEventListener('submit', (e)=>{
      e.preventDefault();
      const oid = 'o' + Math.floor(1000 + Math.random()*9000);
      window.DeliverXCart.writeCart([]);
      renderSummary();
      document.getElementById('oid').textContent = oid;
      document.getElementById('success').classList.remove('d-none');
      updateCartCountBadge();
    });
  });
</script>

</body>
</html>

