<?php
require_once __DIR__ . '/../includes/config.php';
unset($_SESSION['vendor_id']);
header('Location: ' . build_path('/vendor/login.php'));
exit;
?>

