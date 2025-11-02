<?php
/**
 * Payment Processing Page
 * Handles payment for customer orders
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/payment_gateway.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

$paymentData = [
    'card_number' => $input['card_number'] ?? '',
    'expiry' => $input['expiry'] ?? '',
    'cvv' => $input['cvv'] ?? '',
    'cardholder_name' => $input['cardholder_name'] ?? '',
    'amount' => floatval($input['amount'] ?? 0),
    'order_id' => $input['order_id'] ?? null
];

if (empty($paymentData['card_number']) || empty($paymentData['expiry']) || empty($paymentData['cvv']) || $paymentData['amount'] <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required payment information.'
    ]);
    exit;
}

$gateway = new DummyPaymentGateway();
$result = $gateway->processPayment($paymentData);

// If successful, store transaction in session/database
if ($result['success'] && isset($paymentData['order_id'])) {
    $_SESSION['last_transaction'] = [
        'transaction_id' => $result['transaction_id'],
        'order_id' => $paymentData['order_id'],
        'amount' => $result['amount'],
        'timestamp' => $result['timestamp']
    ];
}

echo json_encode($result);
exit;

