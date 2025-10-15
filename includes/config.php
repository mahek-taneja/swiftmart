<?php
// includes/config.php — SwiftMart (DB port-safe, robust)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');   // use 127.0.0.1 on Windows
define('DB_PORT', 3307);          // <-- set to 3306 if your MySQL uses the default port
define('DB_NAME', 'swiftmart');
define('DB_USER', 'root');        // XAMPP default
define('DB_PASS', '');            // XAMPP default (blank)
define('DB_CHARSET', 'utf8mb4');
define('DB_DEBUG', true);         // show exact PDO errors during setup (set false in prod)


define('APP_NAME', 'SwiftMart');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/swiftmart');
define('APP_TIMEZONE', 'Asia/Kolkata');

define('JWT_SECRET', 'your-secret-key-change-this-in-production');
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour


define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

define('ITEMS_PER_PAGE', 20);
define('ADMIN_ITEMS_PER_PAGE', 50);


class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("PDO connect failed: " . $e->getMessage());
            if (DB_DEBUG) {
                // During setup, show the exact reason (e.g., port/host/db/creds)
                throw new Exception("PDO connect failed: " . $e->getMessage());
            }
            throw new Exception("Database connection failed");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function getConnection() { return $this->connection; }
    public function prepare($sql)    { return $this->connection->prepare($sql); }
    public function query($sql)      { return $this->connection->query($sql); }
    public function lastInsertId()   { return $this->connection->lastInsertId(); }
    public function beginTransaction(){ return $this->connection->beginTransaction(); }
    public function commit()         { return $this->connection->commit(); }
    public function rollback()       { return $this->connection->rollback(); }
}


function base_path(): string {
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $appRootFs = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    if ($docRoot && $appRootFs && str_starts_with($appRootFs, $docRoot)) {
        $rel = substr($appRootFs, strlen($docRoot));
        return $rel === '' ? '' : rtrim($rel, '/');
    }
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return $dir === '/' ? '' : $dir;
}

function build_path(string $path): string {
    $base = base_path();
    if ($path === '' || $path === '/') return $base ?: '/';
    if ($path[0] !== '/') $path = '/' . $path;
    return ($base ?: '') . $path;
}

// Alias for old code using url_path()
if (!function_exists('url_path')) {
    function url_path(string $path = ''): string {
        return build_path($path);
    }
}

function redirect(string $url, int $status_code = 302): void {
    header("Location: $url", true, $status_code);
    exit;
}


function current_user_id(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function current_vendor_id(): ?int {
    if (!empty($_SESSION['vendor_id'])) return (int)$_SESSION['vendor_id']; // vendor login
    // fallback: some older flows used user_id+role
    if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'vendor' && !empty($_SESSION['user_id'])) {
        return (int)$_SESSION['user_id'];
    }
    return null;
}

function current_user_role(): ?string {
    return $_SESSION['user_role'] ?? null;
}

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']) || !empty($_SESSION['vendor_id']) || !empty($_SESSION['is_admin']);
}

function is_admin(): bool {
    return !empty($_SESSION['is_admin']) || (($_SESSION['user_role'] ?? '') === 'admin');
}

function is_vendor(): bool {
    return (($_SESSION['user_role'] ?? '') === 'vendor') && !empty(current_vendor_id());
}

function is_customer(): bool {
    return (($_SESSION['user_role'] ?? '') === 'customer');
}

function require_login(): void {
    if (!is_logged_in()) {
        redirect(build_path('/customer/login.php'));
    }
}

function require_vendor(): void {
    if (!is_vendor()) {
        redirect(build_path('/vendor/login.php'));
    }
}

function require_admin(): void {
    if (!is_admin()) {
        redirect(build_path('/admin/login.php'));
    }
}

if (isset($_SESSION['user_id']) || isset($_SESSION['vendor_id']) || isset($_SESSION['is_admin'])) {
    $now = time();
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = $now;
    } elseif (($now - ($_SESSION['last_activity'] ?? 0)) > SESSION_TIMEOUT) {
        $_SESSION = [];
        session_destroy();
        redirect(build_path('/admin/login.php'));
    } else {
        $_SESSION['last_activity'] = $now;
    }
}


function format_price_cents(int $cents): string {
    return '₹' . number_format($cents / 100, 2);
}

function format_price(float $amount): string {
    return '₹' . number_format($amount, 2);
}

function generate_csrf_token(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize_input(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validate_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_password(string $password): array {
    $errors = [];
    if (strlen($password) < PASSWORD_MIN_LENGTH) $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long.";
    if (!preg_match('/[A-Z]/', $password))      $errors[] = "Password must contain at least one uppercase letter.";
    if (!preg_match('/[a-z]/', $password))      $errors[] = "Password must contain at least one lowercase letter.";
    if (!preg_match('/[0-9]/', $password))      $errors[] = "Password must contain at least one number.";
    return $errors;
}

function hash_password(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function generate_order_number(): string {
    return 'SM' . date('Ymd') . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);
}


function upload_file(array $file, string $directory = ''): array {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK)
        return ['success' => false, 'errors' => ['File upload failed']];
    if (($file['size'] ?? 0) > UPLOAD_MAX_SIZE)
        return ['success' => false, 'errors' => ['File too large']];
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, UPLOAD_ALLOWED_TYPES))
        return ['success' => false, 'errors' => ['Invalid file type']];
    $upload_dir = rtrim(UPLOAD_PATH . $directory, '/');
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $filename = uniqid('', true) . '.' . $ext;
    $filepath = $upload_dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $filepath))
        return ['success' => false, 'errors' => ['Failed to move uploaded file']];
    return [
        'success'  => true,
        'filename' => $filename,
        'filepath' => $filepath,
        'url'      => build_path('/uploads/' . trim($directory, '/') . '/' . $filename)
    ];
}

function paginate(int $total_items, int $current_page = 1, int $items_per_page = ITEMS_PER_PAGE): array {
    $total_pages = max(1, (int)ceil($total_items / $items_per_page));
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    return [
        'current_page'   => $current_page,
        'total_pages'    => $total_pages,
        'items_per_page' => $items_per_page,
        'offset'         => $offset
    ];
}

function json_response(array $data, int $status_code = 200): void {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function flash_message(string $type, string $message): void {
    $_SESSION['flash'][$type][] = $message;
}

function get_flash_messages(string $type = null): array {
    if ($type) {
        $msgs = $_SESSION['flash'][$type] ?? [];
        unset($_SESSION['flash'][$type]);
        return $msgs;
    }
    $all = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $all;
}

function has_flash_messages(string $type = null): bool {
    return $type ? !empty($_SESSION['flash'][$type]) : !empty($_SESSION['flash']);
}

date_default_timezone_set(APP_TIMEZONE);