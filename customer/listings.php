<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/models.php';

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$q          = isset($_GET['q']) ? trim($_GET['q']) : null;
$vendorId   = isset($_GET['vendor']) ? (int)$_GET['vendor'] : null;

$productModel = new Product();

// Fetch products from database so newly added vendor products appear at the top
if ($q) {
    $items = $productModel->searchProducts($q);
} elseif ($categoryId) {
    $items = $productModel->getProductsByCategory($categoryId);
} elseif ($vendorId) {
    $items = $productModel->getProductsByVendor($vendorId);
} else {
    $items = $productModel->getActiveProducts();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('Products – SwiftMart'); ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container py-4">
        <h1 class="h4 mb-3">Products</h1>
        <div class="row g-3">
            <?php foreach ($items as $p): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card h-100">
                    <div class="card-img-top-wrapper">
                        <?php if (!empty($p['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($p['image_url']); ?>" class="card-img-top"
                            alt="<?php echo htmlspecialchars($p['name']); ?>" loading="lazy">
                        <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height:100%;">
                            <span class="text-muted small">No image</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 card-title mb-1"><?php echo htmlspecialchars($p['name']); ?></h2>
                        <div class="small text-muted mb-2">
                            <?php echo htmlspecialchars($p['category_name'] ?? ''); ?>
                            <?php if (!empty($p['rating'])): ?>
                            • ⭐ <?php echo number_format((float)$p['rating'], 1); ?>
                            <?php endif; ?>
                        </div>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div class="price"><?php echo format_price((float)$p['price']); ?></div>
                            <a class="btn btn-sm btn-primary"
                                href="<?php echo build_path('/customer/product.php?id=' . urlencode($p['id'])); ?>">View</a>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
            <div class="col-12">
                <div class="alert alert-info">No products found.</div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>