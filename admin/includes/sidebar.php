<?php
declare(strict_types=1);

/**
 * Waypure Admin Sidebar Navigation
 * Pure Core PHP 8.4+ Architecture
 */

$activeNav = $activeNav ?? 'dashboard';
?>
<!-- Sidebar -->
<aside class="admin-sidebar py-3" id="admin-sidebar">
  <div class="px-3 mb-2 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 1px;">
    Main Navigation
  </div>

  <nav class="nav flex-column mb-auto">
    <a href="<?= ADMIN_URL ?>index.php" class="admin-nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard</span>
    </a>

    <a href="<?= ADMIN_URL ?>banners.php" class="admin-nav-link <?= $activeNav === 'banners' ? 'active' : '' ?>">
      <i class="bi bi-images"></i>
      <span>Hero Sliders</span>
    </a>

    <a href="<?= ADMIN_URL ?>categories.php" class="admin-nav-link <?= $activeNav === 'categories' ? 'active' : '' ?>">
      <i class="bi bi-grid"></i>
      <span>Categories</span>
    </a>

    <a href="<?= ADMIN_URL ?>products.php" class="admin-nav-link <?= in_array($activeNav, ['products', 'product-form'], true) ? 'active' : '' ?>">
      <i class="bi bi-box-seam"></i>
      <span>Products</span>
    </a>

    <a href="<?= ADMIN_URL ?>enquiries.php" class="admin-nav-link <?= $activeNav === 'enquiries' ? 'active' : '' ?>">
      <i class="bi bi-whatsapp"></i>
      <span class="flex-grow-1">WhatsApp & Orders</span>
      <?php if (!empty($newEnqCount) && $newEnqCount > 0): ?>
        <span class="badge bg-danger rounded-pill" style="font-size: 10px;"><?= $newEnqCount ?></span>
      <?php endif; ?>
    </a>

    <a href="<?= ADMIN_URL ?>settings.php" class="admin-nav-link <?= $activeNav === 'settings' ? 'active' : '' ?>">
      <i class="bi bi-sliders"></i>
      <span>Store Settings</span>
    </a>
  </nav>

  <div class="px-3 pt-3 mt-auto border-top">
    <a href="<?= ADMIN_URL ?>logout.php" class="admin-nav-link text-danger m-0 px-2">
      <i class="bi bi-box-arrow-right"></i>
      <span>Sign Out</span>
    </a>
  </div>
</aside>

