document.addEventListener('DOMContentLoaded', () => {
  const quickAddBtn = document.getElementById('quickAddBtn');
  if(quickAddBtn){
    quickAddBtn.addEventListener('click', ()=>{
      // Stub action: just show a toast-like alert
      const alertDiv = document.createElement('div');
      alertDiv.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3 shadow';
      alertDiv.style.zIndex = '1080';
      alertDiv.textContent = 'Product saved (stub). Use Inventory for full edit.';
      document.body.appendChild(alertDiv);
      setTimeout(()=> alertDiv.remove(), 2000);
    });
  }
});

