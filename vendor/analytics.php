<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_vendor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Sales Analytics – DeliverX'); ?>
  <style>
    .placeholder-chart{height:220px;background:repeating-linear-gradient(45deg,#e9ecef,#e9ecef 10px,#f8f9fa 10px,#f8f9fa 20px)}
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Sales Analytics (AI Placeholder)</h1>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/vendor/dashboard.php'); ?>">Back</a>
  </div>
  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card p-3">
        <div class="small text-muted mb-2">Revenue Trend</div>
        <div class="placeholder-chart rounded"></div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card p-3">
        <div class="small text-muted mb-2">Top Categories</div>
        <div class="placeholder-chart rounded"></div>
      </div>
    </div>
  </div>
  <div class="card p-3 mt-3">
    <div class="small text-muted">AI Insights (stub)</div>
    <ul class="mb-0">
      <li>Peak sales on weekends.</li>
      <li>Cross-sell opportunity: Headphones buyers also view phone cases.</li>
    </ul>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


