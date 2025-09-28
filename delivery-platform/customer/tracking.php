<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../data/orders.php';
$oid = $_GET['id'] ?? ($ORDERS[0]['id'] ?? '');
$order = null;
foreach($ORDERS as $o){ if($o['id']===$oid){ $order=$o; break; }}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Order Tracking – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <h1 class="h4 mb-3">Order Tracking</h1>
  <?php if(!$order): ?>
    <div class="alert alert-info">Enter an order ID:</div>
    <form class="d-flex" method="get">
      <input class="form-control me-2" name="id" placeholder="e.g., o1001" required>
      <button class="btn btn-primary">Track</button>
    </form>
  <?php else: ?>
    <div class="card p-3">
      <div><strong>Order ID:</strong> <?php echo htmlspecialchars($order['id']); ?></div>
      <div><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></div>
      <div><strong>ETA:</strong> <?php echo htmlspecialchars($order['eta']); ?></div>
      <hr>
      <div class="progress" role="progressbar" aria-label="Order Progress" aria-valuenow="66" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar progress-bar-striped" style="width: 66%">In Transit</div>
      </div>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

