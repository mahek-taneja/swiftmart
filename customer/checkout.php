<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/payment_gateway.php';

$error = '';
$success = '';
$orderId = null;

// Handle success redirect
if (isset($_GET['success']) && $_GET['success'] === '1') {
    $success = true;
    $orderId = $_GET['order'] ?? null;
    if (isset($_GET['txn']) && session_status() !== PHP_SESSION_NONE) {
        $_SESSION['last_transaction'] = [
            'transaction_id' => $_GET['txn'],
            'order_id' => $orderId
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Checkout – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <h1 class="h4 mb-3">Checkout</h1>
  <div class="row g-4">
    <div class="col-12 col-lg-7">
      <form id="checkout-form" class="card p-3">
        <h2 class="h5">Shipping Details</h2>
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" placeholder="Full Name" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Phone</label>
            <input type="tel" class="form-control" name="phone" placeholder="Phone" required>
          </div>
          <div class="col-12">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" placeholder="Address" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">City</label>
            <input type="text" class="form-control" name="city" placeholder="City" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Postal Code</label>
            <input type="text" class="form-control" name="postal" placeholder="Postal Code" required>
          </div>
        </div>
        <hr>
        <h2 class="h5">Payment</h2>
        
        <!-- Payment Method Selection -->
        <div class="mb-4">
            <label class="form-label fw-bold">Select Payment Method</label>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="payment_method" id="pm_card_checkout" value="card" checked>
                <label class="btn btn-outline-primary" for="pm_card_checkout">
                    <i class="bi bi-credit-card me-2"></i>Card
                </label>
                
                <input type="radio" class="btn-check" name="payment_method" id="pm_upi_checkout" value="upi">
                <label class="btn btn-outline-primary" for="pm_upi_checkout">
                    <i class="bi bi-phone me-2"></i>UPI
                </label>
                
                <input type="radio" class="btn-check" name="payment_method" id="pm_online_checkout" value="online">
                <label class="btn btn-outline-primary" for="pm_online_checkout">
                    <i class="bi bi-wallet2 me-2"></i>Online
                </label>
            </div>
        </div>
        
        <!-- Card Payment Form -->
        <div id="cardPaymentFormCheckout" class="payment-form-checkout">
            <div class="alert alert-info">
                <strong>Test Cards:</strong><br>
                Success: 4111 1111 1111 1111 or 5555 5555 5555 4444<br>
                Decline: 4000 0000 0000 0002<br>
                Use any expiry date (future) and any 3-digit CVV
            </div>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Cardholder Name</label>
                <input type="text" class="form-control" name="cardholder_name" id="cardholder_name" placeholder="John Doe">
              </div>
              <div class="col-12">
                <label class="form-label">Card Number</label>
                <input type="text" class="form-control" name="card_number" id="card_number" placeholder="4111 1111 1111 1111" maxlength="19">
              </div>
              <div class="col-6">
                <label class="form-label">Expiry (MM/YY)</label>
                <input type="text" class="form-control" name="expiry" id="expiry" placeholder="12/25" maxlength="5">
              </div>
              <div class="col-6">
                <label class="form-label">CVV</label>
                <input type="text" class="form-control" name="cvv" id="cvv" placeholder="123" maxlength="4">
              </div>
            </div>
        </div>
        
        <!-- UPI Payment Form -->
        <div id="upiPaymentFormCheckout" class="payment-form-checkout" style="display: none;">
            <div class="alert alert-info">
                <strong>Test UPI IDs:</strong><br>
                Success: success@paytm, success@phonepe, success@ybl<br>
                Fail: fail@paytm
            </div>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">UPI ID</label>
                <input type="text" class="form-control" name="upi_id" id="upi_id_checkout" placeholder="yourname@paytm">
                <small class="text-muted">Format: name@paytm, name@phonepe, name@ybl</small>
              </div>
            </div>
        </div>
        
        <!-- Online Payment Form -->
        <div id="onlinePaymentFormCheckout" class="payment-form-checkout" style="display: none;">
            <div class="alert alert-info">
                <strong>Available Providers:</strong> Razorpay, PhonePe, Paytm, Amazon Pay, Google Pay
            </div>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Select Payment Provider</label>
                <select class="form-select" id="online_provider_checkout" name="online_provider">
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
        <div class="mt-3 text-end">
          <button class="btn btn-primary" type="submit" id="placeOrderBtn">
            <i class="bi bi-credit-card me-2"></i>Place Order
          </button>
        </div>
      </form>
      <?php if ($error): ?>
        <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success mt-3">
          Order placed successfully! Your order ID is <strong><?= htmlspecialchars($orderId) ?></strong>.<br>
          Transaction ID: <strong><?= htmlspecialchars($_SESSION['last_transaction']['transaction_id'] ?? 'N/A') ?></strong><br>
          <a href="<?php echo build_path('/customer/tracking.php'); ?>" class="btn btn-sm btn-outline-primary mt-2">Track Order</a>
        </div>
      <?php endif; ?>
    </div>
    <div class="col-12 col-lg-5">
      <div class="card p-3">
        <h2 class="h5">Order Summary</h2>
        <div id="summary"></div>
        <hr>
        <div class="d-flex justify-content-between">
          <div>Total</div>
          <div class="fw-semibold" id="total">₹0.00</div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
  // FORCE formatPrice to use rupees - override any cached version
  window.DeliverX = window.DeliverX || {};
  window.DeliverX.formatPrice = function(cents) {
      const amount = (typeof cents === 'number' ? cents : parseFloat(cents) || 0) / 100;
      const formatted = amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      return `₹${formatted}`;
  };
  
  function renderSummary(){
    const items = window.DeliverXCart.readCart();
    let html = '';
    let total = 0;
    for(const it of items){
      const subtotal = it.price * it.qty;
      total += subtotal;
      html += `<div class="d-flex justify-content-between"><div>${it.name} × ${it.qty}</div><div>${window.DeliverX.formatPrice(subtotal)}</div></div>`;
    }
    document.getElementById('summary').innerHTML = html || '<div class="text-muted">Your cart is empty.</div>';
    document.getElementById('total').textContent = window.DeliverX.formatPrice(total);
  }
  
  document.addEventListener('DOMContentLoaded', ()=>{
    // Force override formatPrice again after all scripts load
    window.DeliverX.formatPrice = function(cents) {
        const amount = (typeof cents === 'number' ? cents : parseFloat(cents) || 0) / 100;
        const formatted = amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return `₹${formatted}`;
    };
    renderSummary();
    
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
    
    // Payment method switching for checkout
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Hide all forms
            document.querySelectorAll('.payment-form-checkout').forEach(form => {
                form.style.display = 'none';
            });
            
            // Show selected form
            const method = this.value;
            if (method === 'card') {
                document.getElementById('cardPaymentFormCheckout').style.display = 'block';
            } else if (method === 'upi') {
                document.getElementById('upiPaymentFormCheckout').style.display = 'block';
            } else if (method === 'online') {
                document.getElementById('onlinePaymentFormCheckout').style.display = 'block';
            }
        });
    });
    
    document.getElementById('checkout-form').addEventListener('submit', async (e)=>{
      e.preventDefault();
      
      const btn = document.getElementById('placeOrderBtn');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
      
      const formData = new FormData(e.target);
      const shippingData = {
        name: formData.get('name') || document.querySelector('input[placeholder*="Full Name"]')?.value || '',
        phone: formData.get('phone') || document.querySelector('input[placeholder*="Phone"]')?.value || '',
        address: formData.get('address') || document.querySelector('input[placeholder*="Address"]')?.value || '',
        city: formData.get('city') || document.querySelector('input[placeholder*="City"]')?.value || '',
        postal: formData.get('postal') || document.querySelector('input[placeholder*="Postal"]')?.value || ''
      };
      
      // Get selected payment method
      const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'card';
      const amount = parseFloat(document.getElementById('total').textContent.replace(/[₹,\s]/g, '')) || 0;
      
      if (amount <= 0) {
        alert('Invalid order amount. Please add items to cart.');
        btn.disabled = false;
        btn.innerHTML = originalText;
        return;
      }
      
      const paymentData = {
        payment_method: paymentMethod,
        amount: amount
      };
      
      // Validate and add method-specific data
      if (paymentMethod === 'card') {
        const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
        const expiry = document.getElementById('expiry').value;
        const cvv = document.getElementById('cvv').value;
        
        if (!cardNumber || !expiry || !cvv) {
          alert('Please fill all card details.');
          btn.disabled = false;
          btn.innerHTML = originalText;
          return;
        }
        
        paymentData.cardholder_name = document.getElementById('cardholder_name').value;
        paymentData.card_number = cardNumber;
        paymentData.expiry = expiry;
        paymentData.cvv = cvv;
      } else if (paymentMethod === 'upi') {
        const upiId = document.getElementById('upi_id_checkout').value.trim();
        if (!upiId) {
          alert('Please enter your UPI ID.');
          btn.disabled = false;
          btn.innerHTML = originalText;
          return;
        }
        paymentData.upi_id = upiId;
      } else if (paymentMethod === 'online') {
        const provider = document.getElementById('online_provider_checkout').value;
        if (!provider) {
          alert('Please select a payment provider.');
          btn.disabled = false;
          btn.innerHTML = originalText;
          return;
        }
        paymentData.online_provider = provider;
      }
      
      try {
        const response = await fetch('<?= build_path("/customer/payment.php") ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(paymentData)
        });
        
        const result = await response.json();
        
        if (result.success) {
          // Payment successful - clear cart and show success
          const oid = 'ORD' + Date.now();
          window.DeliverXCart.writeCart([]);
          renderSummary();
          
          // Reload page to show success message
          window.location.href = '<?= build_path("/customer/checkout.php") ?>?success=1&order=' + oid + '&txn=' + result.transaction_id;
        } else {
          alert('Payment failed: ' + result.message);
          btn.disabled = false;
          btn.innerHTML = originalText;
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    });
    
    // Handle success redirect
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
      const orderId = urlParams.get('order');
      const txnId = urlParams.get('txn');
      document.querySelector('.alert-success')?.classList.remove('d-none');
      if (orderId) {
        document.getElementById('oid')?.textContent = orderId;
      }
    }
  });
</script>

</body>
</html>


