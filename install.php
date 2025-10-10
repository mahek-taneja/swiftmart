<?php
require_once __DIR__ . '/../includes/config.php';

/**
 * Database Installation Script
 */
class DatabaseInstaller {
    private $pdo;
    
    public function __construct() {
        try {
            // Connect to MySQL without specifying database
            $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function install() {
        try {
            $this->pdo->beginTransaction();
            
            // Create database
            $this->createDatabase();
            
            // Use the database
            $this->pdo->exec("USE " . DB_NAME);
            
            // Read and execute schema
            $this->executeSchema();
            
            // Insert sample data
            $this->insertSampleData();
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Database installed successfully!'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            return [
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage()
            ];
        }
    }
    
    private function createDatabase() {
        $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $this->pdo->exec($sql);
    }
    
    private function executeSchema() {
        $schemaFile = __DIR__ . '/../database/schema.sql';
        
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found: " . $schemaFile);
        }
        
        $schema = file_get_contents($schemaFile);
        
        // Split by semicolon and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(--|\/\*)/', $statement)) {
                $this->pdo->exec($statement);
            }
        }
    }
    
    private function insertSampleData() {
        // Insert default admin user
        $adminPassword = hash_password('admin123');
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, email_verified) 
                VALUES ('admin', 'admin@swiftmart.com', ?, 'Admin', 'User', 'admin', 'active', 1)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$adminPassword]);
        
        // Insert sample vendor user
        $vendorPassword = hash_password('vendor123');
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, email_verified) 
                VALUES ('vendor1', 'vendor@swiftmart.com', ?, 'John', 'Doe', 'vendor', 'active', 1)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$vendorPassword]);
        
        $vendorUserId = $this->pdo->lastInsertId();
        
        // Insert vendor details
        $sql = "INSERT INTO vendors (user_id, business_name, business_type, description, address, city, state, zip_code, country, status) 
                VALUES (?, 'Sample Store', 'Retail', 'A sample store for testing', '123 Main St', 'New York', 'NY', '10001', 'USA', 'approved')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$vendorUserId]);
        
        $vendorId = $this->pdo->lastInsertId();
        
        // Insert sample products
        $products = [
            [
                'vendor_id' => $vendorId,
                'category_id' => 1, // Electronics
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'description' => 'Latest iPhone with advanced features',
                'short_description' => 'Premium smartphone',
                'price' => 999.99,
                'sku' => 'IPH15PRO',
                'stock_quantity' => 50,
                'is_featured' => 1,
                'is_active' => 1
            ],
            [
                'vendor_id' => $vendorId,
                'category_id' => 2, // Fashion
                'name' => 'Designer T-Shirt',
                'slug' => 'designer-t-shirt',
                'description' => 'High-quality cotton t-shirt',
                'short_description' => 'Comfortable cotton shirt',
                'price' => 29.99,
                'sku' => 'TSHIRT001',
                'stock_quantity' => 100,
                'is_featured' => 1,
                'is_active' => 1
            ],
            [
                'vendor_id' => $vendorId,
                'category_id' => 3, // Food & Drinks
                'name' => 'Organic Coffee Beans',
                'slug' => 'organic-coffee-beans',
                'description' => 'Premium organic coffee beans',
                'short_description' => 'Fresh roasted coffee',
                'price' => 19.99,
                'sku' => 'COFFEE001',
                'stock_quantity' => 200,
                'is_featured' => 0,
                'is_active' => 1
            ]
        ];
        
        foreach ($products as $product) {
            $sql = "INSERT INTO products (vendor_id, category_id, name, slug, description, short_description, price, sku, stock_quantity, is_featured, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $product['vendor_id'],
                $product['category_id'],
                $product['name'],
                $product['slug'],
                $product['description'],
                $product['short_description'],
                $product['price'],
                $product['sku'],
                $product['stock_quantity'],
                $product['is_featured'],
                $product['is_active']
            ]);
        }
        
        // Insert sample customer
        $customerPassword = hash_password('customer123');
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, email_verified) 
                VALUES ('customer1', 'customer@swiftmart.com', ?, 'Jane', 'Smith', 'customer', 'active', 1)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customerPassword]);
    }
    
    public function checkInstallation() {
        try {
            $this->pdo->exec("USE " . DB_NAME);
            
            // Check if tables exist
            $tables = ['users', 'vendors', 'categories', 'products', 'orders'];
            $existingTables = [];
            
            foreach ($tables as $table) {
                $sql = "SHOW TABLES LIKE ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$table]);
                if ($stmt->fetch()) {
                    $existingTables[] = $table;
                }
            }
            
            return [
                'installed' => count($existingTables) === count($tables),
                'existing_tables' => $existingTables,
                'missing_tables' => array_diff($tables, $existingTables)
            ];
            
        } catch (Exception $e) {
            return [
                'installed' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

// Handle installation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $installer = new DatabaseInstaller();
    
    switch ($_POST['action']) {
        case 'install':
            $result = $installer->install();
            break;
            
        case 'check':
            $result = $installer->checkInstallation();
            break;
            
        default:
            $result = ['success' => false, 'message' => 'Invalid action'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwiftMart Database Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .installer-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .status-success {
            color: #28a745;
        }
        .status-error {
            color: #dc3545;
        }
        .status-warning {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="installer-card">
                    <div class="text-center mb-4">
                        <h1 class="h3 mb-3">SwiftMart Database Installation</h1>
                        <p class="text-muted">Set up your database and get started with SwiftMart</p>
                    </div>
                    
                    <div id="status-container">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Checking installation status...</p>
                        </div>
                    </div>
                    
                    <div id="action-container" class="text-center mt-4" style="display: none;">
                        <button id="install-btn" class="btn btn-primary btn-lg me-3">
                            <i class="bi bi-download"></i> Install Database
                        </button>
                        <button id="check-btn" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Check Status
                        </button>
                    </div>
                    
                    <div id="credentials-info" class="mt-4" style="display: none;">
                        <div class="alert alert-info">
                            <h5>Default Login Credentials:</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Admin:</strong><br>
                                    Email: admin@swiftmart.com<br>
                                    Password: admin123
                                </div>
                                <div class="col-md-4">
                                    <strong>Vendor:</strong><br>
                                    Email: vendor@swiftmart.com<br>
                                    Password: vendor123
                                </div>
                                <div class="col-md-4">
                                    <strong>Customer:</strong><br>
                                    Email: customer@swiftmart.com<br>
                                    Password: customer123
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            checkInstallation();
            
            document.getElementById('install-btn').addEventListener('click', function() {
                installDatabase();
            });
            
            document.getElementById('check-btn').addEventListener('click', function() {
                checkInstallation();
            });
        });
        
        function checkInstallation() {
            showLoading();
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=check'
            })
            .then(response => response.json())
            .then(data => {
                if (data.installed) {
                    showStatus('success', 'Database is already installed and ready to use!', data);
                    document.getElementById('credentials-info').style.display = 'block';
                } else {
                    showStatus('warning', 'Database is not installed. Click "Install Database" to set it up.', data);
                }
                document.getElementById('action-container').style.display = 'block';
            })
            .catch(error => {
                showStatus('error', 'Error checking installation status: ' + error.message);
                document.getElementById('action-container').style.display = 'block';
            });
        }
        
        function installDatabase() {
            showLoading();
            document.getElementById('action-container').style.display = 'none';
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=install'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatus('success', data.message, data);
                    document.getElementById('credentials-info').style.display = 'block';
                } else {
                    showStatus('error', data.message, data);
                }
                document.getElementById('action-container').style.display = 'block';
            })
            .catch(error => {
                showStatus('error', 'Installation failed: ' + error.message);
                document.getElementById('action-container').style.display = 'block';
            });
        }
        
        function showLoading() {
            document.getElementById('status-container').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Processing...</p>
                </div>
            `;
        }
        
        function showStatus(type, message, data = null) {
            let statusClass = 'status-' + type;
            let icon = type === 'success' ? '✓' : type === 'error' ? '✗' : '⚠';
            
            let html = `
                <div class="alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning'}">
                    <h5><i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'exclamation-triangle'}"></i> ${message}</h5>
            `;
            
            if (data && data.existing_tables) {
                html += `
                    <div class="mt-3">
                        <h6>Database Status:</h6>
                        <p><strong>Existing Tables:</strong> ${data.existing_tables.join(', ')}</p>
                        ${data.missing_tables ? `<p><strong>Missing Tables:</strong> ${data.missing_tables.join(', ')}</p>` : ''}
                    </div>
                `;
            }
            
            html += '</div>';
            
            document.getElementById('status-container').innerHTML = html;
        }
    </script>
</body>
</html>
