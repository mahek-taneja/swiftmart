<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_vendor();

$vendorId = current_vendor_id();

// Simple session-based demo orders (created in customer/payment.php)
if (!isset($_SESSION['demo_orders'])) {
    $_SESSION['demo_orders'] = [];
}

// Handle status update actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderNo = $_POST['order_number'] ?? '';
    if ($orderNo && isset($_SESSION['demo_orders'][$orderNo])) {
        $current = &$_SESSION['demo_orders'][$orderNo];
        $status  = $current['status'] ?? 'Processing';
        if ($status === 'Processing') {
            $current['status'] = 'Shipped';
        } elseif ($status === 'Shipped') {
            $current['status'] = 'Delivered';
        }
    }
    redirect(build_path('/vendor/orders.php'));
}

// Filter orders for this vendor
$allOrders    = $_SESSION['demo_orders'] ?? [];
$vendorOrders = array_filter($allOrders, function ($o) use ($vendorId) {
    return (int)($o['vendor_id'] ?? 0) === (int)$vendorId;
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('Vendor Orders – SwiftMart'); ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4">Order Management</h1>
            <a class="btn btn-outline-secondary" href="<?php echo build_path('/vendor/dashboard.php'); ?>">Back</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Status</th>
                        <th>ETA</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$vendorOrders): ?>
                    <tr>
                        <td colspan="4" class="text-muted text-center">No orders yet.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($vendorOrders as $o): ?>
                    <?php
                      $status = $o['status'] ?? 'Processing';
                      $eta    = $o['eta'] ?? 'N/A';
                      $orderNo = $o['order_number'];
                      $btnLabel = $status === 'Processing' ? 'Mark Shipped' : ($status === 'Shipped' ? 'Mark Delivered' : 'Completed');
                      $btnDisabled = $status === 'Delivered';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($orderNo); ?></td>
                        <td>
                            <span class="badge <?php echo $status === 'Delivered' ? 'text-bg-success' : 'text-bg-info'; ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($eta); ?></td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="order_number" value="<?php echo htmlspecialchars($orderNo); ?>">
                                <button class="btn btn-sm btn-outline-primary" <?php echo $btnDisabled ? 'disabled' : ''; ?>>
                                    <?php echo htmlspecialchars($btnLabel); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>
