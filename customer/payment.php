<?php
/**
 * Payment + Order Creation Endpoint
 * Processes payment and creates DB order + tracking events
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/payment_gateway.php';
require_once __DIR__ . '/../includes/models.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$amount        = isset($input['amount']) ? (float)$input['amount'] : 0;
$deliveryCharge = isset($input['delivery_charge']) ? (float)$input['delivery_charge'] : 0;
$paymentMethod = $input['payment_method'] ?? 'card';

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment amount.']);
    exit;
}

// Build payment data
$paymentData = [
    'payment_method'  => $paymentMethod,
    'amount'          => $amount,
    'card_number'     => $input['card_number'] ?? '',
    'expiry'          => $input['expiry'] ?? '',
    'cvv'             => $input['cvv'] ?? '',
    'cardholder_name' => $input['cardholder_name'] ?? '',
    'upi_id'          => $input['upi_id'] ?? '',
    'online_provider' => $input['online_provider'] ?? ''
];

$gateway    = new DummyPaymentGateway();
$result     = $gateway->processPayment($paymentData);
$paidStatus = $result['success'] ? 'paid' : 'failed';

// Always create order (even if payment failed) so we can redirect to tracking
try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $orderModel    = new Order();
    $trackingModel = new OrderTrackingEvent();

    $orderNumber = generate_order_number();

    $items    = $input['items'] ?? [];
    $shipping = $input['shipping'] ?? [];

    // Determine vendor from first cart item (all items assumed same vendor)
    $vendorId = null;
    if (!empty($items[0]['vendor_id'])) {
        $vendorId = (int)$items[0]['vendor_id'];
    }
    if (!$vendorId) {
        $vendorId = 1;
    }

    $userId = current_user_id() ?: 1;

    $shippingAddress = trim(
        ($shipping['name'] ?? '') . "\n" .
        ($shipping['address'] ?? '') . "\n" .
        (($shipping['city'] ?? '') . ' ' . ($shipping['postal'] ?? '')) . "\n" .
        'Phone: ' . ($shipping['phone'] ?? '')
    );

    // Calculate subtotal (total without delivery charge)
    $subtotal = $amount - $deliveryCharge;
    if ($subtotal < 0) $subtotal = 0;

    $orderData = [
        'order_number'      => $orderNumber,
        'user_id'           => $userId,
        'vendor_id'         => $vendorId,
        'status'            => 'confirmed',
        'payment_status'    => $paidStatus,
        'payment_method'    => $paymentMethod,
        'subtotal'          => $subtotal,
        'tax_amount'        => 0,
        'shipping_amount'   => $deliveryCharge,
        'discount_amount'   => 0,
        'total_amount'      => $amount,
        'currency'          => 'INR',
        'shipping_address'  => $shippingAddress,
        'billing_address'   => $shippingAddress,
        'notes'             => null,
        'tracking_number'   => 'TRK' . substr($orderNumber, 2),
        'estimated_delivery'=> date('Y-m-d H:i:s', time() + 60 * 60)
    ];

    $orderItems = array_map(function ($item) {
        $qty   = (int)($item['qty'] ?? 1);
        $price = (float)($item['price'] ?? 0);
        return [
            'product_id'   => (int)$item['id'],
            'product_name' => $item['name'] ?? '',
            'product_sku'  => null,
            'quantity'     => $qty,
            'unit_price'   => $price,
            'total_price'  => $price * $qty,
        ];
    }, $items);

    $orderId = $orderModel->createOrder($orderData, $orderItems);

    // Initial tracking events
    $trackingModel->addEvent($orderId, 'Order Placed', null, 'Your order has been placed successfully.');
    $trackingModel->addEvent($orderId, 'Preparing', null, 'Vendor is preparing your order.');

    $db->commit();

    $_SESSION['last_transaction'] = [
        'transaction_id' => $result['transaction_id'] ?? null,
        'order_id'       => $orderId,
        'order_number'   => $orderNumber,
        'amount'         => $amount,
        'timestamp'      => $result['timestamp'] ?? date('Y-m-d H:i:s'),
    ];

    // --------- DEMO SESSION ORDERS FOR VENDOR & TRACKING PAGES ---------
    if (!isset($_SESSION['demo_orders'])) {
        $_SESSION['demo_orders'] = [];
    }
    $_SESSION['demo_orders'][$orderNumber] = [
        'order_number' => $orderNumber,
        'vendor_id'    => $vendorId,
        'status'       => 'Processing',
        'eta'          => $orderData['estimated_delivery'],
        'created_at'   => date('Y-m-d H:i:s'),
    ];

    echo json_encode([
        'success'        => true,
        'payment_success'=> $result['success'],
        'message'        => $result['message'] ?? 'Order created.',
        'transaction_id' => $result['transaction_id'] ?? null,
        'order_id'       => $orderId,
        'order_number'   => $orderNumber,
    ]);
    exit;

} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    // For demo purposes still respond in a way that lets the frontend redirect
    $fallbackNumber = $orderNumber ?? ('DEMO' . time());
    echo json_encode([
        'success'         => true,
        'payment_success' => $result['success'],
        'message'         => 'Payment succeeded but order creation failed: ' . $e->getMessage(),
        'transaction_id'  => $result['transaction_id'] ?? null,
        'order_id'        => $orderId ?? null,
        'order_number'    => $fallbackNumber,
    ]);
    exit;
}
