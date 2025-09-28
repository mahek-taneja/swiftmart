<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Admin – Analytics'); ?>
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
  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card p-3"><div class="small text-muted mb-2">GMV Trend</div><div class="placeholder rounded"></div></div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card p-3"><div class="small text-muted mb-2">Orders by Category</div><div class="placeholder rounded"></div></div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


