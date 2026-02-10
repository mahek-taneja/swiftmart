<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/models.php';

header('Content-Type: application/json');

$orderNumber = $_GET['order'] ?? '';
if (!$orderNumber) {
    echo json_encode(['success' => false, 'message' => 'Missing order number']);
    exit;
}

try {
    $orderModel    = new Order();
    $trackingModel = new OrderTrackingEvent();

    $order = $orderModel->findByOrderNumber($orderNumber);
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $events = $trackingModel->getEventsForOrder((int)$order['id']);

    echo json_encode([
        'success' => true,
        'order'   => $order,
        'events'  => $events,
    ]);
    exit;
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}


