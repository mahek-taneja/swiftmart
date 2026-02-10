<?php
require_once __DIR__ . '/../../includes/head.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/models.php';
require_once __DIR__ . '/../ai/forecast.php';

require_vendor(); // 🔒 vendor must be logged in

/* -----------------------------
   GET LOGGED-IN VENDOR
----------------------------- */
$vendorId = current_vendor_id();

// Fetch vendor name from DB
$vendorName = 'Vendor';
$vendorModel = new Vendor();
$vendor = $vendorModel->find($vendorId);

if ($vendor && !empty($vendor['business_name'])) {
    $vendorName = $vendor['business_name'];
}

/* -----------------------------
   FORECAST SETTINGS
----------------------------- */
$horizon = isset($_GET['horizon']) ? (int)$_GET['horizon'] : 90;
if (!in_array($horizon, [30, 60, 90], true)) {
    $horizon = 90;
}

/* -----------------------------
   AI FORECAST
----------------------------- */
$data = ai_forecast([
    'vendor_id' => $vendorId,
    'horizon'   => $horizon
]);

$kpis  = $data['kpis'] ?? [];
$chart = $data['chart'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('Sales Forecast');
 ?>
    <script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
</head>

<body>
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="container py-4">

        <!-- PAGE TITLE -->
        <h1 class="mb-3">
            Sales Forecast
            <small class="text-muted">(<?= htmlspecialchars($vendorName); ?>)</small>
        </h1>

        <!-- CONTROLS -->
        <form class="row g-2 mb-3" method="get">
            <div class="col-auto">
                <label for="horizon" class="form-label">Horizon</label>
                <select id="horizon" name="horizon" class="form-select">
                    <option value="30" <?= $horizon === 30 ? 'selected' : ''; ?>>30 days</option>
                    <option value="60" <?= $horizon === 60 ? 'selected' : ''; ?>>60 days</option>
                    <option value="90" <?= $horizon === 90 ? 'selected' : ''; ?>>90 days</option>
                </select>
            </div>
            <div class="col-auto align-self-end">
                <button class="btn btn-primary">Update</button>
            </div>
        </form>

        <?php if (!empty($data['error'])): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($data['error']); ?>
        </div>

        <?php else: ?>

        <!-- KPI CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card p-3">
                    <div class="small text-muted">Recent Avg</div>
                    <div class="fs-4">₹<?= number_format((float)($kpis['recent_avg'] ?? 0), 2); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <div class="small text-muted">Last 30d Total</div>
                    <div class="fs-4">₹<?= number_format((float)($kpis['recent_total'] ?? 0), 2); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <div class="small text-muted">Forecast <?= $horizon; ?>d</div>
                    <div class="fs-4">
                        ₹<?= number_format((float)($kpis['pred_' . $horizon . '_total'] ?? 0), 2); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <div class="small text-muted">Trend</div>
                    <div class="fs-4"><?= number_format((float)($kpis['trend_pct'] ?? 0), 2); ?>%</div>
                </div>
            </div>
        </div>

        <!-- FORECAST CHART -->
        <div id="forecast-chart" style="height:400px;"></div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fig = <?= json_encode($chart ?? new stdClass()); ?>;
            if (fig && fig.data) {
                Plotly.newPlot('forecast-chart', fig.data, fig.layout || {});
            }
        });
        </script>

        <?php endif; ?>

    </main>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>