<?php
declare(strict_types=1);

/**
 * Waypure Add / Edit Product Form (Rich Specs & Multi-Gallery)
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$pdo = getDb();
$activeNav = 'product-form';

$id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$product = null;
$galleryImages = [];

if ($isEdit) {
    $pStmt = $pdo->prepare('SELECT * FROM `products` WHERE id = :id LIMIT 1');
    $pStmt->execute([':id' => $id]);
    $product = $pStmt->fetch();

    if (!$product) {
        setFlash('danger', 'Product not found.');
        header('Location: ' . ADMIN_URL . 'products.php');
        exit;
    }

    $gStmt = $pdo->prepare('SELECT * FROM `product_gallery` WHERE product_id = :pid ORDER BY sort_order ASC, id ASC');
    $gStmt->execute([':pid' => $id]);
    $galleryImages = $gStmt->fetchAll();
}

$pageTitle = ($isEdit ? 'Edit Purifier: ' . e($product['name']) : 'Add New Water Purifier') . ' | Waypure Admin';

// Handle Delete Single Gallery Image via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'delete_gallery') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'product-form.php?id=' . $id);
        exit;
    }

    $galleryId = (int)($_POST['gallery_id'] ?? 0);
    $giStmt = $pdo->prepare('SELECT image_path FROM `product_gallery` WHERE id = :gid AND product_id = :pid LIMIT 1');
    $giStmt->execute([':gid' => $galleryId, ':pid' => $id]);
    $imgPath = $giStmt->fetchColumn();

    if ($imgPath) {
        deleteUploadedImage((string)$imgPath);
        $delG = $pdo->prepare('DELETE FROM `product_gallery` WHERE id = :gid');
        $delG->execute([':gid' => $galleryId]);
        setFlash('success', 'Gallery photo removed.');
    }

    header('Location: ' . ADMIN_URL . 'product-form.php?id=' . $id);
    exit;
}

// Handle Form Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'save') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . ($isEdit ? 'product-form.php?id=' . $id : 'product-form.php'));
        exit;
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $brand = trim((string)($_POST['brand'] ?? 'Waypure'));
    $slug = trim((string)($_POST['slug'] ?? ''));
    if (empty($slug)) {
        $slug = slugify($brand . ' ' . $name);
    } else {
        $slug = slugify($slug);
    }

    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $categoryName = trim((string)($_POST['category_name'] ?? 'Water purifiers'));

    // If category_id selected, fetch category name
    if ($categoryId !== null) {
        $cFetch = $pdo->prepare('SELECT name FROM `categories` WHERE id = :cid LIMIT 1');
        $cFetch->execute([':cid' => $categoryId]);
        $cName = $cFetch->fetchColumn();
        if ($cName) {
            $categoryName = (string)$cName;
        }
    }

    $price = (float)($_POST['price'] ?? 0);
    $mrp = !empty($_POST['mrp']) ? (float)$_POST['mrp'] : null;
    $stock = max(0, (int)($_POST['stock'] ?? 25));
    $technology = trim((string)($_POST['technology'] ?? 'RO + UV'));
    $capacity = trim((string)($_POST['capacity'] ?? ''));
    $warranty = trim((string)($_POST['warranty'] ?? '36 months'));
    $dimensions = trim((string)($_POST['dimensions'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

    // Parse features from lines
    $rawFeatures = trim((string)($_POST['features'] ?? ''));
    $featureList = [];
    if (!empty($rawFeatures)) {
        $lines = preg_split('/\r\n|\r|\n/', $rawFeatures);
        foreach ($lines as $line) {
            $cleanLine = trim($line);
            if (!empty($cleanLine)) {
                $featureList[] = ltrim($cleanLine, '-*• ');
            }
        }
    }
    $featuresJson = json_encode($featureList, JSON_UNESCAPED_UNICODE);

    if (empty($name) || $price <= 0) {
        setFlash('danger', 'Please enter a valid product name and selling price.');
        header('Location: ' . ADMIN_URL . ($isEdit ? 'product-form.php?id=' . $id : 'product-form.php'));
        exit;
    }

    // Main Image Upload Handling
    $mainImage = $product['image'] ?? 'assets/purifier-render.webp';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['image'], 'products');
        if ($uploaded) {
            if ($isEdit && !empty($product['image'])) {
                deleteUploadedImage((string)$product['image']);
            }
            $mainImage = $uploaded;
        }
    }

    if ($isEdit) {
        $upd = $pdo->prepare("
            UPDATE `products` SET
                category_id = :cat_id,
                slug = :slug,
                brand = :brand,
                name = :name,
                category = :cat_name,
                capacity = :capacity,
                technology = :technology,
                mrp = :mrp,
                price = :price,
                image = :image,
                warranty = :warranty,
                dimensions = :dimensions,
                features = :features,
                description = :description,
                stock = :stock,
                is_featured = :is_featured,
                status = :status
            WHERE id = :id
        ");
        $upd->execute([
            ':cat_id' => $categoryId,
            ':slug' => $slug,
            ':brand' => $brand,
            ':name' => $name,
            ':cat_name' => $categoryName,
            ':capacity' => $capacity ?: null,
            ':technology' => $technology,
            ':mrp' => $mrp,
            ':price' => $price,
            ':image' => $mainImage,
            ':warranty' => $warranty ?: null,
            ':dimensions' => $dimensions ?: null,
            ':features' => $featuresJson,
            ':description' => $description ?: null,
            ':stock' => $stock,
            ':is_featured' => $isFeatured,
            ':status' => $status,
            ':id' => $id
        ]);
        $productId = $id;
        setFlash('success', 'Product details updated successfully.');
    } else {
        $ins = $pdo->prepare("
            INSERT INTO `products`
            (`category_id`, `slug`, `brand`, `name`, `category`, `capacity`, `technology`, `mrp`, `price`, `image`, `warranty`, `dimensions`, `features`, `description`, `stock`, `is_featured`, `status`)
            VALUES
            (:cat_id, :slug, :brand, :name, :cat_name, :capacity, :technology, :mrp, :price, :image, :warranty, :dimensions, :features, :description, :stock, :is_featured, :status)
        ");
        $ins->execute([
            ':cat_id' => $categoryId,
            ':slug' => $slug,
            ':brand' => $brand,
            ':name' => $name,
            ':cat_name' => $categoryName,
            ':capacity' => $capacity ?: null,
            ':technology' => $technology,
            ':mrp' => $mrp,
            ':price' => $price,
            ':image' => $mainImage,
            ':warranty' => $warranty ?: null,
            ':dimensions' => $dimensions ?: null,
            ':features' => $featuresJson,
            ':description' => $description ?: null,
            ':stock' => $stock,
            ':is_featured' => $isFeatured,
            ':status' => $status
        ]);
        $productId = (int)$pdo->lastInsertId();
        setFlash('success', 'New product added to catalog.');
    }

    // Handle Multi-Gallery Uploads
    if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
        $totalFiles = count($_FILES['gallery']['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                $singleFile = [
                    'name' => $_FILES['gallery']['name'][$i],
                    'type' => $_FILES['gallery']['type'][$i],
                    'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                    'error' => $_FILES['gallery']['error'][$i],
                    'size' => $_FILES['gallery']['size'][$i]
                ];
                $gUpload = uploadImage($singleFile, 'products');
                if ($gUpload) {
                    $insG = $pdo->prepare("INSERT INTO `product_gallery` (`product_id`, `image_path`, `sort_order`) VALUES (:pid, :img, :sort)");
                    $insG->execute([':pid' => $productId, ':img' => $gUpload, ':sort' => $i + 1]);
                }
            }
        }
    }

    header('Location: ' . ADMIN_URL . 'product-form.php?id=' . $productId);
    exit;
}

// Fetch categories for dropdown
$categories = $pdo->query('SELECT id, name, slug FROM `categories` ORDER BY sort_order ASC, name ASC')->fetchAll();

// Parse features for textarea display
$featuresText = '';
if (!empty($product['features'])) {
    $fArray = json_decode((string)$product['features'], true);
    if (is_array($fArray)) {
        $featuresText = implode("\n", $fArray);
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="admin-content">
  
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="<?= ADMIN_URL ?>products.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Products</a>
        <span class="text-muted small">/</span>
        <span class="text-dark small fw-bold"><?= $isEdit ? 'Edit Purifier' : 'Add Purifier' ?></span>
      </div>
      <h1 class="h3 fw-bold mb-0" style="color:var(--admin-ink);">
        <?= $isEdit ? 'Edit: ' . e($product['brand'] . ' ' . $product['name']) : 'Add New Water Purifier' ?>
      </h1>
    </div>
    
    <div class="d-flex gap-2">
      <?php if ($isEdit): ?>
        <a href="<?= BASE_URL ?>product-details.php?id=<?= urlencode((string)$product['slug']) ?>" target="_blank" class="btn btn-admin-outline d-inline-flex align-items-center gap-2">
          <i class="bi bi-box-arrow-up-right"></i> View on Website
        </a>
      <?php endif; ?>
      <button type="submit" form="product-form" class="btn btn-admin-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-check2-circle"></i> Save Purifier
      </button>
    </div>
  </div>

  <?= renderFlash() ?>

  <form id="product-form" method="POST" action="<?= ADMIN_URL ?>product-form.php?action=save<?= $isEdit ? '&id=' . $id : '' ?>" enctype="multipart/form-data">
    <?= csrfField() ?>

    <div class="row g-4">
      
      <!-- Left Column: Primary Details -->
      <div class="col-lg-8">
        
        <!-- Basic Info Card -->
        <div class="card-custom mb-4">
          <div class="card-custom-header">
            <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Core Information</h2>
          </div>
          <div class="p-4">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-bold">Brand <span class="text-danger">*</span></label>
                <input type="text" name="brand" class="form-control" value="<?= e($product['brand'] ?? 'Waypure') ?>" placeholder="e.g. Waypure, Aquaguard" required>
              </div>

              <div class="col-md-8">
                <label class="form-label small fw-bold">Model / Product Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= e($product['name'] ?? '') ?>" placeholder="e.g. Q-Tone, Mars, Bravo" required>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">URL Slug (Leave empty to auto-generate)</label>
                <input type="text" name="slug" class="form-control" value="<?= e($product['slug'] ?? '') ?>" placeholder="e.g. waypure-mars">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Category</label>
                <select name="category_id" class="form-select">
                  <option value="">Select Category</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? null) == $cat['id'] ? 'selected' : '' ?>>
                      <?= e($cat['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label small fw-bold">Detailed Overview &amp; Purification Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Comprehensive explanation of water benefits, membrane details, suitable water types..."><?= e($product['description'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Technical Specifications Card -->
        <div class="card-custom mb-4">
          <div class="card-custom-header">
            <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Technical Specifications</h2>
          </div>
          <div class="p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Purification Technology <span class="text-danger">*</span></label>
                <input type="text" name="technology" class="form-control" value="<?= e($product['technology'] ?? 'RO + UV + Alkaline minerals') ?>" placeholder="e.g. RO + UV + Minerals + Zinc + Copper" required>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Storage Tank Capacity</label>
                <input type="text" name="capacity" class="form-control" value="<?= e($product['capacity'] ?? '10 L') ?>" placeholder="e.g. 10 L, 15 L, 35 LPH">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Warranty Period</label>
                <input type="text" name="warranty" class="form-control" value="<?= e($product['warranty'] ?? '36 months') ?>" placeholder="e.g. 36 months comprehensive">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Dimensions (W × D × H)</label>
                <input type="text" name="dimensions" class="form-control" value="<?= e($product['dimensions'] ?? '') ?>" placeholder="e.g. 380 × 260 × 490 mm">
              </div>

              <div class="col-12">
                <label class="form-label small fw-bold">Key Engineering Highlights / Features (One per line)</label>
                <textarea name="features" class="form-control" rows="4" placeholder="10-stage purification&#10;LED Filter Change Indicator&#10;Copper &amp; Alkaline infusion&#10;Auto-shutoff system"><?= e($featuresText) ?></textarea>
                <small class="text-muted">Enter each feature on a new line. They will be displayed as tick marks on the product page.</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Multi-Gallery Uploads Card -->
        <div class="card-custom">
          <div class="card-custom-header">
            <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Additional Product Gallery Photos</h2>
          </div>
          <div class="p-4">
            <div class="mb-3">
              <label class="form-label small fw-bold">Upload Multiple Angle Photos</label>
              <input type="file" name="gallery[]" class="form-control" multiple accept="image/*">
              <small class="text-muted">You can select multiple photos at once (JPG, PNG, WebP up to 10MB each).</small>
            </div>

            <?php if (!empty($galleryImages)): ?>
              <div class="mt-4">
                <label class="form-label small fw-bold mb-2">Existing Gallery Photos (<?= count($galleryImages) ?>):</label>
                <div class="d-flex flex-wrap gap-3">
                  <?php foreach ($galleryImages as $gi): ?>
                    <div class="position-relative border rounded p-1 bg-white" style="width: 100px; height: 100px;">
                      <img src="<?= e(assetUrl($gi['image_path'])) ?>" alt="" style="width: 100%; height: 100%; object-fit: contain;">
                      <button type="submit" form="del-gallery-<?= $gi['id'] ?>" class="btn btn-danger btn-sm position-absolute top-0 end-0 translate-middle rounded-circle p-0" style="width: 22px; height: 22px; line-height: 20px;" title="Remove image">&times;</button>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- Right Column: Pricing, Inventory & Primary Image -->
      <div class="col-lg-4">
        
        <!-- Pricing & Stock Card -->
        <div class="card-custom mb-4">
          <div class="card-custom-header">
            <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Pricing &amp; Inventory</h2>
          </div>
          <div class="p-4">
            <div class="mb-3">
              <label class="form-label small fw-bold">Selling Price (₹) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-white">₹</span>
                <input type="number" step="1" name="price" class="form-control fw-bold" value="<?= (int)($product['price'] ?? 0) ?>" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">MRP / Original Price (₹)</label>
              <div class="input-group">
                <span class="input-group-text bg-white">₹</span>
                <input type="number" step="1" name="mrp" class="form-control" value="<?= !empty($product['mrp']) ? (int)$product['mrp'] : '' ?>" placeholder="e.g. 15990">
              </div>
              <small class="text-muted">Displays strike-through discount price if higher than Selling Price.</small>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Stock Quantity</label>
              <input type="number" name="stock" class="form-control" value="<?= (int)($product['stock'] ?? 25) ?>" min="0">
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Status Visibility</label>
              <select name="status" class="form-select">
                <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Visible on website)</option>
                <option value="inactive" <?= ($product['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive (Hidden)</option>
              </select>
            </div>

            <div class="form-check form-switch mt-3">
              <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" <?= !empty($product['is_featured']) ? 'checked' : '' ?>>
              <label class="form-check-label fw-semibold small" for="is_featured">Feature on Homepage</label>
            </div>
          </div>
        </div>

        <!-- Main Product Image Card -->
        <div class="card-custom">
          <div class="card-custom-header">
            <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Primary Image</h2>
          </div>
          <div class="p-4 text-center">
            <div class="border rounded p-3 mb-3 bg-white d-flex align-items-center justify-content-center" style="min-height: 200px;">
              <img id="primary-img-preview" src="<?= e(assetUrl($product['image'] ?? 'assets/purifier-render.webp')) ?>" alt="Preview" style="max-height: 180px; max-width: 100%; object-fit: contain;">
            </div>

            <div class="text-start">
              <label class="form-label small fw-bold"><?= $isEdit ? 'Replace Primary Image' : 'Select Primary Image' ?></label>
              <input type="file" name="image" id="primary-img-input" class="form-control" accept="image/*">
              <small class="text-muted">Transparent PNG or WebP with white background looks best.</small>
            </div>
          </div>
        </div>

      </div>

    </div>

  </form>

  <!-- Separate Hidden Forms for Gallery Deletions -->
  <?php if (!empty($galleryImages)): ?>
    <?php foreach ($galleryImages as $gi): ?>
      <form id="del-gallery-<?= $gi['id'] ?>" method="POST" action="<?= ADMIN_URL ?>product-form.php?action=delete_gallery&id=<?= $id ?>" onsubmit="return confirm('Delete this gallery photo?');">
        <?= csrfField() ?>
        <input type="hidden" name="gallery_id" value="<?= $gi['id'] ?>">
      </form>
    <?php endforeach; ?>
  <?php endif; ?>

</main>

<script>
  // Live image preview
  document.getElementById('primary-img-input')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(evt) {
        document.getElementById('primary-img-preview').src = evt.target.result;
      };
      reader.readAsDataURL(file);
    }
  });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

