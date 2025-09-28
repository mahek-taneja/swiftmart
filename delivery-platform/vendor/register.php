<?php
require_once __DIR__ . '/../includes/head.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Vendor Registration – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4" style="max-width:700px">
  <h1 class="h4 mb-3">Vendor Registration</h1>
  <form class="card p-3">
    <div class="row g-3">
      <div class="col-12 col-md-6">
        <label class="form-label">Business Name</label>
        <input class="form-control" required>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" required>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Contact Number</label>
        <input class="form-control" required>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Category</label>
        <select class="form-select">
          <option>Groceries</option>
          <option>Electronics</option>
          <option>Fashion</option>
          <option>Home</option>
          <option>Health</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Address</label>
        <input class="form-control" required>
      </div>
    </div>
    <div class="mt-3 text-end">
      <button class="btn btn-primary" type="button" onclick="alert('Registration submitted! Admin will review.');">Submit</button>
    </div>
  </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

