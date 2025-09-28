<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function base_path() : string {
    // Determine the web root of the app relative to the server document root,
    // independent of the currently executing script path.
    // This ensures links built via build_path() work from any subdirectory.
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $appRootFs = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    if ($docRoot && $appRootFs && str_starts_with($appRootFs, $docRoot)) {
        $rel = substr($appRootFs, strlen($docRoot));
        return $rel === '' ? '' : rtrim($rel, '/');
    }
    // Fallback to previous behavior using current script directory
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
    $rupees = number_format($cents / 100, 2);
    return '₹' . $rupees;
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


