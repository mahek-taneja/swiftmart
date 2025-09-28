<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../data/products.php';
require_once __DIR__ . '/../data/orders.php';
require_vendor();
$vendorId = current_vendor_id();
$myProducts = array_values(array_filter($PRODUCTS, fn($p)=> $p['vendor_id']===$vendorId));

// Build vendor-centric orders with totals
$vendorOrders = [];
foreach ($ORDERS as $o) {
  $totalCents = 0;
  $itemCount = 0;
  foreach ($o['items'] as $it) {
    $prod = null;
    foreach ($PRODUCTS as $p) { if ($p['id']===$it['id']) { $prod = $p; break; } }
    if ($prod && $prod['vendor_id']===$vendorId) {
      $itemCount += (int)$it['qty'];
      $totalCents += ((int)$prod['price']) * ((int)$it['qty']);
    }
  }
  if ($itemCount > 0) {
    $vendorOrders[] = [
      'id' => $o['id'],
      'status' => $o['status'],
      'eta' => $o['eta'],
      'items' => $itemCount,
      'total' => $totalCents,
    ];
  }
}

$totalProducts = count($myProducts);
$totalOrders = count($vendorOrders);
$totalRevenue = array_sum(array_map(fn($x)=> $x['total'], $vendorOrders));
$lowStock = count(array_filter($myProducts, fn($p)=> (int)$p['stock'] <= 10));
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
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card p-3 h-100">
        <div class="small text-muted">Total Products</div>
        <div class="display-6" id="kpi-products"><?php echo $totalProducts; ?></div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card p-3 h-100">
        <div class="small text-muted">Orders</div>
        <div class="display-6" id="kpi-orders"><?php echo $totalOrders; ?></div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card p-3 h-100">
        <div class="small text-muted">Revenue</div>
        <div class="display-6" id="kpi-revenue"><?php echo format_price_cents($totalRevenue); ?></div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card p-3 h-100">
        <div class="small text-muted">Low Stock (≤10)</div>
        <div class="display-6" id="kpi-lowstock"><?php echo $lowStock; ?></div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-12 col-xl-8">
      <div class="card p-3 h-100">
        <div class="d-flex justify-content-between align-items-center">
          <h2 class="h6 mb-0">Recent Orders</h2>
          <a class="btn btn-sm btn-outline-primary" href="<?php echo build_path('/vendor/orders.php'); ?>">View All</a>
        </div>
        <div class="table-responsive mt-3">
          <table class="table align-middle mb-0">
            <thead><tr><th>Order ID</th><th>Items</th><th>Total</th><th>Status</th><th>ETA</th></tr></thead>
            <tbody>
              <?php $shown = 0; foreach($vendorOrders as $o): if($shown++===5) break; ?>
                <tr>
                  <td><?php echo htmlspecialchars($o['id']); ?></td>
                  <td><?php echo (int)$o['items']; ?></td>
                  <td><?php echo format_price_cents((int)$o['total']); ?></td>
                  <td><span class="badge text-bg-info"><?php echo htmlspecialchars($o['status']); ?></span></td>
                  <td><?php echo htmlspecialchars($o['eta']); ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if(count($vendorOrders)===0): ?>
                <tr><td colspan="5" class="text-muted">No recent orders.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-12 col-xl-4">
      <div class="card p-3 mb-3">
        <h2 class="h6">Inventory Snapshot</h2>
        <ul class="list-group list-group-flush">
          <?php
            $sorted = $myProducts;
            usort($sorted, fn($a,$b)=> (int)$a['stock'] <=> (int)$b['stock']);
            $top = array_slice($sorted, 0, 5);
          ?>
          <?php foreach($top as $p): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="text-truncate" style="max-width:65%"><?php echo htmlspecialchars($p['name']); ?></span>
              <span class="badge <?php echo ((int)$p['stock']<=10)?'text-bg-warning':'text-bg-secondary'; ?>"><?php echo (int)$p['stock']; ?></span>
            </li>
          <?php endforeach; ?>
          <?php if(count($myProducts)===0): ?>
            <li class="list-group-item text-muted">No products yet.</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center">
          <h2 class="h6 mb-0">Quick Actions</h2>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#quickAddModal">Add Product</button>
        </div>
        <div class="mt-3 small text-muted">Use inventory to manage in detail.</div>
      </div>
    </div>
  </div>

  <div class="card p-3 mt-3">
    <div class="small text-muted mb-2">Sales Overview (placeholder)</div>
    <div class="placeholder-glow">
      <div class="placeholder col-12" style="height: 200px;"></div>
    </div>
  </div>

  <div class="mt-4 d-flex flex-wrap gap-2">
    <a class="btn btn-primary" href="<?php echo build_path('/vendor/inventory.php'); ?>">Manage Inventory</a>
    <a class="btn btn-outline-primary" href="<?php echo build_path('/vendor/orders.php'); ?>">Order Management</a>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/vendor/analytics.php'); ?>">Sales Analytics (AI)</a>
  </div>
</main>

<!-- Quick Add Product Modal (stub) -->
<div class="modal fade" id="quickAddModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Product (Quick)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input class="form-control" placeholder="Product name">
        </div>
        <div class="mb-3">
          <label class="form-label">Price (USD)</label>
          <input class="form-control" placeholder="0.00">
        </div>
        <div class="mb-3">
          <label class="form-label">Stock</label>
          <input class="form-control" placeholder="0">
        </div>
        <div class="text-muted small">For full control, use Inventory Management.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="quickAddBtn">Save</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo build_path('/assets/js/vendor-dashboard.js'); ?>" defer></script>
</body>
</html>

