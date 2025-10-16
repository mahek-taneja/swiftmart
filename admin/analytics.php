<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_admin();
require_once __DIR__ . '/../php/ai/forecast.php';

$horizon = isset($_GET['horizon']) ? (int)$_GET['horizon'] : 90;
if (!in_array($horizon, [30,60,90], true)) $horizon = 90;
$data = ai_forecast(['horizon' => $horizon]);
$kpis = $data['kpis'] ?? [];
$chart = $data['chart'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Admin – Analytics'); ?>
  <script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
  <style>
    .placeholder{height:240px;background:repeating-linear-gradient(45deg,#e9ecef,#e9ecef 10px,#f8f9fa 10px,#f8f9fa 20px)}
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Platform Analytics</h1>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/admin/vendors.php'); ?>">Back</a>
  </div>
  <form class="row g-2 mb-3" method="get">
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
  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card p-3">
        <div class="small text-muted mb-2">GMV Trend</div>
        <?php if (!empty($data['error'])): ?>
          <div class="alert alert-danger mb-0"><?php echo htmlspecialchars($data['error']); ?></div>
        <?php else: ?>
          <div id="admin-forecast-chart" style="height:240px;"></div>
          <script>
            document.addEventListener('DOMContentLoaded', function(){
              var fig = <?= json_encode($chart ?? new stdClass()); ?>;
              if (fig && fig.data) Plotly.newPlot('admin-forecast-chart', fig.data, Object.assign({height:240, margin:{l:40,r:20,t:30,b:30}}, fig.layout||{}));
            });
          </script>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card p-3"><div class="small text-muted mb-2">Orders by Category</div><div class="placeholder rounded"></div></div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


