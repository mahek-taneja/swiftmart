<?php
$PRODUCTS = [
  [
    'id' => 'p1001',
    'name' => 'Organic Apples (1kg)',
    'price' => 399,
    'image' => 'https://images.unsplash.com/photo-1567306226416-28f0efdc88ce?w=600&q=80',
    'category' => 'Groceries',
    'vendor_id' => 'v001',
    'rating' => 4.6,
    'stock' => 120
  ],
  [
    'id' => 'p1002',
    'name' => 'Wireless Headphones',
    'price' => 5999,
    'image' => 'https://images.unsplash.com/photo-1518449078602-5ea1a1f18ebc?w=600&q=80',
    'category' => 'Electronics',
    'vendor_id' => 'v002',
    'rating' => 4.4,
    'stock' => 42
  ],
  [
    'id' => 'p1003',
    'name' => 'Denim Jacket',
    'price' => 7499,
    'image' => 'https://images.unsplash.com/photo-1542060748-10c28b62716c?w=600&q=80',
    'category' => 'Fashion',
    'vendor_id' => 'v003',
    'rating' => 4.2,
    'stock' => 65
  ],
  [
    'id' => 'p1004',
    'name' => 'Stainless Steel Cookware Set',
    'price' => 12999,
    'image' => 'https://images.unsplash.com/photo-1556911261-6bd341186b7d?w=600&q=80',
    'category' => 'Home',
    'vendor_id' => 'v004',
    'rating' => 4.5,
    'stock' => 20
  ],
  [
    'id' => 'p1005',
    'name' => 'Vitamin C Tablets',
    'price' => 1099,
    'image' => 'https://images.unsplash.com/photo-1598440947619-2c35fc9c9c1c?w=600&q=80',
    'category' => 'Health',
    'vendor_id' => 'v001',
    'rating' => 4.1,
    'stock' => 150
  ],
];

function get_products(array $opts = []) : array {
  global $PRODUCTS;
  $items = $PRODUCTS;
  if(isset($opts['category'])){
    $items = array_values(array_filter($items, fn($p)=> $p['category']===$opts['category']));
  }
  if(isset($opts['q'])){
    $q = strtolower(trim($opts['q']));
    $items = array_values(array_filter($items, fn($p)=> str_contains(strtolower($p['name']), $q)));
  }
  return $items;
}

function get_product(string $id) : ?array {
  global $PRODUCTS;
  foreach($PRODUCTS as $p){ if($p['id']===$id) return $p; }
  return null;
}

?>


