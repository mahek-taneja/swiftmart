<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/payment_gateway.php';

$error = '';
$vendor_data = [];

// Redirect if already logged in as vendor
if (is_vendor()) {
    redirect(build_path('/vendor/dashboard.php'));
    exit;
}

// Check if vendor registration data is in session
if (!isset($_SESSION['vendor_registration'])) {
    // If no registration data, redirect to register
    redirect(build_path('/vendor/register.php'));
    exit;
}

$vendor_data = $_SESSION['vendor_registration'];

// Handle subscription selection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_type = $_POST['plan_type'] ?? '';
    $amount = 0;
    $duration_days = 0;
    
    if ($plan_type === 'monthly') {
        $amount = 3000;
        $duration_days = 30;
    } elseif ($plan_type === 'yearly') {
        $amount = 30000;
        $duration_days = 365;
    } else {
        $error = "Please select a subscription plan.";
    }
    
        // Payment processing
        $paymentSuccess = false;
        $transactionId = null;
        
        if (!$error && isset($_POST['process_payment']) && $_POST['process_payment'] === '1') {
            $paymentMethod = strtolower($_POST['payment_method'] ?? 'card');
            
            $paymentData = [
                'payment_method' => $paymentMethod,
                'amount' => $amount
            ];
            
            // Add method-specific data
            if ($paymentMethod === 'card') {
                $paymentData['card_number'] = $_POST['card_number'] ?? '';
                $paymentData['expiry'] = $_POST['expiry'] ?? '';
                $paymentData['cvv'] = $_POST['cvv'] ?? '';
                $paymentData['cardholder_name'] = $_POST['cardholder_name'] ?? '';
            } elseif ($paymentMethod === 'upi') {
                $paymentData['upi_id'] = $_POST['upi_id'] ?? '';
            } elseif ($paymentMethod === 'online') {
                $paymentData['online_provider'] = $_POST['online_provider'] ?? '';
            }
            
            $gateway = new DummyPaymentGateway();
            $paymentResult = $gateway->processPayment($paymentData);
            
            if ($paymentResult['success']) {
                $paymentSuccess = true;
                $transactionId = $paymentResult['transaction_id'];
            } else {
                $error = "Payment failed: " . $paymentResult['message'];
            }
        }
    
    if (!$error && $paymentSuccess) {
        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            
            // Create user account
            $stmt = $db->prepare("
                INSERT INTO users (username, email, password_hash, first_name, last_name, role)
                VALUES (?, ?, ?, ?, ?, 'vendor')
            ");
            $username = strtolower(str_replace(' ', '', $vendor_data['name']));
            $stmt->execute([
                $username,
                $vendor_data['email'],
                password_hash($vendor_data['password'], PASSWORD_DEFAULT),
                explode(' ', $vendor_data['name'])[0] ?? $vendor_data['name'],
                explode(' ', $vendor_data['name'])[1] ?? '',
            ]);
            $user_id = $db->lastInsertId();
            
            // Create vendor record (simplified for now - vendor will complete profile later)
            $stmt = $db->prepare("
                INSERT INTO vendors (user_id, business_name, business_type, address, city, state, zip_code, country, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $user_id,
                $vendor_data['name'],
                'General',
                'Address to be updated',
                'City to be updated',
                'State to be updated',
                '00000',
                'India'
            ]);
            $vendor_id = $db->lastInsertId();
            
            // Create subscription record
            $stmt = $db->prepare("
                INSERT INTO vendor_subscriptions (vendor_id, plan_type, amount, duration_days, start_date, end_date, status, payment_method, payment_transaction_id)
                VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 'active', 'card', ?)
            ");
            $stmt->execute([
                $vendor_id,
                $plan_type,
                $amount,
                $duration_days,
                $duration_days,
                $transactionId
            ]);
            
            $db->commit();
            
            // Clear registration session
            unset($_SESSION['vendor_registration']);
            
            // Set success message and redirect to login
            $_SESSION['subscription_success'] = "Subscription successful! Please login to continue.";
            redirect(build_path('/vendor/login.php'));
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error processing subscription: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php render_head('Vendor Subscription Plans'); ?>
    <style>
        .pricing-card {
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .pricing-card.selected {
            border: 3px solid #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .plan-price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
        }
        .plan-badge {
            position: absolute;
            top: 15px;
            right: 15px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold mb-3">Choose Your Subscription Plan</h1>
                    <p class="lead text-muted">Select a plan that works best for your business</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post" id="subscriptionForm">
                    <div class="row g-4 mb-4">
                        <!-- Monthly Plan -->
                        <div class="col-md-6">
                            <div class="card pricing-card h-100 position-relative" onclick="selectPlan('monthly')">
                                <div class="card-body p-4 text-center">
                                    <div class="plan-badge">
                                        <span class="badge bg-primary">Popular</span>
                                    </div>
                                    <h3 class="card-title mb-3">Monthly Plan</h3>
                                    <div class="plan-price mb-2">₹3,000</div>
                                    <p class="text-muted mb-4">per month</p>
                                    <ul class="list-unstyled text-start mb-4">
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Access to vendor dashboard</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Unlimited products</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Order management</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Sales analytics</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>AI forecasting</li>
                                    </ul>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="radio" name="plan_type" id="plan_monthly" value="monthly" required>
                                        <label class="form-check-label ms-2" for="plan_monthly">
                                            <strong>Select Monthly Plan</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Yearly Plan -->
                        <div class="col-md-6">
                            <div class="card pricing-card h-100 position-relative" onclick="selectPlan('yearly')">
                                <div class="card-body p-4 text-center">
                                    <div class="plan-badge">
                                        <span class="badge bg-success">Best Value</span>
                                    </div>
                                    <h3 class="card-title mb-3">Yearly Plan</h3>
                                    <div class="plan-price mb-2">₹30,000</div>
                                    <p class="text-muted mb-4">per year</p>
                                    <div class="alert alert-info mb-3">
                                        <strong>Save ₹6,000!</strong> (2 months free)
                                    </div>
                                    <ul class="list-unstyled text-start mb-4">
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>All Monthly features</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Priority support</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Advanced analytics</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Featured listing</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Dedicated account manager</li>
                                    </ul>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="radio" name="plan_type" id="plan_yearly" value="yearly" required>
                                        <label class="form-check-label ms-2" for="plan_yearly">
                                            <strong>Select Yearly Plan</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card bg-light">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Registration Details</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Name:</strong> <?= htmlspecialchars($vendor_data['name']) ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Email:</strong> <?= htmlspecialchars($vendor_data['email']) ?>
                                </div>
                            </div>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Note:</strong> Your account will be created after subscription. You'll receive login credentials via email.
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4" id="paymentSection" style="display: none;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-credit-card me-2"></i>Payment Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Payment Method Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Payment Method</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="payment_method" id="pm_card" value="card" checked>
                                    <label class="btn btn-outline-primary" for="pm_card">
                                        <i class="bi bi-credit-card me-2"></i>Card
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="payment_method" id="pm_upi" value="upi">
                                    <label class="btn btn-outline-primary" for="pm_upi">
                                        <i class="bi bi-phone me-2"></i>UPI
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="payment_method" id="pm_online" value="online">
                                    <label class="btn btn-outline-primary" for="pm_online">
                                        <i class="bi bi-wallet2 me-2"></i>Online
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Card Payment Form -->
                            <div id="cardPaymentForm" class="payment-form">
                                <div class="alert alert-info">
                                    <strong>Test Cards:</strong><br>
                                    Success: 4111 1111 1111 1111 or 5555 5555 5555 4444<br>
                                    Decline: 4000 0000 0000 0002<br>
                                    Use any expiry date (future) and any 3-digit CVV
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="cardholder_name" class="form-label">Cardholder Name</label>
                                        <input type="text" class="form-control" id="cardholder_name" name="cardholder_name" 
                                               placeholder="John Doe">
                                    </div>
                                    <div class="col-12">
                                        <label for="card_number" class="form-label">Card Number</label>
                                        <input type="text" class="form-control" id="card_number" name="card_number" 
                                               placeholder="4111 1111 1111 1111" maxlength="19">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="expiry" class="form-label">Expiry (MM/YY)</label>
                                        <input type="text" class="form-control" id="expiry" name="expiry" 
                                               placeholder="12/25" maxlength="5">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" name="cvv" 
                                               placeholder="123" maxlength="4">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- UPI Payment Form -->
                            <div id="upiPaymentForm" class="payment-form" style="display: none;">
                                <div class="alert alert-info">
                                    <strong>Test UPI IDs:</strong><br>
                                    Success: success@paytm, success@phonepe, success@ybl<br>
                                    Fail: fail@paytm
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="upi_id" class="form-label">UPI ID</label>
                                        <input type="text" class="form-control" id="upi_id" name="upi_id" 
                                               placeholder="yourname@paytm">
                                        <small class="text-muted">Format: name@paytm, name@phonepe, name@ybl</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Online Payment Form -->
                            <div id="onlinePaymentForm" class="payment-form" style="display: none;">
                                <div class="alert alert-info">
                                    <strong>Available Providers:</strong> Razorpay, PhonePe, Paytm, Amazon Pay, Google Pay
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="online_provider" class="form-label">Select Payment Provider</label>
                                        <select class="form-select" id="online_provider" name="online_provider">
                                            <option value="">Choose provider...</option>
                                            <option value="razorpay">Razorpay</option>
                                            <option value="phonepe">PhonePe</option>
                                            <option value="paytm">Paytm</option>
                                            <option value="amazonpay">Amazon Pay</option>
                                            <option value="googlepay">Google Pay</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="process_payment" value="1">
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" id="submitBtn" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-credit-card me-2"></i>Subscribe & Complete Registration
                        </button>
                        <a href="<?= build_path('/vendor/register.php') ?>" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="bi bi-arrow-left me-2"></i>Go Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script>
        function selectPlan(planType) {
            // Remove selected class from all cards
            document.querySelectorAll('.pricing-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.getElementById('plan_' + planType).checked = true;
        }
        
        // Handle form submission
        document.getElementById('subscriptionForm').addEventListener('submit', function(e) {
            const selectedPlan = document.querySelector('input[name="plan_type"]:checked');
            if (!selectedPlan) {
                e.preventDefault();
                alert('Please select a subscription plan.');
                return false;
            }
            
            // Show payment section if plan is selected
            if (selectedPlan && !document.getElementById('paymentSection').style.display || 
                document.getElementById('paymentSection').style.display === 'none') {
                e.preventDefault();
                document.getElementById('paymentSection').style.display = 'block';
                document.getElementById('paymentSection').scrollIntoView({ behavior: 'smooth' });
                return false;
            }
        });
        
        // Format card number
        document.getElementById('card_number')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            value = value.replace(/(.{4})/g, '$1 ').trim();
            e.target.value = value;
        });
        
        // Format expiry
        document.getElementById('expiry')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
        
        // Payment method switching
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Hide all forms
                document.querySelectorAll('.payment-form').forEach(form => {
                    form.style.display = 'none';
                });
                
                // Show selected form
                const method = this.value;
                if (method === 'card') {
                    document.getElementById('cardPaymentForm').style.display = 'block';
                } else if (method === 'upi') {
                    document.getElementById('upiPaymentForm').style.display = 'block';
                } else if (method === 'online') {
                    document.getElementById('onlinePaymentForm').style.display = 'block';
                }
            });
        });
        
        // Update form validation based on payment method
        document.getElementById('subscriptionForm').addEventListener('submit', function(e) {
            const selectedPlan = document.querySelector('input[name="plan_type"]:checked');
            if (!selectedPlan) {
                e.preventDefault();
                alert('Please select a subscription plan.');
                return false;
            }
            
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            
            if (paymentMethod === 'card') {
                const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
                const expiry = document.getElementById('expiry').value;
                const cvv = document.getElementById('cvv').value;
                
                if (!cardNumber || !expiry || !cvv) {
                    e.preventDefault();
                    alert('Please fill all card details.');
                    return false;
                }
            } else if (paymentMethod === 'upi') {
                const upiId = document.getElementById('upi_id').value.trim();
                if (!upiId) {
                    e.preventDefault();
                    alert('Please enter your UPI ID.');
                    return false;
                }
            } else if (paymentMethod === 'online') {
                const provider = document.getElementById('online_provider').value;
                if (!provider) {
                    e.preventDefault();
                    alert('Please select a payment provider.');
                    return false;
                }
            }
        });
    </script>
</body>
</html>
