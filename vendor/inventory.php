<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/head.php';
require_vendor();

$pdo = Database::getInstance()->getConnection();
$vendorId = current_vendor_id();
if (!$vendorId) {
  header('Location: ' . build_path('/vendor/login.php'));
  exit;
}

$error = '';
$notice = '';

// Detect whether products table uses price_cents (INT) or price (DECIMAL)
$hasPriceCents = false;
try {
  $chk = $pdo->prepare("SHOW COLUMNS FROM products LIKE 'price_cents'");
  $chk->execute();
  $hasPriceCents = (bool)$chk->fetch();
} catch (Throwable $e) {
  $error = $e->getMessage();
}

// ---------- Actions (add / update / delete) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  try {
    if ($action === 'add') {
      $name        = trim($_POST['name'] ?? '');
      $catId       = (int)($_POST['category_id'] ?? 0);
      $priceR      = (float)($_POST['price'] ?? 0);
      $stock       = (int)($_POST['stock'] ?? 0);
      $desc        = trim($_POST['description'] ?? '');
      $imageUrlInp = trim($_POST['image_url'] ?? '');

      if ($name === '' || $catId <= 0 || $priceR < 0) {
        $error = 'Please provide name, category and a valid price.';
      } else {
        // Handle image: prefer uploaded file; fall back to URL if provided
        $imagePath = null;
        if (!empty($_FILES['image_file']['name'] ?? '')) {
          $up = upload_file($_FILES['image_file'], 'products');
          if (!empty($up['success'])) {
            $imagePath = $up['url']; // something like /swiftmart/uploads/products/xxxx.webp
          } else {
            // If upload failed but URL provided, we still proceed with URL; otherwise show error
            if ($imageUrlInp !== '') {
              $imagePath = $imageUrlInp;
            } else {
              $error = 'Image upload failed: ' . implode('; ', $up['errors'] ?? ['Unknown error']);
            }
          }
        } elseif ($imageUrlInp !== '') {
          $imagePath = $imageUrlInp;
        }

        if (!$error) {
          if ($hasPriceCents) {
            $sql = "INSERT INTO products (vendor_id, category_id, name, description, price_cents, stock, image, status)
                    VALUES (:vid, :cid, :name, :descr, :pc, :stock, :image, 'active')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
              ':vid'   => $vendorId,
              ':cid'   => $catId,
              ':name'  => $name,
              ':descr' => $desc !== '' ? $desc : null,
              ':pc'    => (int)round($priceR * 100),
              ':stock' => $stock,
              ':image' => $imagePath ?: null,
            ]);
          } else {
            $sql = "INSERT INTO products (vendor_id, category_id, name, description, price, stock, image, status)
                    VALUES (:vid, :cid, :name, :descr, :price, :stock, :image, 'active')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
              ':vid'   => $vendorId,
              ':cid'   => $catId,
              ':name'  => $name,
              ':descr' => $desc !== '' ? $desc : null,
              ':price' => $priceR,
              ':stock' => $stock,
              ':image' => $imagePath ?: null,
            ]);
          }
          $notice = 'Product added.';
        }
      }
    }

    if ($action === 'update') {
      $pid       = (int)($_POST['id'] ?? 0);
      $priceR    = isset($_POST['price']) ? (float)$_POST['price'] : null;
      $stock     = isset($_POST['stock']) ? (int)$_POST['stock'] : null;
      $desc      = isset($_POST['description']) ? trim($_POST['description']) : null;

      // Ensure ownership
      $own = $pdo->prepare("SELECT id FROM products WHERE id = :id AND vendor_id = :vid");
      $own->execute([':id' => $pid, ':vid' => $vendorId]);
      if (!$own->fetch()) {
        $error = 'You cannot modify this product.';
      } else {
        // Optional: allow updating image (file or URL) on update row form
        $imagePath = null;
        $useImage  = false;
        if (!empty($_FILES['image_file']['name'] ?? '')) {
          $up = upload_file($_FILES['image_file'], 'products');
          if (!empty($up['success'])) {
            $imagePath = $up['url'];
            $useImage  = true;
          } else {
            $error = 'Image upload failed: ' . implode('; ', $up['errors'] ?? ['Unknown error']);
          }
        } elseif (isset($_POST['image_url'])) {
          $imageUrlUpd = trim($_POST['image_url']);
          if ($imageUrlUpd !== '') {
            $imagePath = $imageUrlUpd;
            $useImage  = true;
          }
        }

        if (!$error) {
          if ($hasPriceCents) {
            $sql = "
              UPDATE products
                 SET price_cents = COALESCE(:pc, price_cents),
                     stock       = COALESCE(:stock, stock),
                     description = COALESCE(:descr, description) " .
                     ($useImage ? ", image = :img " : "") . "
               WHERE id = :id AND vendor_id = :vid";
            $stmt = $pdo->prepare($sql);
            $params = [
              ':pc'    => $priceR !== null ? (int)round($priceR * 100) : null,
              ':stock' => $stock !== null ? $stock : null,
              ':descr' => $desc !== null ? ($desc === '' ? null : $desc) : null,
              ':id'    => $pid,
              ':vid'   => $vendorId
            ];
            if ($useImage) { $params[':img'] = $imagePath ?: null; }
            $stmt->execute($params);
          } else {
            $sql = "
              UPDATE products
                 SET price       = COALESCE(:price, price),
                     stock       = COALESCE(:stock, stock),
                     description = COALESCE(:descr, description) " .
                     ($useImage ? ", image = :img " : "") . "
               WHERE id = :id AND vendor_id = :vid";
            $stmt = $pdo->prepare($sql);
            $params = [
              ':price' => $priceR !== null ? $priceR : null,
              ':stock' => $stock !== null ? $stock : null,
              ':descr' => $desc !== null ? ($desc === '' ? null : $desc) : null,
              ':id'    => $pid,
              ':vid'   => $vendorId
            ];
            if ($useImage) { $params[':img'] = $imagePath ?: null; }
            $stmt->execute($params);
          }
          $notice = 'Product updated.';
        }
      }
    }

    if ($action === 'delete') {
      $pid = (int)($_POST['id'] ?? 0);
      $del = $pdo->prepare("DELETE FROM products WHERE id = :id AND vendor_id = :vid");
      $del->execute([':id' => $pid, ':vid' => $vendorId]);
      $notice = 'Product deleted.';
    }

    // PRG: avoid resubmission on refresh
    header('Location: ' . build_path('/vendor/inventory.php'));
    exit;

  } catch (Throwable $e) {
    $error = $e->getMessage();
  }
}

