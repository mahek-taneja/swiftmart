<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../data/products.php';
$id = $_GET['id'] ?? '';
$product = $id ? get_product($id) : null;
if (!$product) { http_response_code(404); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head(($product? $product['name'] : 'Product Not Found') . ' – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <?php if(!$product): ?>
    <div class="alert alert-danger">Product not found.</div>
  <?php else: ?>
    <div class="row g-4">
      <div class="col-12 col-md-6">
        <img class="img-fluid rounded" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
      </div>
      <div class="col-12 col-md-6">
        <h1 class="h3 mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="mb-2 text-muted">Category: <?php echo htmlspecialchars($product['category']); ?> • ⭐ <?php echo number_format($product['rating'],1); ?></div>
        <div class="display-6 price mb-3"><?php echo format_price_cents($product['price']); ?></div>
        <div class="mb-3">In stock: <?php echo (int)$product['stock']; ?></div>
        <div class="d-flex align-items-center gap-2">
          <input type="number" class="form-control" id="qty" value="1" min="1" style="max-width:120px">
          <button class="btn btn-primary" id="addBtn">Add to Cart</button>
        </div>
      </div>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
  const PRODUCT = <?php echo json_encode($product ?? null); ?>;
  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('addBtn');
    if(!btn || !PRODUCT) return;
    btn.addEventListener('click', () => {
      const qty = parseInt(document.getElementById('qty').value || '1', 10);
      window.DeliverXCart.addToCart({ id: PRODUCT.id, name: PRODUCT.name, price: PRODUCT.price }, qty);
      btn.textContent = 'Added!';
      setTimeout(()=> btn.textContent = 'Add to Cart', 1000);
    });
  });
</script>
</body>
</html>

