<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../data/orders.php';
require_vendor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Vendor Orders – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Order Management</h1>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/vendor/dashboard.php'); ?>">Back</a>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Order ID</th><th>Status</th><th>ETA</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($ORDERS as $o): ?>
        <tr>
          <td><?php echo htmlspecialchars($o['id']); ?></td>
          <td><span class="badge text-bg-info"><?php echo htmlspecialchars($o['status']); ?></span></td>
          <td><?php echo htmlspecialchars($o['eta']); ?></td>
          <td>
            <button class="btn btn-sm btn-outline-primary" disabled>Mark Shipped</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

