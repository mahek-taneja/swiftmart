<?php
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/data/products.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php render_head('DeliverX – Home'); ?>
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="container py-4">
    <div class="p-4 p-md-5 mb-4 text-white rounded bg-primary">
        <div class="col-md-8 px-0">
            <h1 class="display-5 fw-bold">DeliverX</h1>
            <p class="lead my-3">Your multi-sector delivery platform for groceries, retail, and more.</p>
            <a class="btn btn-light btn-lg" href="<?php echo build_path('/customer/listings.php'); ?>">Shop Now</a>
        </div>
    </div>

    <section class="my-5">
        <h2 class="h4 mb-3">Categories</h2>
        <div class="row g-3">
            <?php
            $categories = ['Groceries', 'Electronics', 'Fashion', 'Home', 'Health'];
            foreach ($categories as $category): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a class="text-decoration-none" href="<?php echo build_path('/customer/listings.php?category=') . urlencode($category); ?>">
                        <div class="card h-100 category-card">
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <span class="fw-semibold"><?php echo htmlspecialchars($category); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="my-5">
        <h2 class="h4 mb-3">Featured Products</h2>
        <div class="row g-3">
            <?php $featured = array_slice($PRODUCTS, 0, 4); foreach ($featured as $p): ?>
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
        </div>
        <div class="mt-3">
            <a class="btn btn-outline-primary" href="<?php echo build_path('/customer/listings.php'); ?>">View All Products</a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>


