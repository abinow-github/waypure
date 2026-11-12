<?php
declare(strict_types=1);

/**
 * Waypure Admin Login Page
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';

$pdo = getDb();

if (isAdminLoggedIn()) {
    header('Location: ' . ADMIN_URL . 'index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM `admin_users` WHERE username = :u LIMIT 1');
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, (string)$user['password_hash'])) {
                // Regenerate session ID for session fixation security
                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = (int)$user['id'];
                $_SESSION['admin_username'] = (string)$user['username'];
                $_SESSION['admin_name'] = (string)$user['full_name'];

                // Update last login
                $upd = $pdo->prepare('UPDATE `admin_users` SET last_login = CURRENT_TIMESTAMP WHERE id = :id');
                $upd->execute([':id' => $user['id']]);

                setFlash('success', 'Welcome back, ' . e($user['full_name']) . '!');
                $redirectUrl = $_SESSION['admin_intended_url'] ?? (ADMIN_URL . 'index.php');
                unset($_SESSION['admin_intended_url']);

                header('Location: ' . $redirectUrl);
                exit;
            }

            $error = 'Invalid credentials provided. Please check your username and password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Waypure Control Panel</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon/favicon.svg" />
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <style>
    :root {
      --admin-ink: #173e38;
      --admin-green: #3d8837;
      --admin-accent: #2e5b4e;
    }
    body {
      font-family: 'Manrope', sans-serif;
      background: linear-gradient(135deg, #f0f7f3 0%, #e2efe8 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .login-card {
      background: #ffffff;
      border-radius: 20px;
      border: 1px solid #d8e6df;
      box-shadow: 0 15px 45px rgba(23, 62, 56, 0.08);
      width: 100%;
      max-width: 440px;
      overflow: hidden;
    }
    .login-header {
      background: #fafdfb;
      padding: 35px 30px 25px;
      text-align: center;
      border-bottom: 1px solid #e5eee8;
    }
    .brand-logo-wrap {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: #ffffff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 15px rgba(0,0,0,0.06);
      margin-bottom: 14px;
    }
    .brand-logo-wrap img {
      width: 58px;
      height: 58px;
      object-fit: contain;
    }
    .btn-login {
      background: var(--admin-ink);
      color: #ffffff;
      font-weight: 700;
      padding: 12px;
      border-radius: 12px;
      border: none;
      transition: background 0.2s ease;
    }
    .btn-login:hover {
      background: var(--admin-accent);
      color: #ffffff;
    }
  </style>
</head>
<body>

  <div class="login-card">
    <div class="login-header">
      <div class="brand-logo-wrap">
        <img src="<?= BASE_URL ?>assets/img/logo/logo.png" alt="Waypure Logo">
      </div>
      <h2 class="h4 fw-bold mb-1" style="font-family:'Outfit',sans-serif; color:var(--admin-ink);">
        way<span style="color:var(--admin-green);">pure</span> Portal
      </h2>
      <p class="text-muted small mb-0">Sign in to manage purifiers, orders &amp; banners</p>
    </div>

    <div class="p-4 p-sm-5">
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small mb-4" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <?= csrfField() ?>

        <div class="mb-3">
          <label for="username" class="form-label small fw-bold text-muted">Username</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="e.g. admin" required autofocus autocomplete="username">
          </div>
        </div>

        <div class="mb-4">
          <label for="password" class="form-label small fw-bold text-muted">Password</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
          </div>
        </div>

        <button type="submit" class="btn btn-login w-100">
          Sign In to Dashboard <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </form>

      <div class="mt-4 pt-3 border-top text-center text-muted small">
        <a href="<?= BASE_URL ?>index.php" class="text-decoration-none text-muted">
          <i class="bi bi-arrow-left me-1"></i> Return to Main Website
        </a>
      </div>
    </div>
  </div>

</body>
</html>

