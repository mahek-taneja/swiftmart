<?php
require_once __DIR__ . '/../includes/config.php';

try {
    $db = Database::getInstance();

    $sql = "CREATE TABLE IF NOT EXISTS `product_images` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `product_id` int(11) NOT NULL,
      `image_url` varchar(255) NOT NULL,
      `alt_text` varchar(200) DEFAULT NULL,
      `sort_order` int(11) DEFAULT 0,
      `is_primary` tinyint(1) NOT NULL DEFAULT 0,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_product` (`product_id`),
      KEY `idx_primary` (`is_primary`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    // Database::getInstance() wraps PDO; use getConnection() for exec
    $pdo = $db->getConnection();
    $pdo->exec($sql);
    echo "product_images table created or already exists\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

?>
