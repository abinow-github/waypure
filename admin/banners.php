<?php
declare(strict_types=1);

/**
 * Waypure Hero Banner Slider Management (CRUD)
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$pdo = getDb();
$pageTitle = 'Hero Banner Sliders | Waypure Admin';
$activeNav = 'banners';

$action = $_GET['action'] ?? 'list';
$editId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$bannerToEdit = null;

// Handle Delete
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'banners.php');
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    $bStmt = $pdo->prepare('SELECT image FROM `banners` WHERE id = :id LIMIT 1');
    $bStmt->execute([':id' => $id]);
    $img = $bStmt->fetchColumn();

    if ($img) {
        deleteUploadedImage((string)$img);
        $del = $pdo->prepare('DELETE FROM `banners` WHERE id = :id');
        $del->execute([':id' => $id]);
        setFlash('success', 'Banner removed successfully.');
    }
    header('Location: ' . ADMIN_URL . 'banners.php');
    exit;
}

// Handle Toggle Status
if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'banners.php');
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    $upd = $pdo->prepare("UPDATE `banners` SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id");
    $upd->execute([':id' => $id]);
    setFlash('success', 'Banner status updated.');
    header('Location: ' . ADMIN_URL . 'banners.php');
    exit;
}

// Handle Form Submission (Create / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['save_create', 'save_edit'], true)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'banners.php');
        exit;
    }

    $title = trim((string)($_POST['title'] ?? ''));
    $subtitle = trim((string)($_POST['subtitle'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $buttonText = trim((string)($_POST['button_text'] ?? 'Explore Products'));
    $buttonUrl = trim((string)($_POST['button_url'] ?? '#details'));
    $theme = in_array($_POST['theme'] ?? 'dark', ['dark', 'light'], true) ? $_POST['theme'] : 'dark';
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

    if (empty($title)) {
        setFlash('danger', 'Please provide a banner title.');
        header('Location: ' . ADMIN_URL . 'banners.php' . ($action === 'save_edit' ? '?action=edit&id=' . $editId : ''));
        exit;
    }

    if ($action === 'save_create') {
        $imagePath = 'uploads/banners/hero-slide-1.jpg'; // default fallback
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['image'], 'banners');
            if ($uploaded) {
                $imagePath = $uploaded;
            } else {
                setFlash('danger', 'Invalid image file. Please upload JPG, PNG, or WebP under 10MB.');
                header('Location: ' . ADMIN_URL . 'banners.php');
                exit;
            }
        }

        $ins = $pdo->prepare("
            INSERT INTO `banners` (`title`, `subtitle`, `description`, `button_text`, `button_url`, `image`, `theme`, `status`, `sort_order`)
            VALUES (:title, :subtitle, :description, :btext, :burl, :image, :theme, :status, :sort)
        ");
        $ins->execute([
            ':title' => $title,
            ':subtitle' => $subtitle,
            ':description' => $description,
            ':btext' => $buttonText,
            ':burl' => $buttonUrl,
            ':image' => $imagePath,
            ':theme' => $theme,
            ':status' => $status,
            ':sort' => $sortOrder
        ]);
        setFlash('success', 'New hero banner added successfully.');
    } elseif ($action === 'save_edit' && $editId > 0) {
        $curStmt = $pdo->prepare('SELECT image FROM `banners` WHERE id = :id LIMIT 1');
        $curStmt->execute([':id' => $editId]);
        $currentImage = $curStmt->fetchColumn();

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['image'], 'banners');
            if ($uploaded) {
                deleteUploadedImage((string)$currentImage);
                $currentImage = $uploaded;
            }
        }

        $upd = $pdo->prepare("
            UPDATE `banners` 
            SET `title` = :title, `subtitle` = :subtitle, `description` = :description, 
                `button_text` = :btext, `button_url` = :burl, `image` = :image, 
                `theme` = :theme, `status` = :status, `sort_order` = :sort
            WHERE id = :id
        ");
        $upd->execute([
            ':title' => $title,
            ':subtitle' => $subtitle,
            ':description' => $description,
            ':btext' => $buttonText,
            ':burl' => $buttonUrl,
            ':image' => $currentImage,
            ':theme' => $theme,
            ':status' => $status,
            ':sort' => $sortOrder,
            ':id' => $editId
        ]);
        setFlash('success', 'Banner updated successfully.');
    }

    header('Location: ' . ADMIN_URL . 'banners.php');
    exit;
}

if ($action === 'edit' && $editId > 0) {
    $eStmt = $pdo->prepare('SELECT * FROM `banners` WHERE id = :id LIMIT 1');
    $eStmt->execute([':id' => $editId]);
    $bannerToEdit = $eStmt->fetch();
}

// Fetch all banners ordered by sort_order
$bannersList = $pdo->query('SELECT * FROM `banners` ORDER BY sort_order ASC, id ASC')->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="admin-content">
  
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1" style="color:var(--admin-ink);">Hero Banner Sliders</h1>
      <p class="text-muted small mb-0">Manage full-bleed interactive slides displayed on the Waypure homepage.</p>
    </div>
    
    <button type="button" class="btn btn-admin-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#bannerModal">
      <i class="bi bi-plus-lg"></i> Add New Slide
    </button>
  </div>

  <?= renderFlash() ?>

  <!-- Banner Edit Box (if editing) -->
  <?php if ($bannerToEdit): ?>
    <div class="card-custom mb-4 border-warning">
      <div class="card-custom-header bg-warning-subtle">
        <h2 class="h5 fw-bold mb-0 text-dark">Edit Slide #<?= $bannerToEdit['id'] ?></h2>
        <a href="<?= ADMIN_URL ?>banners.php" class="btn btn-sm btn-outline-secondary">Cancel Edit</a>
      </div>
      <div class="p-4">
        <form method="POST" action="<?= ADMIN_URL ?>banners.php?action=save_edit&id=<?= $bannerToEdit['id'] ?>" enctype="multipart/form-data">
          <?= csrfField() ?>
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Slide Title <span class="text-danger">*</span></label>
              <textarea name="title" class="form-control" rows="2" required><?= e($bannerToEdit['title']) ?></textarea>
              <small class="text-muted">Use newlines for breaks (e.g. "What's really in your\ndaily water?")</small>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold">Subtitle / Eyebrow</label>
              <input type="text" name="subtitle" class="form-control" value="<?= e($bannerToEdit['subtitle'] ?? '') ?>" placeholder="e.g. PROTECT YOUR FAMILY">
            </div>

            <div class="col-12">
              <label class="form-label small fw-bold">Description Text</label>
              <textarea name="description" class="form-control" rows="2"><?= e($bannerToEdit['description'] ?? '') ?></textarea>
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold">Button Label</label>
              <input type="text" name="button_text" class="form-control" value="<?= e($bannerToEdit['button_text'] ?? 'Explore Products') ?>">
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold">Button Link URL</label>
              <input type="text" name="button_url" class="form-control" value="<?= e($bannerToEdit['button_url'] ?? '#details') ?>">
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold">Color Theme</label>
              <select name="theme" class="form-select">
                <option value="dark" <?= ($bannerToEdit['theme'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>Dark Background (White text)</option>
                <option value="light" <?= ($bannerToEdit['theme'] ?? 'dark') === 'light' ? 'selected' : '' ?>>Light Background (Dark text)</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold">Replace Slide Image</label>
              <input type="file" name="image" class="form-control" accept="image/*">
              <?php if (!empty($bannerToEdit['image'])): ?>
                <div class="mt-2">
                  <small class="text-muted d-block mb-1">Current:</small>
                  <img src="<?= e(assetUrl($bannerToEdit['image'])) ?>" alt="" style="height:60px; border-radius:8px; object-fit:cover;">
                </div>
              <?php endif; ?>
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" value="<?= (int)($bannerToEdit['sort_order'] ?? 0) ?>">
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold">Status</label>
              <select name="status" class="form-select">
                <option value="active" <?= ($bannerToEdit['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Visible)</option>
                <option value="inactive" <?= ($bannerToEdit['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive (Hidden)</option>
              </select>
            </div>

            <div class="col-12 mt-4 text-end">
              <button type="submit" class="btn btn-admin-primary px-4">Save Changes</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <!-- Banners Table Card -->
  <div class="card-custom">
    <div class="card-custom-header">
      <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Active Slider Slides (<?= count($bannersList) ?>)</h2>
    </div>

    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th style="width: 70px;">Order</th>
            <th style="width: 140px;">Preview</th>
            <th>Slide Content</th>
            <th>Theme</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($bannersList)): ?>
            <tr>
              <td colspan="6" class="text-center py-5 text-muted">No banners created yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($bannersList as $b): ?>
              <tr>
                <td class="fw-bold fs-6 text-muted text-center"><?= $b['sort_order'] ?></td>
                <td>
                  <img src="<?= e(assetUrl($b['image'])) ?>" alt="" class="rounded" style="width: 120px; height: 68px; object-fit: cover; border: 1px solid var(--admin-border);">
                </td>
                <td>
                  <div class="fw-bold" style="color:var(--admin-ink); font-size:15px;"><?= nl2br(e($b['title'])) ?></div>
                  <?php if (!empty($b['subtitle'])): ?>
                    <span class="badge bg-light text-muted border mt-1" style="font-size:10px;"><?= e($b['subtitle']) ?></span>
                  <?php endif; ?>
                  <p class="text-muted small mb-0 mt-1" style="max-width: 480px;"><?= e(substr((string)($b['description'] ?? ''), 0, 110)) ?>...</p>
                </td>
                <td>
                  <span class="badge bg-<?= $b['theme'] === 'dark' ? 'dark' : 'light text-dark border' ?>">
                    <?= ucfirst($b['theme']) ?>
                  </span>
                </td>
                <td>
                  <form method="POST" action="<?= ADMIN_URL ?>banners.php?action=toggle" class="d-inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                    <button type="submit" class="btn btn-sm p-0 border-0 badge-status badge-<?= e($b['status']) ?>" title="Click to Toggle">
                      <?= e($b['status']) ?>
                    </button>
                  </form>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <a href="<?= ADMIN_URL ?>banners.php?action=edit&id=<?= $b['id'] ?>" class="btn btn-sm btn-admin-outline" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="<?= ADMIN_URL ?>banners.php?action=delete" onsubmit="return confirm('Are you sure you want to delete this slide?');" class="d-inline">
                      <?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $b['id'] ?>">
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

  <!-- Add Banner Modal -->
  <div class="modal fade" id="bannerModal" tabindex="-1" aria-labelledby="bannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content border-0 shadow">
        <form method="POST" action="<?= ADMIN_URL ?>banners.php?action=save_create" enctype="multipart/form-data">
          <?= csrfField() ?>
          
          <div class="modal-header border-bottom">
            <h5 class="modal-title fw-bold" id="bannerModalLabel">Add New Hero Banner Slide</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Slide Title <span class="text-danger">*</span></label>
                <textarea name="title" class="form-control" rows="2" placeholder="e.g. What's really in your&#10;daily water?" required></textarea>
                <small class="text-muted">Use Enter for line breaks</small>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Subtitle / Eyebrow</label>
                <input type="text" name="subtitle" class="form-control" placeholder="e.g. PROTECT YOUR FAMILY">
              </div>

              <div class="col-12">
                <label class="form-label small fw-bold">Description Text</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Brief subtitle text under the headline..."></textarea>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold">Button Label</label>
                <input type="text" name="button_text" class="form-control" value="Explore Products">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold">Button Link URL</label>
                <input type="text" name="button_url" class="form-control" value="#details">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold">Color Theme</label>
                <select name="theme" class="form-select">
                  <option value="dark">Dark Theme (White Text)</option>
                  <option value="light">Light Theme (Dark Text)</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Slide Background Image <span class="text-danger">*</span></label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
                <small class="text-muted">High resolution landscape image (1920x1080 recommended)</small>
              </div>

              <div class="col-md-3">
                <label class="form-label small fw-bold">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="<?= count($bannersList) + 1 ?>">
              </div>

              <div class="col-md-3">
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
            <button type="submit" class="btn btn-admin-primary">Create Slide</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

