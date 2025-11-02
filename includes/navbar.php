<?php 
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check user states
$isLoggedIn = is_logged_in();
$isVendor = is_vendor();
$isAdmin = is_admin();
$isCustomer = is_customer();
$vendorId = current_vendor_id();
$userId = current_user_id();
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo build_path('/index.php'); ?>">
            <img src="<?php echo build_path('/assets/img/logo.png'); ?>" alt="SwiftMart"
                style="height:56px;width:auto" />

        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="<?php echo build_path('/index.php'); ?>">
                        <i class="bi bi-house me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="<?php echo build_path('/customer/listings.php'); ?>">
                        <i class="bi bi-shop me-1"></i>Shop
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="<?php echo build_path('/customer/cart.php'); ?>">
                        <i class="bi bi-cart me-1"></i>Cart
                        <span class="badge text-bg-primary ms-1" id="cart-count">0</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php if ($isVendor): ?>
                        Vendor Account
                        <?php elseif ($isAdmin): ?>
                        Admin
                        <?php elseif ($isCustomer): ?>
                        Customer
                        <?php else: ?>
                        Account
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if ($isVendor): ?>
                        <!-- Logged in as Vendor -->
                        <li><a class="dropdown-item" href="<?php echo build_path('/vendor/dashboard.php'); ?>">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a></li>
                        <li><a class="dropdown-item" href="<?php echo build_path('/vendor/inventory.php'); ?>">
                                <i class="bi bi-box-seam me-2"></i>Inventory
                            </a></li>
                        <li><a class="dropdown-item" href="<?php echo build_path('/vendor/orders.php'); ?>">
                                <i class="bi bi-cart-check me-2"></i>Orders
                            </a></li>
                        <li><a class="dropdown-item" href="<?php echo build_path('/vendor/analytics.php'); ?>">
                                <i class="bi bi-graph-up me-2"></i>Analytics
                            </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="<?php echo build_path('/vendor/logout.php'); ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        <?php elseif ($isAdmin): ?>
                        <!-- Logged in as Admin -->
                        <li><a class="dropdown-item" href="<?php echo build_path('/admin/users.php'); ?>">
                                <i class="bi bi-people me-2"></i>Users
                            </a></li>
                        <li><a class="dropdown-item" href="<?php echo build_path('/admin/vendors.php'); ?>">
                                <i class="bi bi-shop me-2"></i>Vendors
                            </a></li>
                        <li><a class="dropdown-item" href="<?php echo build_path('/admin/analytics.php'); ?>">
                                <i class="bi bi-graph-up me-2"></i>Analytics
                            </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="<?php echo build_path('/admin/logout.php'); ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        <?php else: ?>
                        <!-- Not logged in - Show registration options -->
                        <li><a class="dropdown-item" href="<?php echo build_path('/vendor/register.php'); ?>">
                                <i class="bi bi-person-plus me-2"></i>Become a Vendor
                            </a></li>
                        <li><a class="dropdown-item" href="<?php echo build_path('/vendor/login.php'); ?>">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Vendor Login
                            </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="<?php echo build_path('/admin/login.php'); ?>">
                                <i class="bi bi-gear me-2"></i>Admin Login
                            </a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="<?php echo build_path('/stores.php'); ?>">
                        <i class="bi bi-building me-1"></i>Stores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="<?php echo build_path('/chatbot.php'); ?>">
                        <i class="bi bi-robot me-1"></i>AI Assistant
                    </a>
                </li>
            </ul>
            <form class="d-flex my-2 my-lg-0" role="search" action="<?php echo build_path('/customer/listings.php'); ?>"
                method="get">
                <div class="input-group">

                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i>
                        <span class="d-none d-md-inline ms-1">Search</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        try {
            if (window.updateCartCountBadge) {
                updateCartCountBadge();
            }
        } catch (e) {}
    });
    </script>
</nav>