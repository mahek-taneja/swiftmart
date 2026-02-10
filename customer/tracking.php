<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';

$orderNumber = $_GET['order'] ?? '';
$demoOrder   = $_SESSION['demo_orders'][$orderNumber] ?? null;
$initialStatus = $demoOrder['status'] ?? 'Processing';
$initialEta    = $demoOrder['eta'] ?? '30–45 mins';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('Order Tracking – SwiftMart'); ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container py-4">
        <h1 class="h4 mb-3">Order Tracking</h1>

        <?php if (!$orderNumber): ?>
        <div class="alert alert-info">Enter an order number:</div>
        <form class="d-flex" method="get">
            <input class="form-control me-2" name="order" placeholder="e.g., SM202501010001" required>
            <button class="btn btn-primary">Track</button>
        </form>
        <?php else: ?>
        <div class="card p-3 mb-3">
            <div><strong>Order Number:</strong> <?php echo htmlspecialchars($orderNumber); ?></div>
            <div><strong>Status:</strong> <span id="order-status"><?php echo htmlspecialchars($initialStatus); ?></span>
            </div>
            <div><strong>ETA:</strong> <span id="order-eta"><?php echo htmlspecialchars($initialEta); ?></span></div>
            <div><strong>Tracking Number:</strong> <span
                    id="tracking-number">TRK-<?php echo htmlspecialchars($orderNumber); ?></span></div>
        </div>

        <div class="card p-3 mb-3">
            <h2 class="h6 mb-3">Live Status</h2>
            <div class="progress mb-3" role="progressbar" aria-label="Order Progress" aria-valuemin="0"
                aria-valuemax="100">
                <div class="progress-bar progress-bar-striped" id="order-progress" style="width: 25%">Order Placed
                </div>
            </div>
            <ul class="timeline list-unstyled mb-0" id="tracking-events"></ul>
        </div>

        <script>
        const ORDER_NUMBER = <?php echo json_encode($orderNumber); ?>;
        const INITIAL_STATUS = <?php echo json_encode($initialStatus); ?>;

        const demoSteps = [{
                status: 'Processing',
                details: 'Your order has been placed and is being prepared.'
            },
            {
                status: 'Shipped',
                details: 'Vendor has shipped your order.'
            },
            {
                status: 'Delivered',
                details: 'Order delivered. Enjoy!'
            },
        ];

        function computeProgress(stepIndex) {
            const total = demoSteps.length;
            return ((stepIndex + 1) / total) * 100;
        }

        function renderTimeline(currentIndex) {
            const list = document.getElementById('tracking-events');
            list.innerHTML = '';
            const now = new Date();

            demoSteps.forEach((step, idx) => {
                const li = document.createElement('li');
                li.className = 'mb-2';
                const dt = new Date(now.getTime() - (currentIndex - idx) * 5 * 60000);
                li.innerHTML = `
          <div class="small text-muted">${dt.toLocaleTimeString()}</div>
          <div><strong>${step.status}</strong></div>
          <div class="small text-muted">${step.details}</div>
        `;
                list.appendChild(li);
            });
        }

        function statusToIndex(status) {
            const idx = demoSteps.findIndex(s => s.status.toLowerCase() === status.toLowerCase());
            return idx >= 0 ? idx : 0;
        }

        let currentStep = statusToIndex(INITIAL_STATUS);

        function advanceStep() {
            const statusEl = document.getElementById('order-status');
            const etaEl = document.getElementById('order-eta');
            const bar = document.getElementById('order-progress');

            currentStep = Math.min(currentStep + 1, demoSteps.length - 1);

            const step = demoSteps[currentStep];
            statusEl.textContent = step.status;
            const pct = computeProgress(currentStep);
            bar.style.width = pct + '%';
            bar.textContent = step.status;

            // Simple demo ETA update
            if (step.status === 'Delivered') {
                etaEl.textContent = 'Delivered';
            }

            renderTimeline(currentStep);
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Initial render based on current status
            const bar = document.getElementById('order-progress');
            const pct = computeProgress(currentStep);
            bar.style.width = pct + '%';
            bar.textContent = demoSteps[currentStep].status;
            renderTimeline(currentStep);
        });
        </script>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>