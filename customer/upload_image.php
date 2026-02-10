<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/models.php';

// Simple upload endpoint for product images
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

header('Content-Type: application/json');

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing product_id']);
    exit;
}

$productId = (int)$_POST['product_id'];

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(['error' => 'Unsupported file type']);
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
    http_response_code(400);
    echo json_encode(['error' => 'File too large']);
    exit;
}

$ext = $allowed[$mime];
$uploadsDir = __DIR__ . '/../uploads/products';
if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

$filename = sprintf('%s_%s.%s', $productId, bin2hex(random_bytes(8)), $ext);
$destPath = $uploadsDir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to move uploaded file']);
    exit;
}

$imageUrl = '/uploads/products/' . $filename;

try {
    $db = Database::getInstance();
    // make existing images non-primary
    $stmt = $db->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = ?');
    $stmt->execute([$productId]);

    // insert new image
    $stmt = $db->prepare('INSERT INTO product_images (product_id, image_url, alt_text, sort_order, is_primary) VALUES (?, ?, ?, 0, 1)');
    $stmt->execute([$productId, $imageUrl, null]);

    echo json_encode(['success' => true, 'image_url' => $imageUrl]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

?>
