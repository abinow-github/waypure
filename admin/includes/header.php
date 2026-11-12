<?php
declare(strict_types=1);

/**
 * Waypure Admin Shared Header & Topbar
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../../includes/auth_guard.php';

$pdo = getDb();
$currentAdmin = currentAdmin($pdo);

// Unread/new enquiries count
$newEnqCount = (int)$pdo->query("SELECT COUNT(*) FROM `enquiries` WHERE status = 'new'")->fetchColumn();
$activeBannersCount = (int)$pdo->query("SELECT COUNT(*) FROM `banners` WHERE status = 'active'")->fetchColumn();

$pageTitle = $pageTitle ?? 'Admin Dashboard | Waypure';
$activeNav = $activeNav ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon/favicon.svg" />
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5.3 & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <style>
    :root {
      --admin-ink: #173e38;
      --admin-green: #3d8837;
      --admin-accent: #2e5b4e;
      --admin-blue: #3985bf;
      --admin-bg: #f4f8f5;
      --admin-card-bg: #ffffff;
      --admin-border: #e1ede6;
      --admin-sidebar-w: 260px;
    }

    body {
      font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
      background-color: var(--admin-bg);
      color: #2c423b;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    h1, h2, h3, h4, h5, h6, .brand-font {
      font-family: 'Outfit', sans-serif;
    }

    /* Top Navbar */
    .admin-navbar {
      background: #ffffff;
      border-bottom: 1px solid var(--admin-border);
      height: 70px;
      position: sticky;
      top: 0;
      z-index: 1030;
      box-shadow: 0 2px 10px rgba(23, 62, 56, 0.03);
    }

    .admin-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--admin-ink);
      font-weight: 800;
      font-size: 20px;
    }

    .admin-brand img {
      width: 38px;
      height: 38px;
      object-fit: contain;
      border-radius: 50%;
    }

    /* Layout Wrapper */
    .admin-wrapper {
      display: flex;
      flex: 1;
    }

    /* Sidebar */
    .admin-sidebar {
      width: var(--admin-sidebar-w);
      background: #ffffff;
      border-right: 1px solid var(--admin-border);
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      transition: all 0.3s ease;
    }

    .admin-nav-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 20px;
      color: #557168;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      border-radius: 10px;
      margin: 3px 12px;
      transition: all 0.2s ease;
    }

    .admin-nav-link i {
      font-size: 18px;
      width: 22px;
      text-align: center;
    }

    .admin-nav-link:hover {
      background: #f0f7f3;
      color: var(--admin-ink);
    }

    .admin-nav-link.active {
      background: var(--admin-ink);
      color: #ffffff;
    }

    .admin-nav-link.active i {
      color: #6ee8c8;
    }

    /* Main Content */
    .admin-content {
      flex: 1;
      padding: 30px;
      overflow-x: hidden;
    }

    /* Custom Cards */
    .stat-card {
      background: #ffffff;
      border: 1px solid var(--admin-border);
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 4px 18px rgba(23,62,56,0.03);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(23,62,56,0.06);
    }

    .stat-icon-wrap {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
    }

    .card-custom {
      background: #ffffff;
      border: 1px solid var(--admin-border);
      border-radius: 16px;
      box-shadow: 0 4px 18px rgba(23,62,56,0.03);
      overflow: hidden;
    }

    .card-custom-header {
      padding: 20px 24px;
      border-bottom: 1px solid var(--admin-border);
      background: #ffffff;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .table th {
      font-weight: 700;
      color: #557168;
      font-size: 12.5px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid var(--admin-border);
      background-color: #fafdfb;
      padding: 14px 16px;
    }

    .table td {
      padding: 14px 16px;
      vertical-align: middle;
      font-size: 13.5px;
      border-bottom: 1px solid var(--admin-border);
    }

    .badge-status {
      padding: 6px 12px;
      border-radius: 30px;
      font-size: 11.5px;
      font-weight: 700;
      text-transform: capitalize;
    }

    .badge-active, .badge-completed {
      background: #e6f7ec;
      color: #1e7e34;
    }

    .badge-inactive, .badge-cancelled {
      background: #fde8e8;
      color: #c81e1e;
    }

    .badge-new {
      background: #e1f0fe;
      color: #1a56db;
    }

    .badge-contacted {
      background: #fdf5d6;
      color: #9f580a;
    }

    .btn-admin-primary {
      background: var(--admin-ink);
      color: #ffffff;
      font-weight: 600;
      border-radius: 10px;
      padding: 9px 20px;
      border: none;
      transition: background 0.2s ease;
    }

    .btn-admin-primary:hover {
      background: var(--admin-accent);
      color: #ffffff;
    }

    .btn-admin-outline {
      background: #ffffff;
      color: var(--admin-ink);
      border: 1px solid var(--admin-border);
      font-weight: 600;
      border-radius: 10px;
      padding: 8px 18px;
    }

    .btn-admin-outline:hover {
      background: #f0f7f3;
      color: var(--admin-ink);
    }

    @media (max-width: 991.98px) {
      .admin-sidebar {
        position: fixed;
        left: -100%;
        top: 70px;
        bottom: 0;
        z-index: 1020;
      }
      .admin-sidebar.show {
        left: 0;
      }
      .admin-content {
        padding: 20px 15px;
      }
    }
  </style>
</head>
<body>

  <!-- Top Navbar -->
  <nav class="navbar admin-navbar px-3 px-lg-4">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm btn-admin-outline d-lg-none" type="button" id="sidebar-toggle" aria-label="Toggle Navigation">
        <i class="bi bi-list fs-5"></i>
      </button>
      
      <a href="<?= ADMIN_URL ?>index.php" class="admin-brand">
        <img src="<?= BASE_URL ?>assets/img/logo/logo.png" alt="Waypure">
        <span>way<span style="color:var(--admin-green);">pure</span> <small class="text-muted fw-normal fs-6">Control Panel</small></span>
      </a>
    </div>

    <div class="d-flex align-items-center gap-3">
      <a href="<?= BASE_URL ?>index.php" target="_blank" class="btn btn-sm btn-admin-outline d-none d-sm-inline-flex align-items-center gap-2">
        <i class="bi bi-box-arrow-up-right"></i> View Website
      </a>

      <a href="<?= ADMIN_URL ?>enquiries.php?status=new" class="btn btn-sm btn-admin-outline position-relative" title="New Inquiries">
        <i class="bi bi-bell"></i>
        <?php if ($newEnqCount > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= $newEnqCount ?>
            <span class="visually-hidden">unread inquiries</span>
          </span>
        <?php endif; ?>
      </a>

      <!-- Admin Profile Dropdown -->
      <div class="dropdown">
        <button class="btn btn-sm btn-admin-outline dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-person-circle fs-6"></i>
          <span class="d-none d-md-inline fw-semibold"><?= e($currentAdmin['full_name'] ?? 'Admin') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width: 200px;">
          <li class="px-3 py-2 border-bottom">
            <div class="fw-bold"><?= e($currentAdmin['full_name'] ?? 'Admin') ?></div>
            <small class="text-muted"><?= e($currentAdmin['email'] ?? '') ?></small>
          </li>
          <li><a class="dropdown-item py-2" href="<?= ADMIN_URL ?>settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item py-2 text-danger" href="<?= ADMIN_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="admin-wrapper">

