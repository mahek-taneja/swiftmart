<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/models.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productModel = new Product();
$product = $id ? $productModel->find($id) : null;

if (!$product) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head(($product ? $product['name'] : 'Product Not Found') . ' – SwiftMart'); ?>
</head>

<body>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container py-4 product-page">
        <?php if (!$product): ?>

        <div class="alert alert-danger">Product not found.</div>

        <?php else: ?>

        <?php
// ✅ SAFE STOCK HANDLING (NO WARNINGS)
$stock = isset($product['stock_quantity']) ? (int)$product['stock_quantity'] : null;
?>

        <div class="row g-4">
            <div class="col-12 col-md-6">
                <?php if (!empty($product['image_url'])): ?>
                <img loading="lazy" class="img-fluid rounded product-detail-image" src="<?= htmlspecialchars($product['image_url']); ?>"
                    alt="<?= htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:320px;">
                    <span class="text-muted">No image available</span>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-12 col-md-6">
                <h1 class="h3 mb-2"><?= htmlspecialchars($product['name']); ?></h1>

                <div class="mb-2 text-muted">
                    Category: <?= htmlspecialchars($product['category_id']); ?>
                    <?php if (!empty($product['rating'])): ?>
                    • ⭐ <?= number_format((float)$product['rating'], 1); ?>
                    <?php endif; ?>
                </div>

                <div class="display-6 price mb-3">
                    <?= format_price((float)$product['price']); ?>
                </div>

                <div class="mb-3">
                    In stock:
                    <strong>
                        <?= $stock !== null ? $stock : 'Available'; ?>
                    </strong>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <input type="number" class="form-control" id="qty" value="1" min="1" style="max-width:120px">

                    <button class="btn btn-primary" id="addBtn">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
    const PRODUCT = <?= json_encode($product ?? null); ?>;

    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('addBtn');
        if (!btn || !PRODUCT) return;

        btn.addEventListener('click', () => {
            const qty = parseInt(document.getElementById('qty').value || '1', 10);

            window.DeliverXCart.addToCart({
                id: PRODUCT.id,
                name: PRODUCT.name,
                price: PRODUCT.price, // ✅ REAL ₹ PRICE
                vendor_id: PRODUCT.vendor_id
            }, qty);

            btn.textContent = 'Added!';
            setTimeout(() => btn.textContent = 'Add to Cart', 1000);
        });
    });
    </script>

</body>

</html>