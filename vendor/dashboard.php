<?php
// vendor/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/head.php';

// ✅ Ensure vendor is logged in
require_vendor();

// ✅ Get current vendor ID from helper or session
$vendorId = $_SESSION['vendor_id'] ?? null;

if (!$vendorId) {
    // if still missing, redirect to login
    header('Location: ' . build_path('/vendor/login.php'));
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // ✅ Fetch vendor info
    $stmt = $db->prepare("SELECT name, email, approved FROM vendors WHERE id = ?");
    $stmt->execute([$vendorId]);
    $vendor = $stmt->fetch();

    if (!$vendor) {
        // vendor not found in DB, log out
        session_destroy();
        header('Location: ' . build_path('/vendor/login.php'));
        exit;
    }

    // ✅ Fetch vendor products
    $productStmt = $db->prepare("SELECT id, name, category, price_cents AS price, stock FROM products WHERE vendor_id = ?");
    $productStmt->execute([$vendorId]);
    $myProducts = $productStmt->fetchAll();

} catch (Exception $e) {
    $error = $e->getMessage();
    $vendor = ['name' => 'Unknown', 'email' => '', 'approved' => 0];
    $myProducts = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('Vendor Dashboard – ' . htmlspecialchars($vendor['name'] ?? 'Vendor')); ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Welcome, <?= htmlspecialchars($vendor['name']) ?></h1>
            <a class="btn btn-outline-secondary" href="<?= build_path('/vendor/logout.php'); ?>">Logout</a>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-4">
                <div class="card p-3 h-100 shadow-sm">
                    <div class="small text-muted">Total Products</div>
                    <div class="display-6"><?= count($myProducts); ?></div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card p-3 h-100 shadow-sm">
                    <div class="small text-muted">Orders</div>
                    <div class="display-6">Coming Soon</div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card p-3 h-100 shadow-sm">
                    <div class="small text-muted">Revenue</div>
                    <div class="display-6">₹0.00</div>
                </div>
            </div>
        </div>

        <h2 class="h5 mt-4">Your Products</h2>
        <div class="table-responsive card shadow-sm">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($myProducts) > 0): ?>
                    <?php foreach ($myProducts as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']); ?></td>
                        <td><?= htmlspecialchars($p['category']); ?></td>
                        <td><?= format_price_cents((int)$p['price']); ?></td>
                        <td><?= (int)$p['stock']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-muted text-center py-3">No products yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2">
            <a class="btn btn-primary" href="<?= build_path('/vendor/inventory.php'); ?>">Manage Inventory</a>
            <a class="btn btn-outline-primary" href="<?= build_path('/vendor/orders.php'); ?>">Order Management</a>
            <a class="btn btn-outline-secondary" href="<?= build_path('/vendor/analytics.php'); ?>">Sales Analytics</a>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>