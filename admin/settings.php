<?php
declare(strict_types=1);

/**
 * Waypure Store & Admin Settings
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$pdo = getDb();
$pageTitle = 'Store & WhatsApp Settings | Waypure Admin';
$activeNav = 'settings';

// Handle Store Settings Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'save_settings') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'settings.php');
        exit;
    }

    $keys = [
        'store_name',
        'store_tagline',
        'admin_whatsapp',
        'admin_phone',
        'admin_email',
        'address_angadipuram',
        'address_pulamanthole',
        'currency_symbol'
    ];

    foreach ($keys as $k) {
        if (isset($_POST[$k])) {
            setSetting($pdo, $k, trim((string)$_POST[$k]));
        }
    }

    setFlash('success', 'Store and WhatsApp settings updated successfully.');
    header('Location: ' . ADMIN_URL . 'settings.php');
    exit;
}

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'change_password') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'settings.php');
        exit;
    }

    $currentPass = (string)($_POST['current_password'] ?? '');
    $newPass = (string)($_POST['new_password'] ?? '');
    $confirmPass = (string)($_POST['confirm_password'] ?? '');

    $adminId = (int)$_SESSION['admin_id'];
    $stmt = $pdo->prepare('SELECT password_hash FROM `admin_users` WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $adminId]);
    $existingHash = $stmt->fetchColumn();

    if (!$existingHash || !password_verify($currentPass, (string)$existingHash)) {
        setFlash('danger', 'Your current password was entered incorrectly.');
    } elseif (strlen($newPass) < 6) {
        setFlash('danger', 'New password must be at least 6 characters long.');
    } elseif ($newPass !== $confirmPass) {
        setFlash('danger', 'New password and confirmation do not match.');
    } else {
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE `admin_users` SET password_hash = :p WHERE id = :id');
        $upd->execute([':p' => $newHash, ':id' => $adminId]);
        setFlash('success', 'Admin password changed successfully.');
    }

    header('Location: ' . ADMIN_URL . 'settings.php');
    exit;
}

$storeName = getSetting($pdo, 'store_name', 'Waypure');
$storeTagline = getSetting($pdo, 'store_tagline', 'DRINK · EAT · BREATHE PURE');
$adminWhatsApp = getSetting($pdo, 'admin_whatsapp', '919895301810');
$adminPhone = getSetting($pdo, 'admin_phone', '+91 90610 91810');
$adminEmail = getSetting($pdo, 'admin_email', 'info@waypure.in');
$addrAngadipuram = getSetting($pdo, 'address_angadipuram', 'Opp. Nexa Showroom, Angadipuram, Perinthalmanna, Malappuram · 679322');
$addrPulamanthole = getSetting($pdo, 'address_pulamanthole', 'Tangalpadi, UP Pulamanthole');
$currency = getSetting($pdo, 'currency_symbol', '₹');

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="admin-content">
  
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1" style="color:var(--admin-ink);">Settings &amp; Configuration</h1>
      <p class="text-muted small mb-0">Manage customer communication channels, WhatsApp numbers, showroom details, and security.</p>
    </div>
  </div>

  <?= renderFlash() ?>

  <div class="row g-4">
    
    <!-- Store & WhatsApp Details -->
    <div class="col-lg-7">
      <div class="card-custom">
        <div class="card-custom-header">
          <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Business &amp; WhatsApp Details</h2>
        </div>
        <div class="p-4">
          <form method="POST" action="<?= ADMIN_URL ?>settings.php?action=save_settings">
            <?= csrfField() ?>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Store Brand Name</label>
                <input type="text" name="store_name" class="form-control" value="<?= e($storeName) ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Store Tagline</label>
                <input type="text" name="store_tagline" class="form-control" value="<?= e($storeTagline) ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold text-success">
                  <i class="bi bi-whatsapp me-1"></i> Admin WhatsApp Number <span class="text-danger">*</span>
                </label>
                <input type="text" name="admin_whatsapp" class="form-control fw-bold border-success-subtle" value="<?= e($adminWhatsApp) ?>" placeholder="919895301810" required>
                <small class="text-muted">Country code without + (e.g. 919895301810). All website chats and inquiries are dispatched here.</small>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Contact Calling Phone</label>
                <input type="text" name="admin_phone" class="form-control" value="<?= e($adminPhone) ?>" placeholder="+91 90610 91810">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Customer Support Email</label>
                <input type="email" name="admin_email" class="form-control" value="<?= e($adminEmail) ?>" placeholder="info@waypure.in">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Currency Symbol</label>
                <input type="text" name="currency_symbol" class="form-control" value="<?= e($currency) ?>" placeholder="₹">
              </div>

              <div class="col-12">
                <label class="form-label small fw-bold">Angadipuram Showroom Address</label>
                <textarea name="address_angadipuram" class="form-control" rows="2"><?= e($addrAngadipuram) ?></textarea>
              </div>

              <div class="col-12">
                <label class="form-label small fw-bold">Pulamanthole Showroom Address</label>
                <textarea name="address_pulamanthole" class="form-control" rows="2"><?= e($addrPulamanthole) ?></textarea>
              </div>

              <div class="col-12 text-end mt-4">
                <button type="submit" class="btn btn-admin-primary px-4">
                  <i class="bi bi-save me-1"></i> Save Store Settings
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>
    </div>

    <!-- Security & Password Change -->
    <div class="col-lg-5">
      <div class="card-custom mb-4">
        <div class="card-custom-header">
          <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Change Admin Password</h2>
        </div>
        <div class="p-4">
          <form method="POST" action="<?= ADMIN_URL ?>settings.php?action=change_password">
            <?= csrfField() ?>

            <div class="mb-3">
              <label class="form-label small fw-bold">Current Password</label>
              <input type="password" name="current_password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">New Password (Min 6 chars)</label>
              <input type="password" name="new_password" class="form-control" placeholder="••••••••" required minlength="6" autocomplete="new-password">
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required minlength="6" autocomplete="new-password">
            </div>

            <div class="text-end mt-4">
              <button type="submit" class="btn btn-admin-outline w-100">
                <i class="bi bi-key me-1"></i> Update Password
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Quick Platform Info -->
      <div class="card-custom">
        <div class="card-custom-header">
          <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">System Environment</h2>
        </div>
        <div class="p-4 small">
          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">PHP Version:</span>
            <span class="fw-bold"><?= PHP_VERSION ?></span>
          </div>
          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">Database Server:</span>
            <span class="fw-bold">MariaDB / MySQL (PDO)</span>
          </div>
          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">Server Software:</span>
            <span class="fw-bold"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Apache / XAMPP' ?></span>
          </div>
          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">Base URL:</span>
            <code class="text-muted"><?= BASE_URL ?></code>
          </div>
          <div class="d-flex justify-content-between py-2">
            <span class="text-muted">Timezone:</span>
            <span class="fw-bold">Asia/Kolkata (IST)</span>
          </div>
        </div>
      </div>
    </div>

  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

