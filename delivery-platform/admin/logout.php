<?php
require_once __DIR__ . '/../includes/config.php';
unset($_SESSION['is_admin']);
header('Location: ' . build_path('/admin/login.php'));
exit;
?>

