/* Core UI interactions */
/* Updated: 2025-01-XX - Currency changed from USD ($) to INR (₹) */
function qs(selector, root = document) { return root.querySelector(selector); }
function qsa(selector, root = document) { return Array.from(root.querySelectorAll(selector)); }

function formatPrice(cents) {
  // Prices are stored in cents (paise), convert to rupees by dividing by 100
  // FORCE use rupees (₹) - DO NOT use dollars ($)
  const amount = (typeof cents === 'number' ? cents : parseFloat(cents) || 0) / 100;
  // Format with Indian Rupee symbol and proper number formatting
  // Use number_format-like formatting manually for better compatibility
  const formatted = amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  return `₹${formatted}`;
}

window.DeliverX = { formatPrice };

document.addEventListener('click', (e) => {
  const el = e.target.closest('[data-nav]');
  if (!el) return;
});


