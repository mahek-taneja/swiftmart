/* Core UI interactions */
function qs(selector, root=document){ return root.querySelector(selector); }
function qsa(selector, root=document){ return Array.from(root.querySelectorAll(selector)); }

function formatPrice(cents){
  return `$${(cents/100).toFixed(2)}`;
}

window.DeliverX = { formatPrice };

document.addEventListener('click', (e)=>{
  const el = e.target.closest('[data-nav]');
  if(!el) return;
});


