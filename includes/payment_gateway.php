<?php
/**
 * Dummy Payment Gateway for SwiftMart
 * Simulates payment processing for development/testing
 */

class DummyPaymentGateway {
    private $apiKey;
    private $apiSecret;
    
    public function __construct($apiKey = 'dummy_key_12345', $apiSecret = 'dummy_secret_67890') {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }
    
    /**
     * Process payment
     * @param array $paymentData Payment information
     * @return array Payment result
     */
    public function processPayment($paymentData) {
        // Simulate API delay
        usleep(500000); // 0.5 seconds
        
        $paymentMethod = strtolower($paymentData['payment_method'] ?? 'card');
        $amount = floatval($paymentData['amount'] ?? 0);
        
        // Validate amount
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid payment amount.',
                'transaction_id' => null,
                'error_code' => 'INVALID_AMOUNT'
            ];
        }
        
        // Route to appropriate payment method handler
        switch ($paymentMethod) {
            case 'card':
                return $this->processCardPayment($paymentData, $amount);
            case 'upi':
                return $this->processUPIPayment($paymentData, $amount);
            case 'wallet':
            case 'netbanking':
            case 'online':
                return $this->processOnlinePayment($paymentData, $amount);
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid payment method selected.',
                    'transaction_id' => null,
                    'error_code' => 'INVALID_METHOD'
                ];
        }
    }
    
    /**
     * Process Card Payment
     */
    private function processCardPayment($paymentData, $amount) {
        $cardNumber = preg_replace('/\s+/', '', $paymentData['card_number'] ?? '');
        $cvv = $paymentData['cvv'] ?? '';
        $expiry = $paymentData['expiry'] ?? '';
        
        if (empty($cardNumber) || empty($cvv) || empty($expiry)) {
            return [
                'success' => false,
                'message' => 'Card details are required.',
                'transaction_id' => null,
                'error_code' => 'INVALID_DATA'
            ];
        }
        
        // Test card that fails
        if (strpos($cardNumber, '4000000000000002') === 0) {
            return [
                'success' => false,
                'message' => 'Payment declined. Insufficient funds.',
                'transaction_id' => null,
                'error_code' => 'INSUFFICIENT_FUNDS'
            ];
        }
        
        // Validate expiry
        $expiryParts = explode('/', $expiry);
        if (count($expiryParts) !== 2) {
            return [
                'success' => false,
                'message' => 'Invalid expiry date format.',
                'transaction_id' => null,
                'error_code' => 'INVALID_EXPIRY'
            ];
        }
        
        $month = intval($expiryParts[0]);
        $year = intval($expiryParts[1]);
        $currentYear = intval(date('y'));
        $currentMonth = intval(date('m'));
        
        if ($year < $currentYear || ($year == $currentYear && $month < $currentMonth)) {
            return [
                'success' => false,
                'message' => 'Card has expired.',
                'transaction_id' => null,
                'error_code' => 'EXPIRED_CARD'
            ];
        }
        
        // Validate CVV
        if (strlen($cvv) < 3 || strlen($cvv) > 4 || !is_numeric($cvv)) {
            return [
                'success' => false,
                'message' => 'Invalid CVV code.',
                'transaction_id' => null,
                'error_code' => 'INVALID_CVV'
            ];
        }
        
        $transactionId = 'TXN' . date('YmdHis') . rand(1000, 9999);
        
        // 95% success rate
        $random = rand(1, 100);
        if ($random <= 5) {
            return [
                'success' => false,
                'message' => 'Payment processing failed. Please try again.',
                'transaction_id' => null,
                'error_code' => 'PROCESSING_ERROR'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Payment processed successfully.',
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => 'INR',
            'timestamp' => date('Y-m-d H:i:s'),
            'payment_method' => 'card',
            'card_last4' => substr($cardNumber, -4)
        ];
    }
    
    /**
     * Process UPI Payment
     */
    private function processUPIPayment($paymentData, $amount) {
        $upiId = trim($paymentData['upi_id'] ?? '');
        
        if (empty($upiId)) {
            return [
                'success' => false,
                'message' => 'UPI ID is required.',
                'transaction_id' => null,
                'error_code' => 'INVALID_UPI'
            ];
        }
        
        // Validate UPI format (name@paytm, name@phonepe, name@ybl, etc.)
        if (!preg_match('/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/i', $upiId)) {
            return [
                'success' => false,
                'message' => 'Invalid UPI ID format. Use format: name@paytm or name@phonepe',
                'transaction_id' => null,
                'error_code' => 'INVALID_UPI_FORMAT'
            ];
        }
        
        // Test UPI that fails
        if (stripos($upiId, 'fail@') === 0) {
            return [
                'success' => false,
                'message' => 'UPI payment failed. Please try again.',
                'transaction_id' => null,
                'error_code' => 'UPI_FAILED'
            ];
        }
        
        $transactionId = 'UPI' . date('YmdHis') . rand(1000, 9999);
        
        // 98% success rate for UPI
        $random = rand(1, 100);
        if ($random <= 2) {
            return [
                'success' => false,
                'message' => 'UPI transaction timed out. Please try again.',
                'transaction_id' => null,
                'error_code' => 'UPI_TIMEOUT'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'UPI payment processed successfully.',
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => 'INR',
            'timestamp' => date('Y-m-d H:i:s'),
            'payment_method' => 'upi',
            'upi_id' => $upiId
        ];
    }
    
    /**
     * Process Online Payment (NetBanking, Wallet, etc.)
     */
    private function processOnlinePayment($paymentData, $amount) {
        $provider = strtolower($paymentData['online_provider'] ?? '');
        
        if (empty($provider)) {
            return [
                'success' => false,
                'message' => 'Payment provider is required.',
                'transaction_id' => null,
                'error_code' => 'INVALID_PROVIDER'
            ];
        }
        
        // Validate provider
        $allowedProviders = ['razorpay', 'phonepe', 'paytm', 'amazonpay', 'googlepay'];
        if (!in_array($provider, $allowedProviders)) {
            return [
                'success' => false,
                'message' => 'Invalid payment provider selected.',
                'transaction_id' => null,
                'error_code' => 'INVALID_PROVIDER'
            ];
        }
        
        // Test provider that fails
        if ($provider === 'fail') {
            return [
                'success' => false,
                'message' => 'Online payment failed. Please try again.',
                'transaction_id' => null,
                'error_code' => 'ONLINE_FAILED'
            ];
        }
        
        $transactionId = 'ONL' . date('YmdHis') . rand(1000, 9999);
        
        // 96% success rate
        $random = rand(1, 100);
        if ($random <= 4) {
            return [
                'success' => false,
                'message' => 'Payment gateway error. Please try again.',
                'transaction_id' => null,
                'error_code' => 'GATEWAY_ERROR'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Online payment processed successfully.',
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => 'INR',
            'timestamp' => date('Y-m-d H:i:s'),
            'payment_method' => 'online',
            'provider' => $provider
        ];
    }
    
    /**
     * Verify transaction
     * @param string $transactionId
     * @return array
     */
    public function verifyTransaction($transactionId) {
        // In real scenario, this would check with payment provider
        if (preg_match('/^TXN\d+$/', $transactionId)) {
            return [
                'success' => true,
                'status' => 'completed',
                'transaction_id' => $transactionId
            ];
        }
        
        return [
            'success' => false,
            'status' => 'invalid',
            'message' => 'Transaction not found.'
        ];
    }
    
    /**
     * Refund payment
     * @param string $transactionId
     * @param float $amount
     * @return array
     */
    public function refundPayment($transactionId, $amount = null) {
        usleep(500000); // Simulate API delay
        
        if (preg_match('/^TXN\d+$/', $transactionId)) {
            return [
                'success' => true,
                'refund_id' => 'REF' . date('YmdHis') . rand(1000, 9999),
                'amount' => $amount,
                'message' => 'Refund processed successfully.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Invalid transaction ID.'
        ];
    }
    
    /**
     * Get test card numbers for demo
     * @return array
     */
    public static function getTestCards() {
        return [
            [
                'number' => '4111 1111 1111 1111',
                'name' => 'Visa - Success',
                'expiry' => '12/25',
                'cvv' => '123'
            ],
            [
                'number' => '5555 5555 5555 4444',
                'name' => 'Mastercard - Success',
                'expiry' => '12/25',
                'cvv' => '123'
            ],
            [
                'number' => '4000 0000 0000 0002',
                'name' => 'Visa - Decline',
                'expiry' => '12/25',
                'cvv' => '123'
            ]
        ];
    }
    
    /**
     * Get test UPI IDs for demo
     * @return array
     */
    public static function getTestUPI() {
        return [
            ['upi_id' => 'success@paytm', 'name' => 'Paytm - Success'],
            ['upi_id' => 'success@phonepe', 'name' => 'PhonePe - Success'],
            ['upi_id' => 'success@ybl', 'name' => 'Yes Bank - Success'],
            ['upi_id' => 'fail@paytm', 'name' => 'Paytm - Fail (Test)']
        ];
    }
    
    /**
     * Get available online payment providers
     * @return array
     */
    public static function getOnlineProviders() {
        return [
            'razorpay' => 'Razorpay',
            'phonepe' => 'PhonePe',
            'paytm' => 'Paytm',
            'amazonpay' => 'Amazon Pay',
            'googlepay' => 'Google Pay'
        ];
    }
}

