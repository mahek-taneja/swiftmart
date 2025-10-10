<?php
require_once __DIR__ . '/../includes/config.php';

/**
 * Base Model Class
 */
abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function find(int $id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findAll(array $conditions = [], int $limit = null, int $offset = 0, string $orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function create(array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    public function update(int $id, array $data) {
        $setClause = [];
        foreach ($data as $column => $value) {
            $setClause[] = "{$column} = :{$column}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
    }
    
    public function delete(int $id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function count(array $conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }
}

/**
 * User Model
 */
class User extends Model {
    protected $table = 'users';
    
    public function findByEmail(string $email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function findByUsername(string $username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function createUser(array $userData) {
        $userData['password_hash'] = hash_password($userData['password']);
        unset($userData['password']);
        
        return $this->create($userData);
    }
    
    public function verifyLogin(string $email, string $password) {
        $user = $this->findByEmail($email);
        
        if ($user && verify_password($password, $user['password_hash'])) {
            return $user;
        }
        
        return false;
    }
    
    public function updateLastLogin(int $userId) {
        $sql = "UPDATE {$this->table} SET updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    public function getUsersByRole(string $role, int $limit = null, int $offset = 0) {
        return $this->findAll(['role' => $role], $limit, $offset, 'created_at DESC');
    }
}

/**
 * Vendor Model
 */
class Vendor extends Model {
    protected $table = 'vendors';
    
    public function findByUserId(int $userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function getApprovedVendors(int $limit = null, int $offset = 0) {
        return $this->findAll(['status' => 'approved'], $limit, $offset, 'created_at DESC');
    }
    
    public function getPendingVendors(int $limit = null, int $offset = 0) {
        return $this->findAll(['status' => 'pending'], $limit, $offset, 'created_at DESC');
    }
    
    public function approveVendor(int $vendorId) {
        return $this->update($vendorId, ['status' => 'approved']);
    }
    
    public function rejectVendor(int $vendorId) {
        return $this->update($vendorId, ['status' => 'rejected']);
    }
    
    public function suspendVendor(int $vendorId) {
        return $this->update($vendorId, ['status' => 'suspended']);
    }
    
    public function updateRating(int $vendorId, float $rating) {
        $sql = "UPDATE {$this->table} SET rating = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$rating, $vendorId]);
    }
    
    public function incrementOrderCount(int $vendorId) {
        $sql = "UPDATE {$this->table} SET total_orders = total_orders + 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$vendorId]);
    }
}

/**
 * Category Model
 */
class Category extends Model {
    protected $table = 'categories';
    
    public function findBySlug(string $slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    public function getActiveCategories() {
        return $this->findAll(['is_active' => 1], null, 0, 'sort_order ASC, name ASC');
    }
    
    public function getParentCategories() {
        return $this->findAll(['parent_id' => null, 'is_active' => 1], null, 0, 'sort_order ASC, name ASC');
    }
    
    public function getSubCategories(int $parentId) {
        return $this->findAll(['parent_id' => $parentId, 'is_active' => 1], null, 0, 'sort_order ASC, name ASC');
    }
    
    public function createCategory(array $categoryData) {
        if (!isset($categoryData['slug'])) {
            $categoryData['slug'] = $this->generateSlug($categoryData['name']);
        }
        
        return $this->create($categoryData);
    }
    
    private function generateSlug(string $name) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->findBySlug($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}

/**
 * Product Model
 */
class Product extends Model {
    protected $table = 'products';
    
    public function findBySlug(string $slug) {
        $sql = "SELECT p.*, c.name as category_name, v.business_name as vendor_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN vendors v ON p.vendor_id = v.id 
                WHERE p.slug = ? AND p.is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    public function getActiveProducts(int $limit = null, int $offset = 0) {
        $sql = "SELECT p.*, c.name as category_name, v.business_name as vendor_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN vendors v ON p.vendor_id = v.id 
                WHERE p.is_active = 1 
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getProductsByCategory(int $categoryId, int $limit = null, int $offset = 0) {
        $sql = "SELECT p.*, c.name as category_name, v.business_name as vendor_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN vendors v ON p.vendor_id = v.id 
                WHERE p.category_id = ? AND p.is_active = 1 
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
    
    public function getProductsByVendor(int $vendorId, int $limit = null, int $offset = 0) {
        return $this->findAll(['vendor_id' => $vendorId, 'is_active' => 1], $limit, $offset, 'created_at DESC');
    }
    
    public function getFeaturedProducts(int $limit = 10) {
        $sql = "SELECT p.*, c.name as category_name, v.business_name as vendor_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN vendors v ON p.vendor_id = v.id 
                WHERE p.is_featured = 1 AND p.is_active = 1 
                ORDER BY p.rating DESC, p.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function searchProducts(string $query, int $limit = null, int $offset = 0) {
        $sql = "SELECT p.*, c.name as category_name, v.business_name as vendor_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN vendors v ON p.vendor_id = v.id 
                WHERE p.is_active = 1 AND (
                    p.name LIKE ? OR 
                    p.description LIKE ? OR 
                    p.short_description LIKE ? OR
                    c.name LIKE ?
                ) 
                ORDER BY p.rating DESC, p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $searchTerm = "%{$query}%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    public function updateStock(int $productId, int $quantity) {
        $sql = "UPDATE {$this->table} SET stock_quantity = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantity, $productId]);
    }
    
    public function decrementStock(int $productId, int $quantity) {
        $sql = "UPDATE {$this->table} SET stock_quantity = stock_quantity - ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantity, $productId]);
    }
    
    public function updateRating(int $productId, float $rating) {
        $sql = "UPDATE {$this->table} SET rating = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$rating, $productId]);
    }
}

/**
 * Order Model
 */
class Order extends Model {
    protected $table = 'orders';
    
    public function findByOrderNumber(string $orderNumber) {
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email, v.business_name as vendor_name 
                FROM {$this->table} o 
                LEFT JOIN users u ON o.user_id = u.id 
                LEFT JOIN vendors v ON o.vendor_id = v.id 
                WHERE o.order_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderNumber]);
        return $stmt->fetch();
    }
    
    public function getOrdersByUser(int $userId, int $limit = null, int $offset = 0) {
        $sql = "SELECT o.*, v.business_name as vendor_name 
                FROM {$this->table} o 
                LEFT JOIN vendors v ON o.vendor_id = v.id 
                WHERE o.user_id = ? 
                ORDER BY o.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getOrdersByVendor(int $vendorId, int $limit = null, int $offset = 0) {
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email 
                FROM {$this->table} o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.vendor_id = ? 
                ORDER BY o.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vendorId]);
        return $stmt->fetchAll();
    }
    
    public function getOrderItems(int $orderId) {
        $sql = "SELECT oi.*, p.name as product_name, p.image_url 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
    
    public function createOrder(array $orderData, array $items) {
        $this->db->beginTransaction();
        
        try {
            $orderId = $this->create($orderData);
            
            foreach ($items as $item) {
                $item['order_id'] = $orderId;
                $sql = "INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $item['order_id'],
                    $item['product_id'],
                    $item['product_name'],
                    $item['product_sku'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['total_price']
                ]);
            }
            
            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function updateStatus(int $orderId, string $status) {
        return $this->update($orderId, ['status' => $status]);
    }
}

/**
 * Cart Model
 */
class Cart extends Model {
    protected $table = 'cart';
    
    public function getCartItems(int $userId = null, string $sessionId = null) {
        $sql = "SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
                FROM {$this->table} c 
                LEFT JOIN products p ON c.product_id = p.id 
                WHERE ";
        
        $params = [];
        if ($userId) {
            $sql .= "c.user_id = ?";
            $params[] = $userId;
        } else {
            $sql .= "c.session_id = ?";
            $params[] = $sessionId;
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function addToCart(int $productId, int $quantity, int $userId = null, string $sessionId = null) {
        $existingItem = $this->findExistingItem($productId, $userId, $sessionId);
        
        if ($existingItem) {
            return $this->updateQuantity($existingItem['id'], $existingItem['quantity'] + $quantity);
        } else {
            $data = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'user_id' => $userId,
                'session_id' => $sessionId
            ];
            return $this->create($data);
        }
    }
    
    public function updateQuantity(int $cartId, int $quantity) {
        if ($quantity <= 0) {
            return $this->delete($cartId);
        }
        return $this->update($cartId, ['quantity' => $quantity]);
    }
    
    public function removeFromCart(int $cartId) {
        return $this->delete($cartId);
    }
    
    public function clearCart(int $userId = null, string $sessionId = null) {
        $sql = "DELETE FROM {$this->table} WHERE ";
        
        if ($userId) {
            $sql .= "user_id = ?";
            $params = [$userId];
        } else {
            $sql .= "session_id = ?";
            $params = [$sessionId];
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    private function findExistingItem(int $productId, int $userId = null, string $sessionId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = ? AND ";
        
        if ($userId) {
            $sql .= "user_id = ?";
            $params = [$productId, $userId];
        } else {
            $sql .= "session_id = ?";
            $params = [$productId, $sessionId];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}

/**
 * Review Model
 */
class Review extends Model {
    protected $table = 'reviews';
    
    public function getProductReviews(int $productId, int $limit = null, int $offset = 0) {
        $sql = "SELECT r.*, u.first_name, u.last_name 
                FROM {$this->table} r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = ? AND r.is_approved = 1 
                ORDER BY r.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    public function getAverageRating(int $productId) {
        $sql = "SELECT AVG(rating) as average_rating, COUNT(*) as review_count 
                FROM {$this->table} 
                WHERE product_id = ? AND is_approved = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }
    
    public function getUserReview(int $userId, int $productId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $productId]);
        return $stmt->fetch();
    }
}

?>
