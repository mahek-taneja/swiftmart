<?php
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../data/products.php';
require_vendor();

// Simple session-based overrides so vendors can add/update products without a DB
if (!isset($_SESSION['vendor_overrides'])) { $_SESSION['vendor_overrides'] = []; }
$vendorId = current_vendor_id();
if (!isset($_SESSION['vendor_overrides'][$vendorId])) {
  $_SESSION['vendor_overrides'][$vendorId] = [ 'created' => [], 'updates' => [] ];
}

function inv_get_all_for_vendor(string $vendorId) : array {
  global $PRODUCTS;
  $base = array_values(array_filter($PRODUCTS, fn($p)=> $p['vendor_id']===$vendorId));
  $ovr = $_SESSION['vendor_overrides'][$vendorId] ?? [ 'created' => [], 'updates' => [] ];
  $updates = $ovr['updates'] ?? [];
  // apply updates to base items
  foreach($base as &$p){
    if(isset($updates[$p['id']])){
      $u = $updates[$p['id']];
      if(isset($u['price'])){ $p['price'] = (int)$u['price']; }
      if(isset($u['stock'])){ $p['stock'] = (int)$u['stock']; }
    }
  }
  unset($p);
  // append created items
  $created = array_values($ovr['created'] ?? []);
  return array_values(array_merge($base, $created));
}

function inv_create_product(string $vendorId, array $data) : void {
  $ovr =& $_SESSION['vendor_overrides'][$vendorId];
  $newId = 's' . substr(md5(uniqid('', true)), 0, 8);
  $ovr['created'][] = [
    'id' => $newId,
    'name' => $data['name'],
    'price' => (int)$data['price'],
    'image' => $data['image'] ?: 'https://via.placeholder.com/600x400?text=Product',
    'category' => $data['category'],
    'vendor_id' => $vendorId,
    'rating' => 4.0,
    'stock' => (int)$data['stock']
  ];
}

function inv_update_product(string $vendorId, string $productId, array $data) : void {
  $ovr =& $_SESSION['vendor_overrides'][$vendorId];
  if(!isset($ovr['updates'][$productId])){ $ovr['updates'][$productId] = []; }
  foreach(['price','stock'] as $field){ if(isset($data[$field])){ $ovr['updates'][$productId][$field] = (int)$data[$field]; } }
}

function inv_delete_product(string $vendorId, string $productId) : void {
  $ovr =& $_SESSION['vendor_overrides'][$vendorId];
  $ovr['created'] = array_values(array_filter($ovr['created'], fn($p)=> $p['id'] !== $productId));
  // If it was a base product, we can't delete permanently; ignore for now
}

// Handle actions
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action = $_POST['action'] ?? '';
  if($action==='add'){
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $priceRupees = (float)($_POST['price'] ?? '0');
    $priceCents = (int)round($priceRupees * 100);
    $stock = (int)($_POST['stock'] ?? '0');
    $image = trim($_POST['image'] ?? '');
    if($name && $category && $priceCents >= 0){
      inv_create_product($vendorId, [ 'name'=>$name, 'category'=>$category, 'price'=>$priceCents, 'stock'=>$stock, 'image'=>$image ]);
    }
    header('Location: ' . build_path('/vendor/inventory.php'));
    exit;
  }
  if($action==='update'){
    $pid = $_POST['id'] ?? '';
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : null;
    $priceRupees = isset($_POST['price']) ? (float)$_POST['price'] : null;
    $payload = [];
    if($stock !== null){ $payload['stock'] = $stock; }
    if($priceRupees !== null){ $payload['price'] = (int)round($priceRupees * 100); }
    if($pid && $payload){ inv_update_product($vendorId, $pid, $payload); }
    header('Location: ' . build_path('/vendor/inventory.php'));
    exit;
  }
  if($action==='delete'){
    $pid = $_POST['id'] ?? '';
    if($pid){ inv_delete_product($vendorId, $pid); }
    header('Location: ' . build_path('/vendor/inventory.php'));
    exit;
  }
}

// Build inventory and category list
$mine = inv_get_all_for_vendor($vendorId);
$allCats = array_values(array_unique(array_map(fn($p)=> $p['category'], inv_get_all_for_vendor($vendorId)) + array_map(fn($p)=> $p['category'], $PRODUCTS)));
sort($allCats);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php render_head('Inventory – DeliverX'); ?>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Inventory Management</h1>
    <a class="btn btn-outline-secondary" href="<?php echo build_path('/vendor/dashboard.php'); ?>">Back</a>
  </div>
  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h6 mb-3">Add Product</h2>
      <form method="post" class="row g-3">
        <input type="hidden" name="action" value="add">
        <div class="col-12 col-md-6">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Category</label>
          <select name="category" class="form-select" required>
            <?php foreach(array_unique(array_merge($allCats, ['Foods'])) as $cat): ?>
              <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Price (₹)</label>
          <input type="number" name="price" step="0.01" min="0" class="form-control" required>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Stock</label>
          <input type="number" name="stock" min="0" class="form-control" value="0">
        </div>
        <div class="col-12 col-md-9">
          <label class="form-label">Image URL (optional)</label>
          <input type="url" name="image" class="form-control" placeholder="https://...">
        </div>
        <div class="col-12">
          <button class="btn btn-primary" type="submit">Add Product</button>
        </div>
      </form>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr></thead>
      <tbody>
        <?php foreach($mine as $p): ?>
          <tr>
            <td><?php echo htmlspecialchars($p['name']); ?><?php if(str_starts_with($p['id'], 's')): ?> <span class="badge text-bg-secondary">session</span><?php endif; ?></td>
            <td class="small text-muted"><?php echo htmlspecialchars($p['category']); ?></td>
            <td>
              <form method="post" class="d-flex align-items-center gap-2">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($p['id']); ?>">
                <input class="form-control form-control-sm" style="max-width:140px" name="price" type="number" step="0.01" min="0" value="<?php echo number_format($p['price']/100,2,'.',''); ?>">
            </td>
            <td>
                <input class="form-control form-control-sm" style="max-width:120px" name="stock" type="number" min="0" value="<?php echo (int)$p['stock']; ?>">
            </td>
            <td class="text-nowrap">
                <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
              </form>
              <?php if(str_starts_with($p['id'], 's')): ?>
              <form method="post" class="d-inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($p['id']); ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(count($mine)===0): ?>
          <tr><td colspan="5" class="text-muted">No products to manage.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


