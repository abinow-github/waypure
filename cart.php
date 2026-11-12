<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pdo = getDb();
$sessionId = getVisitorSessionId();
$cart = getCartSummary($pdo, $sessionId);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#edf9f5">
  <meta name="description" content="View and manage your selected Waypure purifiers, calculate totals, and proceed to checkout.">
  <title>Shopping Cart | Waypure Pure Living</title>

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
<body class="inner-page cart-page">

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
      <a href="wishlist.php" class="ecom-nav-btn wishlist-nav-btn" aria-label="Wishlist" title="Wishlist">
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

  <!-- Cart Content Area -->
  <main class="ecom-page-wrap">
    
    <div class="ecom-page-title-wrap">
      <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <a href="index.html">Home</a>
        <span>/</span>
        <a href="products.html">Products</a>
        <span>/</span>
        <span style="color:var(--ecom-ink); font-weight:700;">Shopping Cart</span>
      </nav>
      <h1>Your Shopping Cart</h1>
      <p>Review your selected water purifiers and proceed to secure checkout.</p>
    </div>

    <div id="cart-content-view">
      <?php if (empty($cart['items'])): ?>
        <div class="ecom-empty-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          <h3>Your cart is empty</h3>
          <p>You haven't added any purifiers yet. Explore our 56 premium models and find your pure solution.</p>
          <a href="products.html" class="checkout-action-btn" style="display:inline-flex; width:auto; padding:12px 30px;">
            Explore All Purifiers
          </a>
        </div>
      <?php else: ?>
        <div class="cart-page-grid">
          
          <!-- Left: Items list -->
          <div class="cart-items-card">
            <div id="cart-page-items-list">
              <?php foreach ($cart['items'] as $item): ?>
                <div class="cart-page-item" data-product-id="<?= $item['product_id'] ?>">
                  <div class="cart-page-img">
                    <img src="<?= htmlspecialchars($item['image'] ?: 'assets/purifier-render.webp') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                  </div>
                  <div class="cart-page-info">
                    <a href="product.php?id=<?= urlencode($item['slug']) ?>" class="cart-page-name">
                      <?= htmlspecialchars($item['brand']) ?> <?= htmlspecialchars($item['name']) ?>
                    </a>
                    <div class="cart-page-sub">
                      <?= htmlspecialchars($item['technology']) ?> <?= $item['capacity'] ? '· ' . htmlspecialchars($item['capacity']) : '' ?>
                    </div>
                    <div class="cart-page-controls">
                      <div class="cart-qty-ctrl">
                        <button type="button" class="cart-qty-btn" onclick="WaypureEcom.updateCartQuantity(<?= $item['product_id'] ?>, <?= $item['quantity'] - 1 ?>)">&minus;</button>
                        <span class="cart-qty-val"><?= $item['quantity'] ?></span>
                        <button type="button" class="cart-qty-btn" onclick="WaypureEcom.updateCartQuantity(<?= $item['product_id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                      </div>
                      <button type="button" class="cart-item-del" onclick="WaypureEcom.removeCartItem(<?= $item['product_id'] ?>)" title="Remove item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <polyline points="3 6 5 6 21 6"></polyline>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                  <div class="cart-page-price">
                    ₹<?= number_format((float)$item['subtotal']) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Right: Summary Box -->
          <div class="cart-summary-box">
            <h2>Order Summary</h2>
            <div class="summary-line">
              <span>Items Total (<span id="cart-page-count"><?= $cart['count'] ?></span>)</span>
              <span id="cart-page-subtotal"><?= $cart['formatted_subtotal'] ?></span>
            </div>
            <div class="summary-line">
              <span>Delivery & Doorstep Setup</span>
              <span style="color:#2e7d32; font-weight:700;">FREE</span>
            </div>
            <div class="summary-line">
              <span>3-Year Warranty Registration</span>
              <span style="color:#2e7d32; font-weight:700;">FREE</span>
            </div>
            <div class="summary-line total-line">
              <span>Estimated Total</span>
              <span id="cart-page-total"><?= $cart['formatted_subtotal'] ?></span>
            </div>
            <a href="checkout.php" class="checkout-action-btn">
              Proceed to Checkout <span>↗</span>
            </a>
            <div style="margin-top:20px; padding:16px; border-radius:12px; background:#f4f9f6; font-size:12px; color:#4e6c62; line-height:1.5;">
              🛡️ <strong>Waypure Assurance:</strong> Pay on delivery or installation available. 100% genuine water purifiers directly tested and verified.
            </div>
          </div>

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

  <!-- Script to live re-render cart page on events -->
  <script>
    function renderCartEmptyView() {
      const container = document.getElementById('cart-content-view');
      if (!container) return;
      container.innerHTML = `
        <div class="ecom-empty-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          <h3>Your cart is empty</h3>
          <p>You haven't added any purifiers yet. Explore our 56 premium models and find your pure solution.</p>
          <a href="products.html" class="checkout-action-btn" style="display:inline-flex; width:auto; padding:12px 30px;">
            Explore All Purifiers
          </a>
        </div>
      `;
    }

    window.addEventListener('waypure:cart-updated', function(e) {
      const cart = e.detail;
      if (!cart || cart.count === 0) {
        renderCartEmptyView();
        return;
      }
      const countEl = document.getElementById('cart-page-count');
      const subtotalEl = document.getElementById('cart-page-subtotal');
      const totalEl = document.getElementById('cart-page-total');
      if (countEl) countEl.textContent = cart.count;
      if (subtotalEl) subtotalEl.textContent = cart.formatted_subtotal;
      if (totalEl) totalEl.textContent = cart.formatted_subtotal;

      // Update or remove rows
      const currentIds = new Set(cart.items.map(it => Number(it.product_id)));
      document.querySelectorAll('#cart-page-items-list .cart-page-item').forEach(el => {
        const pid = Number(el.dataset.productId);
        if (!currentIds.has(pid)) {
          el.remove();
        } else {
          const it = cart.items.find(i => Number(i.product_id) === pid);
          if (it) {
            const qtyVal = el.querySelector('.cart-qty-val');
            const priceVal = el.querySelector('.cart-page-price');
            if (qtyVal) qtyVal.textContent = it.quantity;
            if (priceVal) priceVal.textContent = '₹' + Number(it.subtotal).toLocaleString('en-IN');
          }
        }
      });
    });
  </script>
</body>
</html>
