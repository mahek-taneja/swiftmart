<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function base_path() : string {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    return $dir === '/' ? '' : $dir;
}

function build_path(string $path) : string {
    $base = base_path();
    if ($path === '' || $path === '/') {
        return $base ?: '/';
    }
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }
    return ($base ?: '') . $path;
}

function format_price_cents(int $cents) : string {
    $dollars = number_format($cents / 100, 2);
    return '$' . $dollars;
}

function current_vendor_id() : ?string {
    return $_SESSION['vendor_id'] ?? null;
}

function require_vendor(): void {
    if (!current_vendor_id()) {
        header('Location: ' . build_path('/vendor/login.php'));
        exit;
    }
}

function is_admin() : bool { return !empty($_SESSION['is_admin']); }

function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . build_path('/admin/login.php'));
        exit;
    }
}

?>


