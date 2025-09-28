<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../data/vendors.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Admin – Vendor Management'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Vendor Management</h1>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/admin/logout.php'); ?>">Logout</a>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($VENDORS as $v): ?>
          <tr>
            <td><?php echo htmlspecialchars($v['name']); ?></td>
            <td><?php echo htmlspecialchars($v['email']); ?></td>
            <td><?php echo $v['approved'] ? '<span class="badge text-bg-success">Approved</span>' : '<span class="badge text-bg-secondary">Pending</span>'; ?></td>
            <td>
              <button class="btn btn-sm btn-outline-primary" disabled>Approve</button>
              <button class="btn btn-sm btn-outline-danger" disabled>Reject</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="mt-3">
    <a class="btn btn-outline-primary" href="<?php echo build_path('/admin/users.php'); ?>">User Management</a>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/admin/analytics.php'); ?>">Analytics</a>
  </div>
  
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

