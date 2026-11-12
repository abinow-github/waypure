<?php
declare(strict_types=1);

/**
 * Waypure Admin Dashboard
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$pdo = getDb();
$pageTitle = 'Dashboard | Waypure Admin Control Panel';
$activeNav = 'dashboard';

// Calculate KPI Metrics
$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
$activeProducts = (int)$pdo->query("SELECT COUNT(*) FROM `products` WHERE status = 'active'")->fetchColumn();

$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM `categories`")->fetchColumn();
$activeBanners = (int)$pdo->query("SELECT COUNT(*) FROM `banners` WHERE status = 'active'")->fetchColumn();

$totalEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM `enquiries`")->fetchColumn();
$newEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM `enquiries` WHERE status = 'new'")->fetchColumn();

$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM `enquiries` WHERE status IN ('completed', 'contacted')")->fetchColumn();

// Fetch 10 most recent inquiries
$recentEnqStmt = $pdo->query("
    SELECT * FROM `enquiries` 
    ORDER BY id DESC LIMIT 10
");
$recentEnquiries = $recentEnqStmt->fetchAll();

// Fetch Category Distribution
$catStatsStmt = $pdo->query("
    SELECT c.name, c.slug, COUNT(p.id) as product_count
    FROM `categories` c
    LEFT JOIN `products` p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY product_count DESC
");
$catStats = $catStatsStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="admin-content">
  
  <!-- Page Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1" style="color:var(--admin-ink);">System Overview</h1>
      <p class="text-muted small mb-0">Live status of Waypure water purifiers, WhatsApp orders, and customer inquiries.</p>
    </div>
    
    <div class="d-flex gap-2">
      <a href="<?= ADMIN_URL ?>product-form.php" class="btn btn-admin-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i> Add Product
      </a>
      <a href="<?= ADMIN_URL ?>banners.php" class="btn btn-admin-outline d-inline-flex align-items-center gap-2">
        <i class="bi bi-image"></i> Add Banner
      </a>
    </div>
  </div>

  <?= renderFlash() ?>

  <!-- KPI Statistics Grid -->
  <div class="row g-3 mb-4">
    
    <!-- Products KPI -->
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <span class="text-muted small fw-bold text-uppercase" style="letter-spacing:0.5px;">Products</span>
          <div class="stat-icon-wrap" style="background:#e8f4fd; color:#2378bc;">
            <i class="bi bi-box-seam"></i>
          </div>
        </div>
        <div class="h2 fw-bold mb-1" style="color:var(--admin-ink);"><?= $totalProducts ?></div>
        <div class="small text-muted">
          <span class="text-success fw-bold"><?= $activeProducts ?></span> Active in Catalog
        </div>
      </div>
    </div>

    <!-- WhatsApp Orders & Enquiries KPI -->
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <span class="text-muted small fw-bold text-uppercase" style="letter-spacing:0.5px;">Inquiries &amp; Orders</span>
          <div class="stat-icon-wrap" style="background:#eaf8ef; color:#25D366;">
            <i class="bi bi-whatsapp"></i>
          </div>
        </div>
        <div class="h2 fw-bold mb-1" style="color:var(--admin-ink);"><?= $totalEnquiries ?></div>
        <div class="small text-muted">
          <span class="badge bg-danger rounded-pill"><?= $newEnquiries ?> New</span> requiring attention
        </div>
      </div>
    </div>

    <!-- Categories KPI -->
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <span class="text-muted small fw-bold text-uppercase" style="letter-spacing:0.5px;">Categories</span>
          <div class="stat-icon-wrap" style="background:#f3eefb; color:#7e4fc9;">
            <i class="bi bi-grid"></i>
          </div>
        </div>
        <div class="h2 fw-bold mb-1" style="color:var(--admin-ink);"><?= $totalCategories ?></div>
        <div class="small text-muted">Organized purifier classifications</div>
      </div>
    </div>

    <!-- Active Banners KPI -->
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <span class="text-muted small fw-bold text-uppercase" style="letter-spacing:0.5px;">Hero Banners</span>
          <div class="stat-icon-wrap" style="background:#fef7e6; color:#d97706;">
            <i class="bi bi-images"></i>
          </div>
        </div>
        <div class="h2 fw-bold mb-1" style="color:var(--admin-ink);"><?= $activeBanners ?></div>
        <div class="small text-muted">Slides on Homepage Slider</div>
      </div>
    </div>

  </div>

  <!-- Main Dashboard Sections -->
  <div class="row g-4">
    
    <!-- Left: Recent Enquiries Table -->
    <div class="col-xl-8">
      <div class="card-custom">
        <div class="card-custom-header">
          <div class="d-flex align-items-center gap-2">
            <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Recent Inquiries &amp; Orders</h2>
            <?php if ($newEnquiries > 0): ?>
              <span class="badge bg-danger rounded-pill"><?= $newEnquiries ?> New</span>
            <?php endif; ?>
          </div>
          <a href="<?= ADMIN_URL ?>enquiries.php" class="btn btn-sm btn-admin-outline">View All</a>
        </div>

        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Code</th>
                <th>Customer</th>
                <th>Type / Product</th>
                <th>Location</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentEnquiries)): ?>
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">No inquiries or orders recorded yet.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($recentEnquiries as $enq): ?>
                  <tr>
                    <td>
                      <span class="fw-bold font-monospace small" style="color:var(--admin-ink);"><?= e($enq['enquiry_code']) ?></span>
                      <div class="text-muted" style="font-size:11px;"><?= formatDate($enq['created_at'], 'd M, h:i A') ?></div>
                    </td>
                    <td>
                      <div class="fw-bold"><?= e($enq['customer_name']) ?></div>
                      <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?= e($enq['customer_phone']) ?></div>
                    </td>
                    <td>
                      <div class="text-truncate" style="max-width: 180px;" title="<?= e($enq['product_name'] ?? '') ?>">
                        <?= e($enq['product_name'] ?: 'General Enquiry') ?>
                      </div>
                      <span class="badge bg-light text-dark border" style="font-size:10px;"><?= e(strtoupper(str_replace('_', ' ', $enq['type']))) ?></span>
                    </td>
                    <td><?= e($enq['city']) ?></td>
                    <td>
                      <span class="badge-status badge-<?= e($enq['status']) ?>">
                        <?= e($enq['status']) ?>
                      </span>
                    </td>
                    <td class="text-end">
                      <div class="d-inline-flex gap-1">
                        <?php if (!empty($enq['whatsapp_url'])): ?>
                          <a href="<?= e($enq['whatsapp_url']) ?>" target="_blank" class="btn btn-sm btn-success px-2 py-1" title="Chat on WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                          </a>
                        <?php endif; ?>
                        <a href="<?= ADMIN_URL ?>enquiries.php?highlight=<?= $enq['id'] ?>" class="btn btn-sm btn-admin-outline px-2 py-1" title="Manage Details">
                          <i class="bi bi-eye"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right: Quick Navigation & Category Stats -->
    <div class="col-xl-4">
      
      <!-- Quick Actions Card -->
      <div class="card-custom mb-4">
        <div class="card-custom-header">
          <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Quick Management</h2>
        </div>
        <div class="p-3 d-grid gap-2">
          <a href="<?= ADMIN_URL ?>product-form.php" class="btn btn-admin-outline text-start d-flex align-items-center justify-content-between p-3">
            <span class="d-flex align-items-center gap-2">
              <i class="bi bi-plus-circle text-primary fs-5"></i>
              <span class="fw-semibold">Add New Purifier</span>
            </span>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>

          <a href="<?= ADMIN_URL ?>banners.php?action=create" class="btn btn-admin-outline text-start d-flex align-items-center justify-content-between p-3">
            <span class="d-flex align-items-center gap-2">
              <i class="bi bi-images text-warning fs-5"></i>
              <span class="fw-semibold">Add Hero Slider Banner</span>
            </span>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>

          <a href="<?= ADMIN_URL ?>categories.php" class="btn btn-admin-outline text-start d-flex align-items-center justify-content-between p-3">
            <span class="d-flex align-items-center gap-2">
              <i class="bi bi-folder-plus text-success fs-5"></i>
              <span class="fw-semibold">Manage Categories</span>
            </span>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>

          <a href="<?= ADMIN_URL ?>settings.php" class="btn btn-admin-outline text-start d-flex align-items-center justify-content-between p-3">
            <span class="d-flex align-items-center gap-2">
              <i class="bi bi-whatsapp text-success fs-5"></i>
              <span class="fw-semibold">Store &amp; WhatsApp Settings</span>
            </span>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
        </div>
      </div>

      <!-- Categories Summary Card -->
      <div class="card-custom">
        <div class="card-custom-header">
          <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Categories</h2>
          <a href="<?= ADMIN_URL ?>categories.php" class="btn btn-sm btn-admin-outline">Manage</a>
        </div>
        <div class="list-group list-group-flush">
          <?php foreach ($catStats as $cs): ?>
            <div class="list-group-item d-flex align-items-center justify-content-between px-4 py-3">
              <div>
                <div class="fw-bold text-dark"><?= e($cs['name']) ?></div>
                <div class="text-muted small" style="font-size:11px;"><?= e($cs['slug']) ?></div>
              </div>
              <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                <?= $cs['product_count'] ?> models
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

