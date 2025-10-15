<?php
// vendor/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/head.php';

// ✅ Ensure vendor is logged in
require_vendor();

// ✅ Get current vendor ID via helper
$vendorId = current_vendor_id();
if (!$vendorId) {
    header('Location: ' . build_path('/vendor/login.php'));
    exit;
}

$error = '';
$vendor = ['name' => 'Vendor', 'email' => '', 'approved' => 0];
$myProducts = [];
$totalProducts = 0;

try {
    $db = Database::getInstance()->getConnection();

    // Fetch vendor info
    $stmt = $db->prepare("SELECT id, name, email, approved FROM vendors WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $vendorId]);
    $row = $stmt->fetch();
    if (!$row) {
        // Vendor not found → logout & redirect
        session_unset();
        session_destroy();
        header('Location: ' . build_path('/vendor/login.php'));
        exit;
    }
    $vendor = $row;

    // Detect price column (price_cents vs price)
    $hasPriceCents = false;
    $colStmt = $db->prepare("SHOW COLUMNS FROM products LIKE 'price_cents'");
    $colStmt->execute();
    $hasPriceCents = (bool)$colStmt->fetch();

    // Count products
    $countStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE vendor_id = :vid");
    $countStmt->execute([':vid' => $vendorId]);
    $totalProducts = (int)$countStmt->fetchColumn();

    // Products list with category join
    if ($hasPriceCents) {
        $sql = "
            SELECT p.id, p.name,
                   COALESCE(c.name, 'Uncategorized') AS category,
                   p.price_cents, p.stock
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.vendor_id = :vid
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT 200
        ";
    } else {
        $sql = "
            SELECT p.id, p.name,
                   COALESCE(c.name, 'Uncategorized') AS category,
                   p.price, p.stock
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.vendor_id = :vid
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT 200
        ";
    }
    $productStmt = $db->prepare($sql);
    $productStmt->execute([':vid' => $vendorId]);
    $myProducts = $productStmt->fetchAll();

} catch (Throwable $e) {
    $error = $e->getMessage();
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
                    <div class="display-6"><?= (int)$totalProducts; ?></div>
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
                        <th style="min-width:220px;">Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($myProducts): ?>
                    <?php foreach ($myProducts as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']); ?></td>
                        <td><?= htmlspecialchars($p['category'] ?? 'Uncategorized'); ?></td>
                        <td>
                            <?php
                  if (array_key_exists('price_cents', $p) && $p['price_cents'] !== null) {
                      echo format_price_cents((int)$p['price_cents']);
                  } elseif (array_key_exists('price', $p) && $p['price'] !== null) {
                      echo format_price((float)$p['price']);
                  } else {
                      echo '—';
                  }
                ?>
                        </td>
                        <td><?= (int)($p['stock'] ?? 0); ?></td>
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