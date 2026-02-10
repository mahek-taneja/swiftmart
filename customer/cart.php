<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../data/products.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('Your Cart – SwiftMart'); ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container py-4">
        <h1 class="h4 mb-3">Your Cart</h1>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="width:140px">Qty</th>
                        <th>Price (₹)</th>
                        <th>Subtotal (₹)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="cart-body"></tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <a class="btn btn-outline-secondary" href="<?= build_path('/customer/listings.php'); ?>">
                Continue Shopping
            </a>

            <div class="fs-5">
                Total:
                <span class="fw-semibold" id="cart-total">₹0.00</span>
            </div>
        </div>

        <div class="mt-3 text-end">
            <a class="btn btn-primary" href="<?= build_path('/customer/checkout.php'); ?>">
                Checkout
            </a>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
    /* ---------- PRODUCTS ---------- */
    const PRODUCTS = <?= json_encode($PRODUCTS); ?>;

    function findProductById(id) {
        return PRODUCTS.find(p => String(p.id) === String(id));
    }

    function formatPrice(amount) {
        return `₹${Number(amount).toFixed(2)}`;
    }

    function renderCart() {
        const body = document.getElementById('cart-body');
        const items = window.DeliverXCart.readCart();

        body.innerHTML = '';
        let total = 0;

        if (!items || items.length === 0) {
            body.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted">
                    Your cart is empty
                </td>
            </tr>
        `;
            document.getElementById('cart-total').textContent = '₹0.00';
            return;
        }

        items.forEach(item => {
            const product = findProductById(item.id);

            // PRICE IS STORED IN RUPEES
            const price = Number(item.price ?? product?.price ?? 0);
            const qty = Number(item.qty);
            const subtotal = price * qty;

            total += subtotal;

            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>${product?.name || item.name}</td>
            <td>
                <input type="number"
                       class="form-control form-control-sm"
                       value="${qty}"
                       min="1"
                       data-id="${item.id}">
            </td>
            <td>${formatPrice(price)}</td>
            <td>${formatPrice(subtotal)}</td>
            <td>
                <button class="btn btn-sm btn-outline-danger"
                        data-remove="${item.id}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
            body.appendChild(tr);
        });

        document.getElementById('cart-total').textContent = formatPrice(total);
    }

    /* ---------- EVENTS ---------- */
    document.addEventListener('DOMContentLoaded', () => {
        renderCart();

        const body = document.getElementById('cart-body');

        body.addEventListener('change', e => {
            const input = e.target.closest('input[type="number"]');
            if (!input) return;

            window.DeliverXCart.updateQty(
                input.dataset.id,
                parseInt(input.value || '1', 10)
            );
            renderCart();
        });

        body.addEventListener('click', e => {
            const btn = e.target.closest('[data-remove]');
            if (!btn) return;

            window.DeliverXCart.removeFromCart(btn.dataset.remove);
            renderCart();
        });
    });
    </script>

</body>

</html>