<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_vendor();
$vendorId = current_vendor_id();
if ($vendorId) {
	header('Location: ' . build_path('/php/pages/sales_forecast.php?vendor_id=' . (int)$vendorId));
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('Sales Analytics – DeliverX'); ?>
    <style>
    .placeholder-chart {
        height: 220px;
        background: repeating-linear-gradient(45deg, #e9ecef, #e9ecef 10px, #f8f9fa 10px, #f8f9fa 20px)
    }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4">Sales Analytics (AI Placeholder)</h1>
            <a class="btn btn-outline-secondary" href="<?php echo build_path('/vendor/dashboard.php'); ?>">Back</a>
        </div>
        <div class="alert alert-info">Redirecting to your sales forecast...</div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>