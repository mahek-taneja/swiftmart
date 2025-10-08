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
    'image' => '../assets/img/headphone.webp',
    'category' => 'Electronics',
    'vendor_id' => 'v002',
    'rating' => 4.4,
    'stock' => 42
  ],
  [
    'id' => 'p1003',
    'name' => 'Denim Jacket',
    'price' => 7499,
    'image' => '../assets/img/jacket.webp',
    'category' => 'Fashion',
    'vendor_id' => 'v003',
    'rating' => 4.2,
    'stock' => 65
  ],
  [
    'id' => 'p1004',
    'name' => 'Stainless Steel Cookware Set',
    'price' => 12999,
    'image' => '../assets/img/steel.jpg',
    'category' => 'Home',
    'vendor_id' => 'v004',
    'rating' => 4.5,
    'stock' => 20
  ],
  [
    'id' => 'p1005',
    'name' => 'Vitamin C Tablets',
    'price' => 1099,
    'image' => '../assets/img/vitamin c.jpg',
    'category' => 'Health',
    'vendor_id' => 'v001',
    'rating' => 4.1,
    'stock' => 150
  ],
  [
    'id' => 'p1006',
    'name' => 'Instant Noodles (Masala)',
    'price' => 499,
    'image' => '../assets/img/instant noodles.webp',
    'category' => 'Foods',
    'vendor_id' => 'v001',
    'rating' => 4.3,
    'stock' => 320
  ],
  [
    'id' => 'p1007',
    'name' => 'Ready-to-Eat Paneer Butter Masala',
    'price' => 1799,
    'image' => '../assets/img/panner-butter-masala.jpg',
    'category' => 'Foods',
    'vendor_id' => 'v001',
    'rating' => 4.0,
    'stock' => 95
  ],
  [
    'id' => 'p1008',
    'name' => 'Herbal Face Wash',
    'price' => 2499,
    'image' => '../assets/img/herbal-facewash.jpg',
    'category' => 'Beauty',
    'vendor_id' => 'v003',
    'rating' => 4.2,
    'stock' => 210
  ],
  [
    'id' => 'p1009',
    'name' => 'Kids Building Blocks Set',
    'price' => 3299,
    'image' => '../assets/img/',
    'category' => 'Toys',
    'vendor_id' => 'v002',
    'rating' => 4.5,
    'stock' => 70
  ],
  [
    'id' => 'p1010',
    'name' => 'Cricket Bat (English Willow)',
    'price' => 79999,
    'image' => '../assets/img/bat.jpg',
    'category' => 'Sports',
    'vendor_id' => 'v004',
    'rating' => 4.7,
    'stock' => 15
  ],
  [
    'id' => 'p1011',
    'name' => 'Car vacuum Cleaner',
    'price' => 25999,
    'image' => '../assets/img/vaccum-cleaner.jpg',
    'category' => 'Automotive',
    'vendor_id' => 'v002',
    'rating' => 4.1,
    'stock' => 38
  ],
  [
    'id' => 'p1012',
    'name' => 'Smartphone (128GB)',
    'price' => 249999,
    'image' => '../assets/img/smartphone.jpg',
    'category' => 'Electronics',
    'vendor_id' => 'v002',
    'rating' => 4.6,
    'stock' => 55
  ],
  [
    'id' => 'p1013',
    'name' => 'Organic Basmati Rice (5kg)',
    'price' => 8999,
    'image' => '../assets/img/rice.jpg',
    'category' => 'Groceries',
    'vendor_id' => 'v001',
    'rating' => 4.4,
    'stock' => 80
  ],
  [
    'id' => 'p1014',
    'name' => 'Air Fryer (3.5L)',
    'price' => 64999,
    'image' => '../assets/img/air-f.webp',
    'category' => 'Home',
    'vendor_id' => 'v004',
    'rating' => 4.3,
    'stock' => 27
  ],
  [
    'id' => 'p1015',
    'name' => 'Chicken Biryani (Regular)',
    'price' => 24999,
    'image' => '../assets/img/chicken-biryani.jpg',
    'category' => 'Foods',
    'vendor_id' => 'v001',
    'rating' => 4.5,
    'stock' => 50
  ],
  [
    'id' => 'p1016',
    'name' => 'Margherita Pizza (Medium)',
    'price' => 32999,
    'image' => '../assets/img/pizza.jpg',
    'category' => 'Foods',
    'vendor_id' => 'v002',
    'rating' => 4.3,
    'stock' => 40
  ],
  [
    'id' => 'p1017',
    'name' => 'Veg Burger Combo',
    'price' => 19999,
    'image' => '../assets/img/burger.jpg',
    'vendor_id' => 'v003',
    'rating' => 4.2,
    'stock' => 60
  ],
  [
    'id' => 'p1018',
    'name' => 'Chicken Momos (10 pcs)',
    'price' => 14999,
    'image' => '../assets/img/momos.webp',
    'category' => 'Foods',
    'vendor_id' => 'v004',
    'rating' => 4.4,
    'stock' => 70
  ],
  [
    'id' => 'p1019',
    'name' => 'Masala Dosa',
    'price' => 12999,
    'image' => '../assets/img/dosa.jpg',
    'category' => 'Foods',
    'vendor_id' => 'v001',
    'rating' => 4.6,
    'stock' => 80
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


