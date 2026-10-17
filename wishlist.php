<?php
require_once __DIR__ . '/db.php';

$pdo = getDb();
$sessionId = getSessionId();
$wishlist = getWishlistSummary($pdo, $sessionId);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#edf9f5">
  <meta name="description" content="View your saved Waypure purifiers, compare models, and move them to cart.">
  <title>Your Wishlist | Waypure Pure Living</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="favicon/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="favicon/favicon.svg" />
  <link rel="shortcut icon" href="favicon/favicon.ico" />

  <!-- Fonts & Styles -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="water-theme.css">
  <link rel="stylesheet" href="water-effects.css">
  <link rel="stylesheet" href="fresh-water.css">
  <link rel="stylesheet" href="waypure.css">
  <link rel="stylesheet" href="pages.css">
  <link rel="stylesheet" href="ecommerce.css">

  <script defer src="ecommerce.js"></script>
</head>
<body class="inner-page wishlist-page">

  <!-- Header -->
  <header class="header">
    <a class="brand" href="index.html" aria-label="Waypure home">
      <img class="brand-logo" src="assets/img/logo/logo.png" alt="Waypure Logo" width="1746" height="1724" fetchpriority="high">
      <span class="brand-name">way<span>pure</span><small>DRINK · EAT · BREATHE PURE</small></span>
    </a>
    <nav class="desktop-nav" aria-label="Main navigation">
      <a href="index.html">Home</a>
      <a href="about.html">About</a>
      <a href="products.html">Products</a>
      <a href="contact.html">Contact</a>
    </nav>
    <div class="header-actions">
      <a href="wishlist.php" class="ecom-nav-btn wishlist-nav-btn is-active" aria-label="Wishlist" title="Wishlist">
        <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        <span class="ecom-badge wishlist-badge" style="display:none;">0</span>
      </a>
      <button type="button" class="ecom-nav-btn cart-nav-btn" id="open-cart-drawer" aria-label="Shopping Cart" title="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <span class="ecom-badge cart-badge" style="display:none;">0</span>
      </button>
    </div>
    <a href="products.html" class="nav-cta">Explore all <span>↗</span></a>
    <button class="menu-toggle" aria-label="Open navigation" aria-expanded="false" aria-controls="mobile-nav"><span></span><span></span></button>
  </header>

  <nav id="mobile-nav" class="mobile-nav" aria-label="Mobile navigation" hidden>
    <a href="index.html">Home</a>
    <a href="about.html">About</a>
    <a href="products.html">Products</a>
    <a href="contact.html">Contact</a>
    <a href="cart.php">Shopping Cart</a>
    <a href="wishlist.php">Saved Wishlist</a>
  </nav>

  <!-- Wishlist Main Content -->
  <main class="ecom-page-wrap">
    
    <div class="ecom-page-title-wrap">
      <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <a href="index.html">Home</a>
        <span>/</span>
        <a href="products.html">Products</a>
        <span>/</span>
        <span style="color:var(--ecom-ink); font-weight:700;">Saved Wishlist</span>
      </nav>
      <h1>Your Wishlist</h1>
      <p>Save and compare your favorite purifiers before making a decision.</p>
    </div>

    <div id="wishlist-content-view">
      <?php if (empty($wishlist['items'])): ?>
        <div class="ecom-empty-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
          </svg>
          <h3>Your wishlist is empty</h3>
          <p>Tap the heart icon on any product to save it here for later.</p>
          <a href="products.html" class="checkout-action-btn" style="display:inline-flex; width:auto; padding:12px 30px;">
            Explore All Purifiers
          </a>
        </div>
      <?php else: ?>
        <div class="wishlist-grid">
          <?php foreach ($wishlist['items'] as $item): ?>
            <div class="wishlist-item-card" data-product-id="<?= $item['product_id'] ?>">
              <div class="wishlist-item-img">
                <img src="<?= htmlspecialchars($item['image'] ?: 'assets/purifier-render.webp') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
              </div>
              <a href="product.php?id=<?= urlencode($item['slug']) ?>" class="wishlist-item-title">
                <?= htmlspecialchars($item['brand']) ?> <?= htmlspecialchars($item['name']) ?>
              </a>
              <div class="wishlist-item-tech">
                <?= htmlspecialchars($item['technology']) ?>
              </div>
              <div class="wishlist-item-price">
                ₹<?= number_format((float)$item['price']) ?>
              </div>
              <div class="wishlist-btn-row">
                <button type="button" class="btn-move-cart" data-move-to-cart="<?= $item['product_id'] ?>">
                  Move to Cart 🛒
                </button>
                <button type="button" class="btn-wish-del" data-wishlist-toggle="<?= $item['product_id'] ?>" data-wishlist-id="<?= $item['product_id'] ?>" title="Remove from wishlist">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                  </svg>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-wrap">
      <div class="footer-top">
        <div class="footer-brand">
          <div class="brand">
            <img class="brand-logo" src="assets/img/logo/logo.png" alt="Waypure" width="1746" height="1724">
            <span class="brand-name">way<span>pure</span><small>DRINK · EAT · BREATHE PURE</small></span>
          </div>
          <p class="footer-tagline">Advanced RO, UV and Mineral purification systems designed to safeguard every drop you and your family consume.</p>
        </div>
        <div class="footer-nav">
          <div class="footer-col">
            <h4>Explore</h4>
            <a href="index.html">Home</a>
            <a href="about.html">About Us</a>
            <a href="products.html">All Products</a>
            <a href="contact.html">Contact & Service</a>
          </div>
          <div class="footer-col">
            <h4>E-Commerce</h4>
            <a href="cart.php">Shopping Cart</a>
            <a href="wishlist.php">Saved Wishlist</a>
            <a href="checkout.php">Express Checkout</a>
          </div>
          <div class="footer-col">
            <h4>Showrooms</h4>
            <p><strong>Angadipuram:</strong> Near Railway Overbridge, Angadipuram, Kerala</p>
            <p><strong>Pulamanthole:</strong> Main Road, Pulamanthole, Kerala</p>
            <p>Phone: +91 98953 01810</p>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Waypure Water Purifiers. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Script to live update wishlist UI on changes without page reloads -->
  <script>
    function renderWishlistView(wishlist) {
      const container = document.getElementById('wishlist-content-view');
      if (!container) return;

      if (!wishlist || !wishlist.items || wishlist.items.length === 0) {
        container.innerHTML = `
          <div class="ecom-empty-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <h3>Your wishlist is empty</h3>
            <p>Tap the heart icon on any product to save it here for later.</p>
            <a href="products.html" class="checkout-action-btn" style="display:inline-flex; width:auto; padding:12px 30px;">
              Explore All Purifiers
            </a>
          </div>
        `;
        return;
      }

      container.innerHTML = `
        <div class="wishlist-grid" id="wishlist-grid">
          ${wishlist.items.map(item => `
            <div class="wishlist-item-card" data-product-id="${item.product_id}">
              <div class="wishlist-item-img">
                <img src="${item.image || 'assets/purifier-render.webp'}" alt="${escapeHtml(item.name)}">
              </div>
              <a href="product.php?id=${encodeURIComponent(item.slug)}" class="wishlist-item-title">
                ${escapeHtml(item.brand)} ${escapeHtml(item.name)}
              </a>
              <div class="wishlist-item-tech">
                ${escapeHtml(item.technology || '')}
              </div>
              <div class="wishlist-item-price">
                ₹${Number(item.price).toLocaleString('en-IN')}
              </div>
              <div class="wishlist-btn-row">
                <button type="button" class="btn-move-cart" data-move-to-cart="${item.product_id}">
                  Move to Cart 🛒
                </button>
                <button type="button" class="btn-wish-del" data-wishlist-toggle="${item.product_id}" data-wishlist-id="${item.product_id}" title="Remove from wishlist">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                  </svg>
                </button>
              </div>
            </div>
          `).join('')}
        </div>
      `;
    }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    window.addEventListener('waypure:wishlist-updated', function(e) {
      if (e.detail) {
        renderWishlistView(e.detail);
      }
    });
  </script>
</body>
</html>
