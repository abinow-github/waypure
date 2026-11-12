<?php
declare(strict_types=1);

/**
 * Waypure Product Catalog Management
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$pdo = getDb();
$pageTitle = 'Product Catalog | Waypure Admin';
$activeNav = 'products';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'delete') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'products.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $pStmt = $pdo->prepare('SELECT name, image FROM `products` WHERE id = :id LIMIT 1');
    $pStmt->execute([':id' => $id]);
    $prod = $pStmt->fetch();

    if ($prod) {
        // Delete gallery images from disk
        $gStmt = $pdo->prepare('SELECT image_path FROM `product_gallery` WHERE product_id = :pid');
        $gStmt->execute([':pid' => $id]);
        $galleryImages = $gStmt->fetchAll();
        foreach ($galleryImages as $g) {
            deleteUploadedImage((string)$g['image_path']);
        }

        // Delete main image from disk if in uploads/
        deleteUploadedImage((string)$prod['image']);

        // Delete product record (cascade deletes gallery & cart references)
        $del = $pdo->prepare('DELETE FROM `products` WHERE id = :id');
        $del->execute([':id' => $id]);

        setFlash('success', sprintf('Product "%s" has been removed from catalog.', e($prod['name'])));
    }

    header('Location: ' . ADMIN_URL . 'products.php');
    exit;
}

// Handle Toggle Featured / Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_GET['action'] ?? '', ['toggle_status', 'toggle_featured'], true)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'products.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($_GET['action'] === 'toggle_status') {
        $upd = $pdo->prepare("UPDATE `products` SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id");
        $upd->execute([':id' => $id]);
        setFlash('success', 'Product visibility updated.');
    } else {
        $upd = $pdo->prepare("UPDATE `products` SET is_featured = IF(is_featured = 1, 0, 1) WHERE id = :id");
        $upd->execute([':id' => $id]);
        setFlash('success', 'Featured status updated.');
    }

    header('Location: ' . ADMIN_URL . 'products.php');
    exit;
}

// Filter inputs
$search = trim((string)($_GET['search'] ?? ''));
$filterCategory = trim((string)($_GET['category'] ?? ''));
$filterBrand = trim((string)($_GET['brand'] ?? ''));
$filterStatus = trim((string)($_GET['status'] ?? ''));

// Build Query
$query = "
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM `products` p
    LEFT JOIN `categories` c ON p.category_id = c.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (p.name LIKE :search OR p.brand LIKE :search OR p.slug LIKE :search OR p.technology LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($filterCategory)) {
    $query .= " AND (c.slug = :cat OR p.category = :cat OR c.name = :cat)";
    $params[':cat'] = $filterCategory;
}

if (!empty($filterBrand)) {
    $query .= " AND p.brand = :brand";
    $params[':brand'] = $filterBrand;
}

if (!empty($filterStatus) && in_array($filterStatus, ['active', 'inactive'], true)) {
    $query .= " AND p.status = :status";
    $params[':status'] = $filterStatus;
}

$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch filter option lists
$categoriesList = $pdo->query('SELECT slug, name FROM `categories` ORDER BY name ASC')->fetchAll();
$brandsList = $pdo->query('SELECT DISTINCT brand FROM `products` ORDER BY brand ASC')->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="admin-content">
  
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1" style="color:var(--admin-ink);">Water Purifiers Catalog</h1>
      <p class="text-muted small mb-0">Manage all models, pricing, stock levels, and specifications.</p>
    </div>
    
    <a href="<?= ADMIN_URL ?>product-form.php" class="btn btn-admin-primary d-inline-flex align-items-center gap-2">
      <i class="bi bi-plus-lg"></i> Add New Product
    </a>
  </div>

  <?= renderFlash() ?>

  <!-- Filters Card -->
  <div class="card-custom mb-4">
    <div class="p-3">
      <form method="GET" action="<?= ADMIN_URL ?>products.php" class="row g-2 align-items-center">
        
        <div class="col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" name="search" class="form-control" placeholder="Search by name, brand, tech..." value="<?= e($search) ?>">
          </div>
        </div>

        <div class="col-sm-6 col-md-3">
          <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categoriesList as $cl): ?>
              <option value="<?= e($cl['slug']) ?>" <?= $filterCategory === $cl['slug'] ? 'selected' : '' ?>>
                <?= e($cl['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-sm-6 col-md-2">
          <select name="brand" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Brands</option>
            <?php foreach ($brandsList as $bl): ?>
              <option value="<?= e($bl['brand']) ?>" <?= $filterBrand === $bl['brand'] ? 'selected' : '' ?>>
                <?= e($bl['brand']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-sm-6 col-md-2">
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Active Only</option>
            <option value="inactive" <?= $filterStatus === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
          </select>
        </div>

        <div class="col-sm-6 col-md-1 d-flex gap-1">
          <button type="submit" class="btn btn-sm btn-admin-primary w-100">Filter</button>
          <?php if (!empty($search) || !empty($filterCategory) || !empty($filterBrand) || !empty($filterStatus)): ?>
            <a href="<?= ADMIN_URL ?>products.php" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
          <?php endif; ?>
        </div>

      </form>
    </div>
  </div>

  <!-- Products Table Card -->
  <div class="card-custom">
    <div class="card-custom-header">
      <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Showing <?= count($products) ?> Models</h2>
    </div>

    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th style="width: 80px;">Image</th>
            <th>Product Name &amp; Brand</th>
            <th>Category</th>
            <th>Pricing</th>
            <th>Tech &amp; Capacity</th>
            <th>Stock</th>
            <th>Featured</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($products)): ?>
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">No products found matching the criteria.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($products as $p): ?>
              <tr>
                <td>
                  <div style="width:58px; height:58px; background:#f4f8f5; border-radius:10px; display:flex; align-items:center; justify-content:center; border:1px solid var(--admin-border);">
                    <img src="<?= e(assetUrl($p['image'])) ?>" alt="" style="max-width:85%; max-height:85%; object-fit:contain; mix-blend-mode:multiply;">
                  </div>
                </td>
                <td>
                  <div class="fw-bold" style="color:var(--admin-ink); font-size:15px;"><?= e($p['name']) ?></div>
                  <span class="badge bg-light text-muted border"><?= e($p['brand']) ?></span>
                </td>
                <td>
                  <span class="text-dark fw-semibold small"><?= e($p['category_name'] ?: $p['category']) ?></span>
                </td>
                <td>
                  <div class="fw-bold text-dark"><?= formatPrice($p['price']) ?></div>
                  <?php if (!empty($p['mrp']) && (float)$p['mrp'] > (float)$p['price']): ?>
                    <small class="text-muted text-decoration-line-through"><?= formatPrice($p['mrp']) ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="text-truncate small" style="max-width:180px;" title="<?= e($p['technology']) ?>">
                    <?= e($p['technology']) ?>
                  </div>
                  <?php if (!empty($p['capacity'])): ?>
                    <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:10px;"><?= e($p['capacity']) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= (int)$p['stock'] > 5 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                    <?= (int)$p['stock'] ?> units
                  </span>
                </td>
                <td>
                  <form method="POST" action="<?= ADMIN_URL ?>products.php?action=toggle_featured" class="d-inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-sm p-0 border-0 fs-5" title="Toggle Featured">
                      <?= $p['is_featured'] ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-muted"></i>' ?>
                    </button>
                  </form>
                </td>
                <td>
                  <form method="POST" action="<?= ADMIN_URL ?>products.php?action=toggle_status" class="d-inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-sm p-0 border-0 badge-status badge-<?= e($p['status']) ?>" title="Click to Toggle">
                      <?= e($p['status']) ?>
                    </button>
                  </form>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="<?= BASE_URL ?>product-details.php?id=<?= urlencode((string)$p['slug']) ?>" target="_blank" class="btn btn-sm btn-admin-outline" title="View on site">
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <a href="<?= ADMIN_URL ?>product-form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-admin-outline" title="Edit Product">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="<?= ADMIN_URL ?>products.php?action=delete" onsubmit="return confirm('Are you sure you want to delete <?= addslashes($p['name']) ?>?');" class="d-inline">
                      <?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

