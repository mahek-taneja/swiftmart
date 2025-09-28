<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function base_path() : string {
    // If explicitly set, use environment override (e.g., "/swiftmart")
    $override = getenv('APP_BASE_URI');
    if ($override !== false) {
        $override = rtrim($override, '/');
        return $override === '' ? '' : $override;
    }

    // Auto-detect app root as the first path segment (e.g., /swiftmart)
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $parts = explode('/', trim($scriptName, '/'));
    if (count($parts) > 1) {
        // script under /app/..., use "/app" as base
        return '/' . $parts[0];
    }
    // running at domain root
    return '';
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

