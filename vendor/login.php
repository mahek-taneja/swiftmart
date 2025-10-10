<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/head.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM vendors WHERE email = ?");
        $stmt->execute([$email]);
        $vendor = $stmt->fetch();

        if (!$vendor) {
            $error = "No account found.";
        } elseif (!$vendor['approved']) {
            $error = "Your account is pending approval.";
        } elseif (!password_verify($password, $vendor['password_hash'])) {
            $error = "Incorrect password.";
        } else {
            $_SESSION['vendor_id'] = $vendor['id'];
            $_SESSION['vendor_name'] = $vendor['name'];
            $_SESSION['user_role'] = 'vendor';
            header("Location: " . build_path('/vendor/dashboard.php'));
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