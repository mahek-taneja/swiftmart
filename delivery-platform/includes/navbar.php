<?php require_once __DIR__ . '/config.php'; ?>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo build_path('/index.php'); ?>">DeliverX</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo build_path('/customer/listings.php'); ?>">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo build_path('/customer/cart.php'); ?>"><i class="bi bi-cart"></i> Cart <span class="badge text-bg-primary" id="cart-count">0</span></a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Vendor</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo build_path('/vendor/login.php'); ?>">Login</a></li>
            <li><a class="dropdown-item" href="<?php echo build_path('/vendor/register.php'); ?>">Register</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?php echo build_path('/vendor/dashboard.php'); ?>">Dashboard</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="<?php echo build_path('/admin/login.php'); ?>">Admin</a></li>
      </ul>
      <form class="d-flex" role="search" action="<?php echo build_path('/customer/listings.php'); ?>" method="get">
        <input class="form-control me-2" type="search" name="q" placeholder="Search products" aria-label="Search">
        <button class="btn btn-outline-primary" type="submit">Search</button>
      </form>
    </div>
  </div>
  <script>document.addEventListener('DOMContentLoaded', function(){ try{ if(window.updateCartCountBadge){ updateCartCountBadge(); } }catch(e){} });</script>
</nav>