// Load categories
$categories = [];
try {
  $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
} catch (Throwable $e) {
  $error = $error ?: $e->getMessage();
}

// Load vendor products with category name
try {
  if ($hasPriceCents) {
    $sql = "
      SELECT p.id, p.name, COALESCE(c.name,'Uncategorized') AS category,
             p.price_cents, p.stock, p.image, p.description
      FROM products p
      LEFT JOIN categories c ON c.id = p.category_id
      WHERE p.vendor_id = :vid
      ORDER BY p.created_at DESC, p.id DESC
    ";
  } else {
    $sql = "
      SELECT p.id, p.name, COALESCE(c.name,'Uncategorized') AS category,
             p.price, p.stock, p.image, p.description
      FROM products p
      LEFT JOIN categories c ON c.id = p.category_id
      WHERE p.vendor_id = :vid
      ORDER BY p.created_at DESC, p.id DESC
    ";
  }
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':vid' => $vendorId]);
  $mine = $stmt->fetchAll();
} catch (Throwable $e) {
  $error = $error ?: $e->getMessage();
  $mine = [];
}

// Small helper to truncate description
function short($text, $limit = 60) {
  $t = trim((string)$text);
  if (mb_strlen($t) <= $limit) return $t;
  return mb_substr($t, 0, $limit - 1) . '…';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('Inventory – SwiftMart'); ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Inventory Management</h1>
            <a class="btn btn-outline-secondary" href="<?= build_path('/vendor/dashboard.php'); ?>">Back</a>
        </div>

        <?php if (!empty($notice)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($notice) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 mb-3">Add Product</h2>
                <form method="post" class="row g-3" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">

                    <div class="col-12 col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control"
                            placeholder="Write a short description…"></textarea>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Choose…</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
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

                    <div class="col-12 col-md-6">
                        <label class="form-label">Product Image (upload)</label>
                        <input type="file" name="image_file" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
                        <div class="form-text">Max 5MB. Allowed: jpg, jpeg, png, gif, webp.</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">or Image URL</label>
                        <input type="url" name="image_url" class="form-control" placeholder="https://…">
                        <div class="form-text">If both are provided, the uploaded file takes priority.</div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Add Product</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive card shadow-sm">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:64px;">Image</th>
                        <th>Name & Description</th>
                        <th>Category</th>
                        <th style="width:240px">Price</th>
                        <th style="width:160px">Stock</th>
                        <th class="text-end" style="width:200px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($mine): ?>
                    <?php foreach ($mine as $p): ?>
                    <tr>
                        <td>
                            <?php if (!empty($p['image'])): ?>
                            <img src="<?= htmlspecialchars($p['image']); ?>" alt=""
                                style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                            <?php else: ?>
                            <div class="bg-light border rounded" style="width:48px;height:48px;"></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($p['name']); ?></div>
                            <div class="text-muted small"><?= htmlspecialchars(short($p['description'] ?? '')); ?></div>
                        </td>
                        <td class="small text-muted"><?= htmlspecialchars($p['category']); ?></td>
                        <td>
                            <form method="post" class="d-flex align-items-center gap-2" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= (int)$p['id']; ?>">
                                <?php
                  $priceValue = '';
                  if (array_key_exists('price_cents', $p) && $p['price_cents'] !== null) {
                    $priceValue = number_format(((int)$p['price_cents']) / 100, 2, '.', '');
                  } elseif (array_key_exists('price', $p) && $p['price'] !== null) {
                    $priceValue = number_format((float)$p['price'], 2, '.', '');
                  }
                ?>
                                <input class="form-control form-control-sm" style="max-width:120px" name="price"
                                    type="number" step="0.01" min="0" value="<?= $priceValue ?>">
                        </td>
                        <td>
                            <input class="form-control form-control-sm" style="max-width:100px" name="stock"
                                type="number" min="0" value="<?= (int)($p['stock'] ?? 0); ?>">
                        </td>
                        <td class="text-end">
                            <!-- Update description & image inline (optional) -->
                            <input type="hidden" name="image_url" value="">
                            <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                            </form>

                            <!-- Separate form to update description + upload image -->
                            <form method="post" class="d-inline" enctype="multipart/form-data" style="margin-left:6px">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= (int)$p['id']; ?>">
                                <input type="hidden" name="price" value="">
                                <input type="hidden" name="stock" value="">
                                <!-- Small dropdown/popup could be nicer, but keep simple -->
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="this.nextElementSibling.style.display='inline-block'; this.style.display='none'">Edit
                                    Details</button>
                                <span style="display:none">
                                    <input type="file" name="image_file"
                                        class="form-control form-control-sm d-inline-block" style="width:160px"
                                        accept=".jpg,.jpeg,.png,.gif,.webp">
                                    <input type="text" name="description"
                                        class="form-control form-control-sm d-inline-block" style="width:220px"
                                        placeholder="New description">
                                    <button class="btn btn-sm btn-secondary" type="submit">Update</button>
                                </span>
                            </form>

                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$p['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit"
                                    onclick="return confirm('Delete this product?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-muted text-center py-3">No products to manage.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>