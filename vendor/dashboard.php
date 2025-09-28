<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../data/products.php';
require_vendor();
$myProducts = array_values(array_filter($PRODUCTS, fn($p)=> $p['vendor_id']===current_vendor_id()));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Vendor Dashboard – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Vendor Dashboard</h1>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/vendor/logout.php'); ?>">Logout</a>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-4">
      <div class="card p-3 h-100">
        <div class="small text-muted">Total Products</div>
        <div class="display-6"><?php echo count($myProducts); ?></div>
      </div>
    </div>
    <div class="col-12 col-lg-4">
      <div class="card p-3 h-100">
        <div class="small text-muted">Orders (dummy)</div>
        <div class="display-6">12</div>
      </div>
    </div>
    <div class="col-12 col-lg-4">
      <div class="card p-3 h-100">
        <div class="small text-muted">Revenue (dummy)</div>
        <div class="display-6">$2,345.00</div>
      </div>
    </div>
  </div>

  <h2 class="h5 mt-4">Your Products</h2>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th></tr></thead>
      <tbody>
        <?php foreach($myProducts as $p): ?>
          <tr>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo htmlspecialchars($p['category']); ?></td>
            <td><?php echo format_price_cents($p['price']); ?></td>
            <td><?php echo (int)$p['stock']; ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(count($myProducts)===0): ?>
          <tr><td colspan="4" class="text-muted">No products yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-4 d-flex flex-wrap gap-2">
    <a class="btn btn-primary" href="<?php echo build_path('/vendor/inventory.php'); ?>">Manage Inventory</a>
    <a class="btn btn-outline-primary" href="<?php echo build_path('/vendor/orders.php'); ?>">Order Management</a>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/vendor/analytics.php'); ?>">Sales Analytics (AI)</a>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


