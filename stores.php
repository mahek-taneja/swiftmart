<?php
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/data/stores.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('swiftmart – Stores'); ?>
    <style>
    .store-card img {
        width: 100%;
        height: 140px;
        object-fit: cover;
        background: #f1f5f9;
    }

    .store-meta {
        color: #64748b;
        font-size: .9rem;
    }
    </style>
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h4 m-0">Stores</h1>
            <form class="d-none d-md-flex" role="search" action="<?php echo build_path('/stores.php'); ?>" method="get">
                <input class="form-control me-2" type="search" name="q" placeholder="Search stores" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>

        <?php
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $filtered = array_values(array_filter($STORES, function ($s) use ($q) {
            if ($q === '') return true;
            $hay = strtolower($s['name'] . ' ' . $s['location'] . ' ' . $s['description'] . ' ' . implode(' ', $s['categories']));
            return str_contains($hay, strtolower($q));
        }));
        ?>

        <div class="row g-3">
            <?php foreach ($filtered as $store): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 store-card">
                    <img src="<?php echo build_path($store['image']); ?>" alt="<?php echo htmlspecialchars($store['name']); ?>">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between mb-1">
                            <h2 class="h6 card-title mb-0"><?php echo htmlspecialchars($store['name']); ?></h2>
                            <span class="badge text-bg-primary"><i class="bi bi-star-fill"></i> <?php echo number_format($store['rating'], 1); ?></span>
                        </div>
                        <div class="store-meta mb-2"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($store['location']); ?></div>
                        <p class="mb-3 flex-grow-1 small text-muted"><?php echo htmlspecialchars($store['description']); ?></p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <?php foreach ($store['categories'] as $cat): ?>
                                <span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars($cat); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-auto d-flex gap-2">
                            <a class="btn btn-sm btn-primary" href="<?php echo build_path('/customer/listings.php?vendor=' . urlencode($store['vendor_id'])); ?>">View Products</a>
                            <a class="btn btn-sm btn-outline-primary" href="<?php echo build_path('/store.php?id=' . urlencode($store['id'])); ?>">Visit Store</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($filtered)): ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">No stores found.</div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>


