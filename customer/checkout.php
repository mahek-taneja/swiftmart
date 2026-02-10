<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('Checkout – SwiftMart'); ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container py-4">
        <div class="row g-4">

            <!-- LEFT -->
            <div class="col-lg-8">
                <h4 class="mb-3">Checkout</h4>

                <form id="checkout-form" class="card border-0 shadow-sm">
                    <div class="card-body">

                        <!-- DELIVERY -->
                        <h5>Delivery Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" pattern="[0-9]{10}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Postal Code</label>
                                <input type="text" class="form-control" name="postal" pattern="[0-9]{5,6}" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- PAYMENT METHOD -->
                        <h5>Payment Method</h5>
                        <div class="btn-group w-100 mb-3">
                            <input type="radio" class="btn-check" name="payment_method" id="pm_card" value="card"
                                checked>
                            <label class="btn btn-outline-primary" for="pm_card">Card</label>

                            <input type="radio" class="btn-check" name="payment_method" id="pm_upi" value="upi">
                            <label class="btn btn-outline-primary" for="pm_upi">UPI</label>

                            <input type="radio" class="btn-check" name="payment_method" id="pm_online" value="online">
                            <label class="btn btn-outline-primary" for="pm_online">Online</label>
                        </div>

                        <!-- CARD -->
                        <div id="cardForm">
                            <div class="alert alert-info small">
                                Dummy Card: <code>4111 1111 1111 1111</code>
                            </div>
                            <input class="form-control mb-2" placeholder="Cardholder Name">
                            <input class="form-control mb-2" placeholder="Card Number">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" placeholder="MM/YY">
                                </div>
                                <div class="col">
                                    <input class="form-control" placeholder="CVV">
                                </div>
                            </div>
                        </div>

                        <!-- UPI -->
                        <div id="upiForm" class="d-none">
                            <div class="row g-3">
                                <div class="col-md-6 text-center">
                                    <div class="border rounded p-3 bg-light">
                                        <p class="fw-semibold mb-2">Scan Dummy UPI QR</p>
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=upi://pay?pa=dummy@upi&pn=SwiftMart"
                                            class="img-fluid">
                                        <small class="text-muted d-block mt-2">Demo only</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">UPI ID</label>
                                    <input type="text" class="form-control form-control-lg" id="upi_id"
                                        placeholder="dummy@upi">
                                    <div class="alert alert-info small mt-2">
                                        Any valid UPI format works (demo)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ONLINE -->
                        <div id="onlineForm" class="d-none">
                            <label class="form-label">Payment Provider</label>
                            <select class="form-select">
                                <option value="">Choose provider</option>
                                <option>Razorpay</option>
                                <option>Paytm</option>
                                <option>PhonePe</option>
                                <option>Google Pay</option>
                            </select>
                        </div>

                        <hr class="my-4">

                        <button type="submit" id="placeOrderBtn" class="btn btn-primary btn-lg w-100">
                            Pay & Place Order
                        </button>

                    </div>
                </form>
            </div>

            <!-- RIGHT -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6>Order Summary</h6>
                        <div id="summary" class="small"></div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Total</span>
                            <strong id="total">₹0.00</strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const cardForm = document.getElementById('cardForm');
        const upiForm = document.getElementById('upiForm');
        const onlineForm = document.getElementById('onlineForm');
        const summaryEl = document.getElementById('summary');
        const totalEl = document.getElementById('total');
        const checkoutForm = document.getElementById('checkout-form');

        function switchPayment(method) {
            cardForm.classList.add('d-none');
            upiForm.classList.add('d-none');
            onlineForm.classList.add('d-none');

            if (method === 'card') cardForm.classList.remove('d-none');
            if (method === 'upi') upiForm.classList.remove('d-none');
            if (method === 'online') onlineForm.classList.remove('d-none');
        }

        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', e => {
                switchPayment(e.target.value);
            });
        });

        const selected = document.querySelector('input[name="payment_method"]:checked');
        switchPayment(selected ? selected.value : 'card');

        // --- Cart summary (connected to DeliverXCart) ---
        function formatRupees(amount) {
            const n = Number(amount) || 0;
            return '₹' + n.toFixed(2);
        }

        let checkoutTotal = 0;

        function renderSummary() {
            if (!window.DeliverXCart || !window.DeliverXCart.readCart) {
                summaryEl.innerHTML = '<div class="text-muted">Your cart is empty.</div>';
                totalEl.textContent = formatRupees(0);
                checkoutTotal = 0;
                return;
            }
            const items = window.DeliverXCart.readCart();
            let html = '';
            let total = 0;
            items.forEach(it => {
                const price = Number(it.price ?? 0);
                const qty = Number(it.qty ?? 1);
                const sub = price * qty;
                total += sub;
                html += `<div class="d-flex justify-content-between mb-1">
                            <span>${it.name} × ${qty}</span>
                            <span>${formatRupees(sub)}</span>
                         </div>`;
            });
            if (!items.length) {
                summaryEl.innerHTML = '<div class="text-muted">Your cart is empty.</div>';
                totalEl.textContent = formatRupees(0);
                checkoutTotal = 0;
                return;
            }
            summaryEl.innerHTML = html;
            totalEl.textContent = formatRupees(total);
            checkoutTotal = total;
        }

        try { renderSummary(); } catch (e) { console.error(e); }

        // --- Submit: call payment endpoint then redirect to tracking ---
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (checkoutTotal <= 0) {
                alert('Your cart is empty or total is zero.');
                return;
            }

            const formData = new FormData(checkoutForm);
            const shipping = {
                name: formData.get('name') || '',
                phone: formData.get('phone') || '',
                address: formData.get('address') || '',
                city: formData.get('city') || '',
                postal: formData.get('postal') || ''
            };

            const method = formData.get('payment_method') || 'card';
            const payload = {
                payment_method: method,
                amount: checkoutTotal,
                items: (window.DeliverXCart && window.DeliverXCart.readCart) ? window.DeliverXCart.readCart() : [],
                shipping
            };

            if (method === 'upi') {
                const upi = document.getElementById('upi_id').value.trim();
                if (!upi) {
                    alert('Please enter your UPI ID.');
                    return;
                }
                payload.upi_id = upi;
            }

            // For this dummy flow we don't strictly validate card/online; backend already simulates

            const btn = checkoutForm.querySelector('button[type="submit"]') || document.getElementById('placeOrderBtn');
            const original = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            }

            try {
                const res = await fetch('<?= build_path("/customer/payment.php") ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                // Always redirect if we have an order number, even if payment failed (demo behaviour)
                if (result.order_number) {
                    if (window.DeliverXCart && window.DeliverXCart.writeCart) {
                        window.DeliverXCart.writeCart([]);
                    }
                    const orderNumber = result.order_number;
                    const txnId = result.transaction_id || '';

                    // Optional notice for failed payments
                    if (result.payment_success === false) {
                        console.warn('Payment failed, but order was created for demo purposes.');
                    }

                    window.location.href = '<?= build_path("/customer/tracking.php") ?>?order='
                        + encodeURIComponent(orderNumber)
                        + '&txn=' + encodeURIComponent(txnId);
                    return;
                }

                // If we reach here, something went wrong and no order was created
                alert('Payment failed: ' + (result.message || 'Unknown error'));
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = original;
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred. Please try again.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = original;
                }
            }
        });
    });
    </script>

</body>

</html>