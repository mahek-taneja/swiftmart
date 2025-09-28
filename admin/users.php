<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../data/users.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Admin – User Management'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">User Management</h1>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/admin/vendors.php'); ?>">Back</a>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Name</th><th>Email</th></tr></thead>
      <tbody>
        <?php foreach($USERS as $u): ?>
          <tr>
            <td><?php echo htmlspecialchars($u['name']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


