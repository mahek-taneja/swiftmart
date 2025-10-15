<?php
require_once __DIR__ . '/includes/config.php';
try {
  $pdo = Database::getInstance()->getConnection();
  $row = $pdo->query("SELECT DATABASE() db, VERSION() ver")->fetch();
  echo "✅ Connected to DB: " . htmlspecialchars($row['db']) . "<br>";
  echo "MySQL version: " . htmlspecialchars($row['ver']);
} catch (Throwable $e) {
  echo "❌ DB error: " . htmlspecialchars($e->getMessage());
}