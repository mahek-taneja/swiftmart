<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../data/vendors.php';

$error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = trim($_POST['email'] ?? '');
  foreach($VENDORS as $v){
    if($v['email']===$email){
      $_SESSION['vendor_id'] = $v['id'];
      header('Location: ' . build_path('/vendor/dashboard.php'));
      exit;
    }
  }
  $error = 'Invalid credentials (use any vendor email from dummy data).';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Vendor Login – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4" style="max-width:600px">
  <h1 class="h4 mb-3">Vendor Login</h1>
  <?php if($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="post" class="card p-3">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" class="form-control" name="email" required placeholder="vendor@example.com">
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" class="form-control" name="password" required placeholder="dummy">
    </div>
    <button class="btn btn-primary" type="submit">Login</button>
  </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


