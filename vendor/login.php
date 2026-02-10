<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/head.php';

// Redirect if already logged in as vendor
if (function_exists('is_vendor') && is_vendor()) {
    redirect(build_path('/vendor/dashboard.php'));
    exit;
}

$error = '';
$success = '';

if (isset($_SESSION['subscription_success'])) {
    $success = $_SESSION['subscription_success'];
    unset($_SESSION['subscription_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = "Please provide email and password.";
    } else {
        try {
            $db = Database::getInstance()->getConnection();

            // Does vendors table have password_hash?
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE table_schema = DATABASE()
                  AND table_name = 'vendors'
                  AND column_name = 'password_hash'
            ");
            $stmt->execute();
            $has_vendor_password_hash = (bool) $stmt->fetchColumn();

            // Does vendors table have a user_id linking to users?
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE table_schema = DATABASE()
                  AND table_name = 'vendors'
                  AND column_name = 'user_id'
            ");
            $stmt->execute();
            $has_vendor_user_id = (bool) $stmt->fetchColumn();

            // Does users table have password column?
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE table_schema = DATABASE()
                  AND table_name = 'users'
                  AND column_name = 'password'
            ");
            $stmt->execute();
            $has_users_password = (bool) $stmt->fetchColumn();

            $vendor = false;

            if ($has_vendor_password_hash) {
                // Authenticate using vendors table directly
                $stmt = $db->prepare("
                    SELECT id AS vendor_id, name AS vendor_name, email, password_hash, approved
                    FROM vendors
                    WHERE email = ?
                    LIMIT 1
                ");
                $stmt->execute([$email]);
                $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$vendor) {
                    $error = "No vendor account found with this email.";
                } else {
                    if (isset($vendor['approved']) && intval($vendor['approved']) === 0) {
                        $error = "Your vendor account is not approved yet. Please wait for admin approval.";
                    } elseif (!password_verify($password, $vendor['password_hash'])) {
                        $error = "Incorrect password.";
                    } else {
                        $_SESSION['vendor_id']   = $vendor['vendor_id'];
                        $_SESSION['vendor_name'] = $vendor['vendor_name'] ?? null;
                        // don't attempt to fetch vendor.user_id since column may not exist
                        $_SESSION['user_role'] = 'vendor';
                        redirect(build_path('/vendor/dashboard.php'));
                        exit;
                    }
                }
            } elseif ($has_vendor_user_id && $has_users_password) {
                // Authenticate by joining users->vendors (only if linking exists)
                $stmt = $db->prepare("
                    SELECT u.id AS user_id, u.email AS user_email, u.password AS user_password, u.name AS user_name,
                           v.id AS vendor_id, v.name AS vendor_name, v.approved
                    FROM vendors v
                    INNER JOIN users u ON v.user_id = u.id
                    WHERE u.email = ? AND u.role = 'vendor'
                    LIMIT 1
                ");
                $stmt->execute([$email]);
                $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$vendor) {
                    $error = "No vendor account found with this email.";
                } else {
                    if (isset($vendor['approved']) && intval($vendor['approved']) === 0) {
                        $error = "Your vendor account is not approved yet. Please wait for admin approval.";
                    } elseif (!password_verify($password, $vendor['user_password'])) {
                        $error = "Incorrect password.";
                    } else {
                        $_SESSION['vendor_id']   = $vendor['vendor_id'];
                        $_SESSION['user_id']     = $vendor['user_id'];
                        $_SESSION['vendor_name'] = $vendor['vendor_name'] ?? $vendor['user_name'] ?? null;
                        $_SESSION['user_role']   = 'vendor';
                        redirect(build_path('/vendor/dashboard.php'));
                        exit;
                    }
                }
            } else {
                // Last resort: fetch vendors.* to let you inspect columns manually
                $stmt = $db->prepare("SELECT * FROM vendors WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$vendor) {
                    $error = "No vendor account found with this email.";
                } else {
                    if (isset($vendor['password_hash'])) {
                        if (!password_verify($password, $vendor['password_hash'])) {
                            $error = "Incorrect password.";
                        } else {
                            $_SESSION['vendor_id'] = $vendor['id'];
                            $_SESSION['vendor_name'] = $vendor['name'] ?? null;
                            $_SESSION['user_role'] = 'vendor';
                            redirect(build_path('/vendor/dashboard.php'));
                            exit;
                        }
                    } elseif (isset($vendor['password'])) {
                        // insecure plain-text password check (only if present)
                        if ($vendor['password'] !== $password) {
                            $error = "Incorrect password.";
                        } else {
                            $_SESSION['vendor_id'] = $vendor['id'];
                            $_SESSION['vendor_name'] = $vendor['name'] ?? null;
                            $_SESSION['user_role'] = 'vendor';
                            redirect(build_path('/vendor/dashboard.php'));
                            exit;
                        }
                    } else {
                        $error = "Unable to authenticate: vendor password column not found. Please run DESCRIBE vendors and share the columns.";
                    }
                }
            }
        } catch (Exception $e) {
            // Do NOT show raw SQL errors to users in production.
            // Log $e->getMessage() to a file or error logger here.
            // error_log($e->getMessage()); // uncomment to log
            $error = "An unexpected error occurred. Please try again later.";
        }
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

        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" class="card p-3">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input autocomplete="username" type="email" class="form-control" name="email" required
                    value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input autocomplete="current-password" type="password" class="form-control" name="password" required>
            </div>
            <button class="btn btn-primary w-100">Login</button>
        </form>
    </main>
</body>

</html>