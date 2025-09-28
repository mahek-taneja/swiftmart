<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../data/products.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Your Cart – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <h1 class="h4 mb-3">Your Cart</h1>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>Product</th><th style="width:140px">Qty</th><th>Price</th><th>Subtotal</th><th></th></tr>
      </thead>
      <tbody id="cart-body"></tbody>
    </table>
  </div>
  <div class="d-flex justify-content-between align-items-center mt-3">
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/customer/listings.php'); ?>">Continue Shopping</a>
    <div class="fs-5">Total: <span class="fw-semibold" id="cart-total">$0.00</span></div>
  </div>
  <div class="mt-3 text-end">
    <a class="btn btn-primary" href="<?php echo build_path('/customer/checkout.php'); ?>">Checkout</a>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
  const PRODUCTS = <?php echo json_encode($PRODUCTS); ?>;
  function findProductById(id){ return PRODUCTS.find(p=> p.id===id); }
  function renderCart(){
    const body = document.getElementById('cart-body');
    const items = window.DeliverXCart.readCart();
    body.innerHTML='';
    let total = 0;
    for(const item of items){
      const p = findProductById(item.id);
      const price = item.price ?? (p? p.price : 0);
      const subtotal = price * item.qty;
      total += subtotal;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${p? p.name : item.name}</td>
        <td><input type="number" class="form-control form-control-sm" value="${item.qty}" min="1" data-id="${item.id}"></td>
        <td>${window.DeliverX.formatPrice(price)}</td>
        <td>${window.DeliverX.formatPrice(subtotal)}</td>
        <td><button class="btn btn-sm btn-outline-danger" data-remove="${item.id}"><i class="bi bi-trash"></i></button></td>
      `;
      body.appendChild(tr);
    }
    document.getElementById('cart-total').textContent = window.DeliverX.formatPrice(total);
    body.addEventListener('change', (e)=>{
      const input = e.target.closest('input[type="number"]');
      if(!input) return;
      const id = input.getAttribute('data-id');
      window.DeliverXCart.updateQty(id, parseInt(input.value||'1',10));
      renderCart();
    }, { once:true });
    body.addEventListener('click', (e)=>{
      const btn = e.target.closest('[data-remove]');
      if(!btn) return;
      window.DeliverXCart.removeFromCart(btn.getAttribute('data-remove'));
      renderCart();
    }, { once:true });
  }
  document.addEventListener('DOMContentLoaded', renderCart);
</script>

</body>
</html>

