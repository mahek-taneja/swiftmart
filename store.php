<?php
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/data/stores.php';
require_once __DIR__ . '/data/products.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$store = null;
foreach ($STORES as $s) {
    if ($s['id'] === $id) { $store = $s; break; }
}
if (!$store) {
    http_response_code(404);
}

$products = $store ? get_products(['vendor_id' => $store['vendor_id']]) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php render_head($store ? ($store['name'] . ' – swiftmart') : 'Store not found – swiftmart'); ?>
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="container py-4">
    <?php if(!$store): ?>
        <div class="alert alert-danger">Store not found.</div>
    <?php else: ?>
        <div class="row g-4 align-items-center mb-3">
            <div class="col-auto">
                <img src="<?php echo build_path($store['image']); ?>" alt="<?php echo htmlspecialchars($store['name']); ?>" style="width:72px;height:72px;object-fit:contain;background:#f1f5f9;border-radius:12px;">
            </div>
            <div class="col">
                <h1 class="h4 mb-1"><?php echo htmlspecialchars($store['name']); ?></h1>
                <div class="text-muted small"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($store['location']); ?> · <i class="bi bi-star-fill"></i> <?php echo number_format($store['rating'],1); ?></div>
                <p class="mb-0 mt-2 text-muted"><?php echo htmlspecialchars($store['description']); ?></p>
            </div>
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($store['categories'] as $cat): ?>
                        <span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars($cat); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <h2 class="h5 mb-3">Products</h2>
        <div class="row g-3">
            <?php foreach ($products as $p): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card h-100">
                    <img src="<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['name']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h3 class="h6 card-title mb-1"><?php echo htmlspecialchars($p['name']); ?></h3>
                        <div class="small text-muted mb-2"><?php echo htmlspecialchars($p['category']); ?> • ⭐ <?php echo number_format($p['rating'],1); ?></div>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div class="price"><?php echo format_price_cents($p['price']); ?></div>
                            <a class="btn btn-sm btn-primary" href="<?php echo build_path('/customer/product.php?id=' . urlencode($p['id'])); ?>">View</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (count($products)===0): ?>
                <div class="col-12"><div class="alert alert-info">No products found for this store.</div></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>


