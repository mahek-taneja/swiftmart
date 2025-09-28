<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';

$error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');
  if($email==='admin@example.com' && $password==='admin'){
    $_SESSION['is_admin'] = true;
    header('Location: ' . build_path('/admin/vendors.php'));
    exit;
  } else {
    $error = 'Invalid admin credentials (try admin@example.com / admin)';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Admin Login – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4" style="max-width:600px">
  <h1 class="h4 mb-3">Admin Login</h1>
  <?php if($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="post" class="card p-3">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" class="form-control" name="email" required value="admin@example.com">
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" class="form-control" name="password" required value="admin">
    </div>
    <button class="btn btn-primary" type="submit">Login</button>
  </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


