<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../ai/forecast.php';

$horizon = isset($_GET['horizon']) ? (int)$_GET['horizon'] : 90;
if (!in_array($horizon, [30,60,90], true)) $horizon = 90;
$vendorId = isset($_GET['vendor_id']) ? (int)$_GET['vendor_id'] : null;
$data = ai_forecast(['horizon' => $horizon, 'vendor_id' => $vendorId]);
$kpis = $data['kpis'] ?? [];
$chart = $data['chart'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Forecast<?= $vendorId? ' – Vendor #'.(int)$vendorId : '' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
</head>

<body>
    <div class="container py-4">
        <h1 class="mb-3">Sales Forecast
            <?= $vendorId? '<small class="text-muted">(Vendor #'.(int)$vendorId.')</small>' : '' ?></h1>
        <form class="row g-2 mb-3" method="get">
            <input type="hidden" name="vendor_id" value="<?= (int)($vendorId ?? 0) ?>">
            <div class="col-auto">
                <label for="horizon" class="form-label">Horizon</label>
                <select id="horizon" name="horizon" class="form-select">
                    <option value="30" <?= $horizon===30?'selected':''; ?>>30 days</option>
                    <option value="60" <?= $horizon===60?'selected':''; ?>>60 days</option>
                    <option value="90" <?= $horizon===90?'selected':''; ?>>90 days</option>
                </select>
            </div>
            <div class="col-auto align-self-end">
                <button class="btn btn-primary">Update</button>
            </div>
        </form>

        <?php if (!empty($data['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($data['error']); ?></div>
        <?php else: ?>
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
                    <div class="fs-4">₹<?= number_format((float)($kpis['pred_'.($horizon).'_total'] ?? 0), 2); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <div class="small text-muted">Trend</div>
                    <div class="fs-4"><?= number_format((float)($kpis['trend_pct'] ?? 0), 2); ?>%</div>
                </div>
            </div>
        </div>
        <div id="forecast-chart" style="height:400px;"></div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var fig = <?= json_encode($chart ?? new stdClass()); ?>;
            if (fig && fig.data) Plotly.newPlot('forecast-chart', fig.data, fig.layout || {});
        });
        </script>
        <?php endif; ?>
    </div>
</body>

</html>