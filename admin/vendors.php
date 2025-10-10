<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/head.php';

require_admin();
$db = Database::getInstance()->getConnection();

// Approve/Reject Vendor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vendor_id'], $_POST['action'])) {
    $id = (int)$_POST['vendor_id'];
    $action = $_POST['action'] === 'approve' ? 1 : 0;
    $stmt = $db->prepare("UPDATE vendors SET approved = ? WHERE id = ?");
    $stmt->execute([$action, $id]);
    $_SESSION['flash'][$action ? 'success' : 'warning'][] = $action ? 'Vendor approved.' : 'Vendor rejected.';
    header("Location: " . build_path('/admin/vendors.php'));
    exit;
}

$vendors = $db->query("SELECT * FROM vendors ORDER BY created_at DESC")->fetchAll();
$flashes = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">

<head><?php render_head('Admin – Vendor Management'); ?></head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Vendor Management</h1>
            <a href="<?= build_path('/admin/logout.php') ?>" class="btn btn-outline-danger">Logout</a>
        </div>

        <?php foreach ($flashes as $type => $messages): ?>
        <?php foreach ($messages as $msg): ?>
        <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endforeach; ?>
        <?php endforeach; ?>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendors as $v): ?>
                        <tr>
                            <td><?= htmlspecialchars($v['name']) ?></td>
                            <td><?= htmlspecialchars($v['email']) ?></td>
                            <td>
                                <?= $v['approved'] ? '<span class="badge text-bg-success">Approved</span>' : '<span class="badge text-bg-secondary">Pending</span>' ?>
                            </td>
                            <td>
                                <form method="post" style="display:inline-block;">
                                    <input type="hidden" name="vendor_id" value="<?= $v['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button class="btn btn-sm btn-outline-success"
                                        <?= $v['approved'] ? 'disabled' : '' ?>>Approve</button>
                                </form>
                                <form method="post" style="display:inline-block;">
                                    <input type="hidden" name="vendor_id" value="<?= $v['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button class="btn btn-sm btn-outline-danger"
                                        <?= !$v['approved'] ? 'disabled' : '' ?>>Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>