<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../data/products.php';
$category = $_GET['category'] ?? null;
$q = $_GET['q'] ?? null;
$vendorId = $_GET['vendor'] ?? null;
$items = get_products(['category'=>$category,'q'=>$q,'vendor_id'=>$vendorId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Products – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <h1 class="h4 mb-3">Products <?php if($category) echo '– '.htmlspecialchars($category); ?></h1>
  <div class="row g-3">
    <?php foreach($items as $p): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card product-card h-100">
          <img src="<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['name']); ?>">
          <div class="card-body d-flex flex-column">
            <h2 class="h6 card-title mb-1"><?php echo htmlspecialchars($p['name']); ?></h2>
            <div class="small text-muted mb-2"><?php echo htmlspecialchars($p['category']); ?> • ⭐ <?php echo number_format($p['rating'],1); ?></div>
            <div class="mt-auto d-flex justify-content-between align-items-center">
              <div class="price"><?php echo format_price_cents($p['price']); ?></div>
              <a class="btn btn-sm btn-primary" href="<?php echo build_path('/customer/product.php?id=' . urlencode($p['id'])); ?>">View</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if(count($items)===0): ?>
      <div class="col-12"><div class="alert alert-info">No products found.</div></div>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


