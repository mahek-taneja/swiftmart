<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/head.php';

// Redirect if already logged in as vendor
if (is_vendor()) {
    redirect(build_path('/vendor/dashboard.php'));
    exit;
}

$error = '';
$success = '';

// Check for subscription success message
if (isset($_SESSION['subscription_success'])) {
    $success = $_SESSION['subscription_success'];
    unset($_SESSION['subscription_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $db = Database::getInstance()->getConnection();
        // Join users and vendors tables for login
        $stmt = $db->prepare("
            SELECT u.id as user_id, u.email, u.password_hash, u.first_name, u.last_name,
                   v.id as vendor_id, v.business_name, v.status
            FROM users u
            INNER JOIN vendors v ON v.user_id = u.id
            WHERE u.email = ? AND u.role = 'vendor'
        ");
        $stmt->execute([$email]);
        $vendor = $stmt->fetch();

        if (!$vendor) {
            $error = "No account found with this email.";
        } elseif ($vendor['status'] === 'suspended') {
            $error = "Your account has been suspended. Please contact admin.";
        } elseif ($vendor['status'] === 'rejected') {
            $error = "Your account was rejected. Please contact admin.";
        } elseif ($vendor['status'] === 'pending') {
            $error = "Your account is pending approval. Please wait for admin approval.";
        } elseif (!password_verify($password, $vendor['password_hash'])) {
            $error = "Incorrect password.";
        } else {
            $_SESSION['vendor_id'] = $vendor['vendor_id'];
            $_SESSION['user_id'] = $vendor['user_id'];
            $_SESSION['vendor_name'] = $vendor['business_name'];
            $_SESSION['user_role'] = 'vendor';
            redirect(build_path('/vendor/dashboard.php'));
            exit;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head><?php render_head('Vendor Login'); ?></head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <main class="container py-4" style="max-width:600px;">
        <h2 class="h4 mb-3">Vendor Login</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" class="card p-3">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button class="btn btn-primary w-100">Login</button>
        </form>
    </main>
</body>

</html>