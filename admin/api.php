<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/models.php';
require_once __DIR__ . '/../includes/auth.php';

/**
 * Admin Controller Class
 */
class AdminController {
    private $userModel;
    private $vendorModel;
    private $productModel;
    private $orderModel;
    private $categoryModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->vendorModel = new Vendor();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->categoryModel = new Category();
    }
    
    /**
     * Dashboard Statistics
     */
    public function getDashboardStats() {
        $stats = [
            'total_users' => $this->userModel->count(),
            'total_vendors' => $this->vendorModel->count(),
            'total_products' => $this->productModel->count(),
            'total_orders' => $this->orderModel->count(),
            'pending_vendors' => $this->vendorModel->count(['status' => 'pending']),
            'active_products' => $this->productModel->count(['is_active' => 1]),
            'recent_orders' => $this->getRecentOrders(10),
            'top_vendors' => $this->getTopVendors(5),
            'sales_data' => $this->getSalesData()
        ];
        
        return $stats;
    }
    
    /**
     * User Management
     */
    public function getUsers(int $page = 1, int $limit = 20, string $search = '', string $role = '') {
        $offset = ($page - 1) * $limit;
        $conditions = [];
        
        if ($role) {
            $conditions['role'] = $role;
        }
        
        if ($search) {
            $sql = "SELECT * FROM users WHERE 
                    (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)";
            $params = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
            
            if ($role) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->userModel->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll();
        } else {
            $users = $this->userModel->findAll($conditions, $limit, $offset, 'created_at DESC');
        }
        
        $total = $this->userModel->count($conditions);
        
        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    public function updateUserStatus(int $userId, string $status) {
        if (!in_array($status, ['active', 'inactive', 'suspended'])) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        $success = $this->userModel->update($userId, ['status' => $status]);
        
        if ($success) {
            return ['success' => true, 'message' => 'User status updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update user status'];
        }
    }
    
    /**
     * Vendor Management
     */
    public function getVendors(int $page = 1, int $limit = 20, string $status = '') {
        $offset = ($page - 1) * $limit;
        $conditions = [];
        
        if ($status) {
            $conditions['status'] = $status;
        }
        
        $sql = "SELECT v.*, u.first_name, u.last_name, u.email, u.username 
                FROM vendors v 
                LEFT JOIN users u ON v.user_id = u.id";
        
        if ($status) {
            $sql .= " WHERE v.status = ?";
        }
        
        $sql .= " ORDER BY v.created_at DESC LIMIT ? OFFSET ?";
        
        $params = [];
        if ($status) {
            $params[] = $status;
        }
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->vendorModel->prepare($sql);
        $stmt->execute($params);
        $vendors = $stmt->fetchAll();
        
        $total = $this->vendorModel->count($conditions);
        
        return [
            'vendors' => $vendors,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    public function approveVendor(int $vendorId) {
        $success = $this->vendorModel->approveVendor($vendorId);
        
        if ($success) {
            return ['success' => true, 'message' => 'Vendor approved successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to approve vendor'];
        }
    }
    
    public function rejectVendor(int $vendorId) {
        $success = $this->vendorModel->rejectVendor($vendorId);
        
        if ($success) {
            return ['success' => true, 'message' => 'Vendor rejected successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to reject vendor'];
        }
    }
    
    /**
     * Product Management
     */
    public function getProducts(int $page = 1, int $limit = 20, string $search = '', int $vendorId = 0) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT p.*, c.name as category_name, v.business_name as vendor_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN vendors v ON p.vendor_id = v.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($vendorId) {
            $sql .= " AND p.vendor_id = ?";
            $params[] = $vendorId;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->productModel->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM products p WHERE 1=1";
        $countParams = [];
        
        if ($search) {
            $countSql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $countParams[] = "%{$search}%";
            $countParams[] = "%{$search}%";
        }
        
        if ($vendorId) {
            $countSql .= " AND p.vendor_id = ?";
            $countParams[] = $vendorId;
        }
        
        $stmt = $this->productModel->prepare($countSql);
        $stmt->execute($countParams);
        $total = $stmt->fetch()['total'];
        
        return [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    public function updateProductStatus(int $productId, bool $isActive) {
        $success = $this->productModel->update($productId, ['is_active' => $isActive ? 1 : 0]);
        
        if ($success) {
            return ['success' => true, 'message' => 'Product status updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update product status'];
        }
    }
    
    /**
     * Order Management
     */
    public function getOrders(int $page = 1, int $limit = 20, string $status = '') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email, v.business_name as vendor_name 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                LEFT JOIN vendors v ON o.vendor_id = v.id";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->orderModel->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM orders o";
        $countParams = [];
        
        if ($status) {
            $countSql .= " WHERE o.status = ?";
            $countParams[] = $status;
        }
        
        $stmt = $this->orderModel->prepare($countSql);
        $stmt->execute($countParams);
        $total = $stmt->fetch()['total'];
        
        return [
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    public function updateOrderStatus(int $orderId, string $status) {
        $allowedStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        
        if (!in_array($status, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        $success = $this->orderModel->updateStatus($orderId, $status);
        
        if ($success) {
            return ['success' => true, 'message' => 'Order status updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update order status'];
        }
    }
    
    /**
     * Category Management
     */
    public function getCategories() {
        return $this->categoryModel->getActiveCategories();
    }
    
    public function createCategory(array $categoryData) {
        $errors = [];
        
        if (empty($categoryData['name'])) {
            $errors[] = 'Category name is required';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $categoryId = $this->categoryModel->createCategory($categoryData);
            return ['success' => true, 'category_id' => $categoryId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateCategory(int $categoryId, array $categoryData) {
        $success = $this->categoryModel->update($categoryId, $categoryData);
        
        if ($success) {
            return ['success' => true, 'message' => 'Category updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update category'];
        }
    }
    
    public function deleteCategory(int $categoryId) {
        // Check if category has products
        $productCount = $this->productModel->count(['category_id' => $categoryId]);
        
        if ($productCount > 0) {
            return ['success' => false, 'message' => 'Cannot delete category with existing products'];
        }
        
        $success = $this->categoryModel->delete($categoryId);
        
        if ($success) {
            return ['success' => true, 'message' => 'Category deleted successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete category'];
        }
    }
    
    /**
     * Private helper methods
     */
    private function getRecentOrders(int $limit) {
        $sql = "SELECT o.*, u.first_name, u.last_name, v.business_name as vendor_name 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                LEFT JOIN vendors v ON o.vendor_id = v.id 
                ORDER BY o.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->orderModel->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    private function getTopVendors(int $limit) {
        $sql = "SELECT v.*, u.first_name, u.last_name, COUNT(o.id) as order_count, SUM(o.total_amount) as total_sales 
                FROM vendors v 
                LEFT JOIN users u ON v.user_id = u.id 
                LEFT JOIN orders o ON v.id = o.vendor_id 
                WHERE v.status = 'approved' 
                GROUP BY v.id 
                ORDER BY total_sales DESC 
                LIMIT ?";
        
        $stmt = $this->vendorModel->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    private function getSalesData() {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as order_count,
                    SUM(total_amount) as total_sales
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $stmt = $this->orderModel->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

/**
 * Admin API Handler
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Check if user is admin
    if (!is_admin()) {
        json_response(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    
    $controller = new AdminController();
    $action = $_POST['action'];
    
    try {
        switch ($action) {
            case 'dashboard_stats':
                $result = $controller->getDashboardStats();
                break;
                
            case 'get_users':
                $page = (int)($_POST['page'] ?? 1);
                $limit = (int)($_POST['limit'] ?? 20);
                $search = $_POST['search'] ?? '';
                $role = $_POST['role'] ?? '';
                $result = $controller->getUsers($page, $limit, $search, $role);
                break;
                
            case 'update_user_status':
                $userId = (int)$_POST['user_id'];
                $status = $_POST['status'];
                $result = $controller->updateUserStatus($userId, $status);
                break;
                
            case 'get_vendors':
                $page = (int)($_POST['page'] ?? 1);
                $limit = (int)($_POST['limit'] ?? 20);
                $status = $_POST['status'] ?? '';
                $result = $controller->getVendors($page, $limit, $status);
                break;
                
            case 'approve_vendor':
                $vendorId = (int)$_POST['vendor_id'];
                $result = $controller->approveVendor($vendorId);
                break;
                
            case 'reject_vendor':
                $vendorId = (int)$_POST['vendor_id'];
                $result = $controller->rejectVendor($vendorId);
                break;
                
            case 'get_products':
                $page = (int)($_POST['page'] ?? 1);
                $limit = (int)($_POST['limit'] ?? 20);
                $search = $_POST['search'] ?? '';
                $vendorId = (int)($_POST['vendor_id'] ?? 0);
                $result = $controller->getProducts($page, $limit, $search, $vendorId);
                break;
                
            case 'update_product_status':
                $productId = (int)$_POST['product_id'];
                $isActive = (bool)$_POST['is_active'];
                $result = $controller->updateProductStatus($productId, $isActive);
                break;
                
            case 'get_orders':
                $page = (int)($_POST['page'] ?? 1);
                $limit = (int)($_POST['limit'] ?? 20);
                $status = $_POST['status'] ?? '';
                $result = $controller->getOrders($page, $limit, $status);
                break;
                
            case 'update_order_status':
                $orderId = (int)$_POST['order_id'];
                $status = $_POST['status'];
                $result = $controller->updateOrderStatus($orderId, $status);
                break;
                
            case 'get_categories':
                $result = $controller->getCategories();
                break;
                
            case 'create_category':
                $categoryData = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'] ?? '',
                    'icon' => $_POST['icon'] ?? '',
                    'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null
                ];
                $result = $controller->createCategory($categoryData);
                break;
                
            case 'update_category':
                $categoryId = (int)$_POST['category_id'];
                $categoryData = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'] ?? '',
                    'icon' => $_POST['icon'] ?? '',
                    'is_active' => (bool)$_POST['is_active']
                ];
                $result = $controller->updateCategory($categoryId, $categoryData);
                break;
                
            case 'delete_category':
                $categoryId = (int)$_POST['category_id'];
                $result = $controller->deleteCategory($categoryId);
                break;
                
            default:
                $result = ['success' => false, 'message' => 'Invalid action'];
        }
        
        json_response($result);
        
    } catch (Exception $e) {
        json_response(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

?>
