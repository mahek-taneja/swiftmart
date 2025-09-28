const CART_KEY = 'deliverx_cart_v1';

function readCart(){
  try{ return JSON.parse(localStorage.getItem(CART_KEY)) || []; }catch{ return []; }
}
function writeCart(items){
  localStorage.setItem(CART_KEY, JSON.stringify(items));
}
function addToCart(product, qty=1){
  const cart = readCart();
  const existing = cart.find(i=>i.id===product.id);
  if(existing){ existing.qty += qty; }
  else { cart.push({ id: product.id, name: product.name, price: product.price, qty }); }
  writeCart(cart);
  updateCartCountBadge();
}
function removeFromCart(productId){
  const cart = readCart().filter(i=> i.id!==productId);
  writeCart(cart);
  updateCartCountBadge();
}
function updateQty(productId, qty){
  const cart = readCart();
  const item = cart.find(i=>i.id===productId);
  if(item){ item.qty = Math.max(1, qty|0); writeCart(cart); }
  updateCartCountBadge();
}
function cartCount(){
  return readCart().reduce((sum,i)=> sum + i.qty, 0);
}
function updateCartCountBadge(){
  const badge = document.getElementById('cart-count');
  if(!badge) return;
  badge.textContent = cartCount();
}

window.DeliverXCart = { readCart, writeCart, addToCart, removeFromCart, updateQty, cartCount };

