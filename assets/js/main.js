/* Core UI interactions */
/* Currency: INR (₹) — prices stored directly in rupees */

function qs(selector, root = document) {
  return root.querySelector(selector);
}
function qsa(selector, root = document) {
  return Array.from(root.querySelectorAll(selector));
}

/**
 * Format price assuming amount is already in RUPEES
 * Example: 100 -> ₹100.00
 */
function formatPrice(amount) {
  const value = Number(amount) || 0;
  const formatted = value
    .toFixed(2)
    .replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  return `₹${formatted}`;
}

// Expose globally
window.DeliverX = window.DeliverX || {};
window.DeliverX.formatPrice = formatPrice;

/* Nav handler (unchanged) */
document.addEventListener('click', (e) => {
  const el = e.target.closest('[data-nav]');
  if (!el) return;
});
