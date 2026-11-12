<?php
declare(strict_types=1);

/**
 * Waypure Category Management (CRUD with FK Constraints)
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$pdo = getDb();
$pageTitle = 'Category Management | Waypure Admin';
$activeNav = 'categories';

$action = $_GET['action'] ?? 'list';
$editId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$categoryToEdit = null;

// Handle Delete with FK check
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'categories.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $cStmt = $pdo->prepare('SELECT id, name, slug, image FROM `categories` WHERE id = :id LIMIT 1');
    $cStmt->execute([':id' => $id]);
    $cat = $cStmt->fetch();

    if ($cat) {
        // FK Check: count assigned products
        $prodCountStmt = $pdo->prepare('
            SELECT COUNT(*) FROM `products` 
            WHERE category_id = :id OR category = :name OR category = :slug
        ');
        $prodCountStmt->execute([
            ':id' => $id,
            ':name' => $cat['name'],
            ':slug' => $cat['slug']
        ]);
        $prodCount = (int)$prodCountStmt->fetchColumn();

        if ($prodCount > 0) {
            setFlash('danger', sprintf(
                'Cannot delete category "%s" because %d purifier(s) are currently assigned to it. Please reassign or delete those products first.',
                e($cat['name']),
                $prodCount
            ));
        } else {
            deleteUploadedImage((string)($cat['image'] ?? ''));
            $del = $pdo->prepare('DELETE FROM `categories` WHERE id = :id');
            $del->execute([':id' => $id]);
            setFlash('success', sprintf('Category "%s" deleted successfully.', e($cat['name'])));
        }
    }

    header('Location: ' . ADMIN_URL . 'categories.php');
    exit;
}

// Handle Toggle Status
if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'categories.php');
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    $upd = $pdo->prepare("UPDATE `categories` SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id");
    $upd->execute([':id' => $id]);
    setFlash('success', 'Category status updated.');
    header('Location: ' . ADMIN_URL . 'categories.php');
    exit;
}

// Handle Form Submission (Create / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['save_create', 'save_edit'], true)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'categories.php');
        exit;
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    if (empty($slug)) {
        $slug = slugify($name);
    } else {
        $slug = slugify($slug);
    }

    $description = trim((string)($_POST['description'] ?? ''));
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

    if (empty($name)) {
        setFlash('danger', 'Please provide a category name.');
        header('Location: ' . ADMIN_URL . 'categories.php' . ($action === 'save_edit' ? '?action=edit&id=' . $editId : ''));
        exit;
    }

    if ($action === 'save_create') {
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = uploadImage($_FILES['image'], 'categories');
        }

        try {
            $ins = $pdo->prepare("
                INSERT INTO `categories` (`name`, `slug`, `description`, `image`, `status`, `sort_order`)
                VALUES (:name, :slug, :description, :image, :status, :sort)
            ");
            $ins->execute([
                ':name' => $name,
                ':slug' => $slug,
                ':description' => $description,
                ':image' => $imagePath,
                ':status' => $status,
                ':sort' => $sortOrder
            ]);
            setFlash('success', sprintf('Category "%s" created successfully.', e($name)));
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                setFlash('danger', 'A category with this slug already exists. Please choose another slug.');
            } else {
                setFlash('danger', 'Database error: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'save_edit' && $editId > 0) {
        $cStmt = $pdo->prepare('SELECT image FROM `categories` WHERE id = :id LIMIT 1');
        $cStmt->execute([':id' => $editId]);
        $currentImage = $cStmt->fetchColumn();

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['image'], 'categories');
            if ($uploaded) {
                deleteUploadedImage((string)$currentImage);
                $currentImage = $uploaded;
            }
        }

        try {
            $upd = $pdo->prepare("
                UPDATE `categories`
                SET `name` = :name, `slug` = :slug, `description` = :description, 
                    `image` = :image, `status` = :status, `sort_order` = :sort
                WHERE id = :id
            ");
            $upd->execute([
                ':name' => $name,
                ':slug' => $slug,
                ':description' => $description,
                ':image' => $currentImage,
                ':status' => $status,
                ':sort' => $sortOrder,
                ':id' => $editId
            ]);
            setFlash('success', 'Category updated successfully.');
        } catch (PDOException $e) {
            setFlash('danger', 'Error updating category: ' . $e->getMessage());
        }
    }

    header('Location: ' . ADMIN_URL . 'categories.php');
    exit;
}

if ($action === 'edit' && $editId > 0) {
    $eStmt = $pdo->prepare('SELECT * FROM `categories` WHERE id = :id LIMIT 1');
    $eStmt->execute([':id' => $editId]);
    $categoryToEdit = $eStmt->fetch();
}

// Fetch all categories with product counts
$categoriesListStmt = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count
    FROM `categories` c
    LEFT JOIN `products` p ON (p.category_id = c.id OR p.category = c.name OR p.category = c.slug)
    GROUP BY c.id
    ORDER BY c.sort_order ASC, c.id ASC
");
$categoriesList = $categoriesListStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="admin-content">
  
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1" style="color:var(--admin-ink);">Categories</h1>
      <p class="text-muted small mb-0">Organize purifiers into Home, Premium, Commercial, and Specialty ranges.</p>
    </div>
    
    <button type="button" class="btn btn-admin-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#categoryModal">
      <i class="bi bi-plus-lg"></i> Add New Category
    </button>
  </div>

  <?= renderFlash() ?>

  <!-- Edit Category Box (if editing) -->
  <?php if ($categoryToEdit): ?>
    <div class="card-custom mb-4 border-warning">
      <div class="card-custom-header bg-warning-subtle">
        <h2 class="h5 fw-bold mb-0 text-dark">Edit Category: <?= e($categoryToEdit['name']) ?></h2>
        <a href="<?= ADMIN_URL ?>categories.php" class="btn btn-sm btn-outline-secondary">Cancel Edit</a>
      </div>
      <div class="p-4">
        <form method="POST" action="<?= ADMIN_URL ?>categories.php?action=save_edit&id=<?= $categoryToEdit['id'] ?>" enctype="multipart/form-data">
          <?= csrfField() ?>
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Category Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" value="<?= e($categoryToEdit['name']) ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold">URL Slug</label>
              <input type="text" name="slug" class="form-control" value="<?= e($categoryToEdit['slug']) ?>">
            </div>

            <div class="col-12">
              <label class="form-label small fw-bold">Description</label>
              <textarea name="description" class="form-control" rows="2"><?= e($categoryToEdit['description'] ?? '') ?></textarea>
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold">Thumbnail Image</label>
              <input type="file" name="image" class="form-control" accept="image/*">
              <?php if (!empty($categoryToEdit['image'])): ?>
                <div class="mt-2">
                  <img src="<?= e(assetUrl($categoryToEdit['image'])) ?>" alt="" style="height:50px; border-radius:6px; object-fit:contain; background:#f4f8f5; padding:4px;">
                </div>
              <?php endif; ?>
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" value="<?= (int)($categoryToEdit['sort_order'] ?? 0) ?>">
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold">Status</label>
              <select name="status" class="form-select">
                <option value="active" <?= ($categoryToEdit['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($categoryToEdit['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>

            <div class="col-12 mt-4 text-end">
              <button type="submit" class="btn btn-admin-primary px-4">Update Category</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <!-- Categories Table Card -->
  <div class="card-custom">
    <div class="card-custom-header">
      <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">All Categories (<?= count($categoriesList) ?>)</h2>
    </div>

    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th style="width: 70px;">Order</th>
            <th style="width: 100px;">Icon</th>
            <th>Category Details</th>
            <th>Slug</th>
            <th>Models Count</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categoriesList)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">No categories created yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($categoriesList as $cat): ?>
              <tr>
                <td class="fw-bold text-muted text-center"><?= $cat['sort_order'] ?></td>
                <td>
                  <div style="width:54px; height:54px; background:#f4f8f5; border-radius:10px; display:flex; align-items:center; justify-content:center; border:1px solid var(--admin-border);">
                    <img src="<?= e(assetUrl($cat['image'])) ?>" alt="" style="max-width:85%; max-height:85%; object-fit:contain; mix-blend-mode:multiply;">
                  </div>
                </td>
                <td>
                  <div class="fw-bold fs-6 text-dark"><?= e($cat['name']) ?></div>
                  <div class="text-muted small"><?= e(substr((string)($cat['description'] ?? ''), 0, 90)) ?></div>
                </td>
                <td>
                  <code class="text-muted small"><?= e($cat['slug']) ?></code>
                </td>
                <td>
                  <a href="<?= ADMIN_URL ?>products.php?category=<?= urlencode((string)$cat['slug']) ?>" class="badge bg-light text-dark border text-decoration-none px-2 py-1">
                    <?= $cat['product_count'] ?> products
                  </a>
                </td>
                <td>
                  <form method="POST" action="<?= ADMIN_URL ?>categories.php?action=toggle" class="d-inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <button type="submit" class="btn btn-sm p-0 border-0 badge-status badge-<?= e($cat['status']) ?>" title="Click to Toggle">
                      <?= e($cat['status']) ?>
                    </button>
                  </form>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <a href="<?= ADMIN_URL ?>categories.php?action=edit&id=<?= $cat['id'] ?>" class="btn btn-sm btn-admin-outline" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="<?= ADMIN_URL ?>categories.php?action=delete" onsubmit="return confirm('Are you sure you want to delete this category? (Will check for attached products)');" class="d-inline">
                      <?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete with FK validation">
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

  <!-- Add Category Modal -->
  <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content border-0 shadow">
        <form method="POST" action="<?= ADMIN_URL ?>categories.php?action=save_create" enctype="multipart/form-data">
          <?= csrfField() ?>
          
          <div class="modal-header border-bottom">
            <h5 class="modal-title fw-bold" id="categoryModalLabel">Create New Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label small fw-bold">Category Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Under-Sink Purifiers" required>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Slug (Optional, auto-generated)</label>
              <input type="text" name="slug" class="form-control" placeholder="e.g. under-sink-purifiers">
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Description</label>
              <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this purifier line..."></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Thumbnail Icon / Image</label>
              <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <div class="row g-2">
              <div class="col-6">
                <label class="form-label small fw-bold">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="<?= count($categoriesList) + 1 ?>">
              </div>
              <div class="col-6">
                <label class="form-label small fw-bold">Status</label>
                <select name="status" class="form-select">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="modal-footer border-top">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-admin-primary">Save Category</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

