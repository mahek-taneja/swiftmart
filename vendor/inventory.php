<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../data/products.php';
require_vendor();
$mine = array_values(array_filter($PRODUCTS, fn($p)=> $p['vendor_id']===current_vendor_id()));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Inventory – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Inventory Management</h1>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/vendor/dashboard.php'); ?>">Back</a>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Name</th><th>Price</th><th>Stock</th><th></th></tr></thead>
      <tbody>
        <?php foreach($mine as $p): ?>
          <tr>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo format_price_cents($p['price']); ?></td>
            <td><input class="form-control form-control-sm" style="max-width:120px" value="<?php echo (int)$p['stock']; ?>" disabled></td>
            <td><button class="btn btn-sm btn-outline-secondary" disabled>Save</button></td>
          </tr>
        <?php endforeach; ?>
        <?php if(count($mine)===0): ?>
          <tr><td colspan="4" class="text-muted">No products to manage.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


