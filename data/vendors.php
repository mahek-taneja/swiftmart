<?php
// Ensure session + config are loaded first
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../data/vendors.php';

/**
 * Gate: only admins allowed
 * (Make sure require_admin() exists in includes/config.php – see note below)
 */
if (!function_exists('require_admin')) {
    function require_admin(): void {
        if (empty($_SESSION['is_admin'])) {
            header('Location: ' . url_path('/admin/login.php'));
            exit;
        }
    }
}
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
            <h1 class="h4 mb-0">Vendor Management</h1>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary"
                    href="<?= htmlspecialchars(url_path('/admin/users.php')) ?>">Users</a>
                <a class="btn btn-outline-secondary"
                    href="<?= htmlspecialchars(url_path('/admin/analytics.php')) ?>">Analytics</a>
                <a class="btn btn-outline-danger"
                    href="<?= htmlspecialchars(url_path('/admin/logout.php')) ?>">Logout</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($VENDORS as $v): ?>
                            <tr>
                                <td><?= htmlspecialchars($v['name']) ?></td>
                                <td><a
                                        href="mailto:<?= htmlspecialchars($v['email']) ?>"><?= htmlspecialchars($v['email']) ?></a>
                                </td>
                                <td>
                                    <?php if (!empty($v['approved'])): ?>
                                    <span class="badge text-bg-success">Approved</span>
                                    <?php else: ?>
                                    <span class="badge text-bg-secondary">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <!-- Wire these up later to POST actions or an API -->
                                    <button class="btn btn-sm btn-outline-primary" disabled>Approve</button>
                                    <button class="btn btn-sm btn-outline-danger" disabled>Reject</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>