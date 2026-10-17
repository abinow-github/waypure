<?php
require_once __DIR__ . '/db.php';

$pdo = getDb();
$sessionId = getSessionId();

$brandFilter = $_GET['brand'] ?? 'All';
$categoryFilter = $_GET['category'] ?? 'All';
$searchQuery = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM `products` WHERE 1=1";
$params = [];

if ($brandFilter !== 'All' && !empty($brandFilter)) {
    $sql .= " AND `brand` = :brand";
    $params[':brand'] = $brandFilter;
}
if ($categoryFilter !== 'All' && !empty($categoryFilter)) {
    $sql .= " AND `category` = :cat";
    $params[':cat'] = $categoryFilter;
}
if (!empty($searchQuery)) {
    $sql .= " AND (`name` LIKE :q OR `brand` LIKE :q OR `technology` LIKE :q)";
    $params[':q'] = "%$searchQuery%";
}
$sql .= " ORDER BY `id` ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#edf9f5">
  <meta name="description" content="Explore 56 Waypure, Aquaguard, Merlin, Prolife and Chrome models. Compare purification, storage and order with doorstep installation.">
  <title>Waypure Products | Explore the Complete Collection</title>

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

  <script defer src="vendor/gsap.min.js"></script>
  <script defer src="vendor/ScrollTrigger.min.js"></script>
  <script defer src="ecommerce.js"></script>
</head>
<body class="inner-page">

  <!-- Header -->
  <header class="header">
    <a class="brand" href="index.html" aria-label="Waypure home">
      <img class="brand-logo" src="assets/img/logo/logo.png" alt="Waypure Logo" width="1746" height="1724" fetchpriority="high">
      <span class="brand-name">way<span>pure</span><small>DRINK · EAT · BREATHE PURE</small></span>
    </a>
    <nav class="desktop-nav" aria-label="Main navigation">
      <a href="index.html">Home</a>
      <a href="about.html">About</a>
      <a href="products.php" class="is-active">Products</a>
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
    <a href="products.php" class="nav-cta">Explore all <span>↗</span></a>
    <button class="menu-toggle" aria-label="Open navigation" aria-expanded="false" aria-controls="mobile-nav"><span></span><span></span></button>
  </header>

  <nav id="mobile-nav" class="mobile-nav" aria-label="Mobile navigation" hidden>
    <a href="index.html">Home</a>
    <a href="about.html">About</a>
    <a href="products.php">Products</a>
    <a href="contact.html">Contact</a>
    <a href="cart.php">Shopping Cart</a>
    <a href="wishlist.php">Saved Wishlist</a>
  </nav>

  <main id="main-content">
    <section class="page-hero products-hero" aria-labelledby="page-title">
      <div class="page-current" aria-hidden="true"><span></span><span></span><span></span></div>
      <div class="page-hero-copy">
        <span class="eyebrow"><span class="live-dot"></span> OUR PRODUCTS</span>
        <h1 id="page-title" data-water-text>Your water.<br><em>Your perfect fit.</em></h1>
        <p data-water-text>Explore 56 models powered by MySQL. Compare brands, purification options and storage, and order with 1-click doorstep installation.</p>
      </div>
      <div class="page-hero-bottom">
        <span>WAYPURE / OUR PRODUCTS</span>
        <a href="#details">Keep exploring <span>↓</span></a>
      </div>
    </section>

    <!-- Catalogue Section -->
    <section class="product-section catalogue-section section-space" id="details" aria-labelledby="catalogue-title">
      <div class="catalogue-intro">
        <div class="product-copy">
          <span class="eyebrow"><span class="live-dot"></span> EXPLORE OUR PRODUCTS</span>
          <h2 id="catalogue-title">Find your<br><em>pure solution.</em></h2>
          <p class="product-description">Discover Waypure, Aquaguard, Merlin, Prolife and Chrome models. Tap "Product Details" on any purifier for specs, warranty and instant checkout.</p>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="catalogue-controls">
        <div class="catalogue-filters" role="group" aria-label="Filter products by brand">
          <button type="button" data-brand-filter="All" aria-pressed="true">All brands</button>
          <button type="button" data-brand-filter="Waypure" aria-pressed="false">Waypure</button>
          <button type="button" data-brand-filter="Aquaguard" aria-pressed="false">Aquaguard</button>
          <button type="button" data-brand-filter="Merlin" aria-pressed="false">Merlin</button>
          <button type="button" data-brand-filter="Prolife" aria-pressed="false">Prolife</button>
          <button type="button" data-brand-filter="Chrome" aria-pressed="false">Chrome</button>
        </div>
        <div class="catalogue-search">
          <label for="product-search">Find a product
            <input id="product-search" type="search" placeholder="Search model or technology…" autocomplete="off">
          </label>
          <label for="product-category">Solution
            <select id="product-category">
              <option value="All">All solutions</option>
              <option value="Water purifiers">Water purifiers</option>
              <option value="Commercial &amp; treatment">Commercial &amp; treatment</option>
              <option value="Dispensers &amp; ozone">Dispensers &amp; ozone</option>
            </select>
          </label>
        </div>
      </div>

      <p class="catalogue-count" role="status" aria-live="polite" data-catalogue-count>
        Showing <?= count($products) ?> of <?= count($products) ?> products
      </p>

      <!-- Products Grid (2-column on mobile, 4-column on desktop) -->
      <div class="catalogue-grid" id="catalogue-grid">
        <?php foreach ($products as $p): ?>
          <article class="catalogue-card" 
                   data-brand="<?= htmlspecialchars($p['brand']) ?>" 
                   data-category="<?= htmlspecialchars($p['category']) ?>" 
                   data-search="<?= htmlspecialchars(strtolower($p['brand'] . ' ' . $p['name'] . ' ' . $p['technology'] . ' ' . $p['capacity'])) ?>">
            
            <button type="button" class="card-wishlist-btn" data-wishlist-id="<?= $p['id'] ?>" data-wishlist-toggle="<?= $p['id'] ?>" aria-label="Save to Wishlist" title="Save to Wishlist">
              <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </button>

            <div class="card-glaze" aria-hidden="true"></div>
            <div class="card-image-wrap">
              <div class="card-image">
                <img src="<?= htmlspecialchars($p['image'] ?: 'assets/purifier-render.webp') ?>" alt="<?= htmlspecialchars($p['brand'] . ' ' . $p['name']) ?>" loading="lazy">
              </div>
            </div>
            
            <div class="card-body">
              <h3 tabindex="-1"><?= htmlspecialchars($p['name']) ?></h3>
              <p class="card-technology"><?= htmlspecialchars($p['technology']) ?></p>
              <div class="card-price-row">
                <strong class="card-price">₹<?= number_format((float)$p['price']) ?></strong>
              </div>
              <div class="card-actions">
                <a href="product.php?id=<?= urlencode($p['slug']) ?>" class="card-btn details-btn" style="text-align:center; text-decoration:none;">Product Details</a>
                <button type="button" class="card-btn shop-btn" data-product="<?= htmlspecialchars($p['brand'] . ' ' . $p['name']) ?>" onclick="window.open('https://wa.me/919895301810?text=' + encodeURIComponent('Hello Waypure, I am interested in <?= htmlspecialchars($p['brand'] . ' ' . $p['name']) ?>. Please share current pricing and details.'), '_blank')">Shop Now</button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

    </section>
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
            <a href="products.php">All Products</a>
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

  <!-- Instant Filtering Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const cards = Array.from(document.querySelectorAll('.catalogue-card'));
      const filters = Array.from(document.querySelectorAll('[data-brand-filter]'));
      const searchInput = document.getElementById('product-search');
      const catSelect = document.getElementById('product-category');
      const countEl = document.querySelector('[data-catalogue-count]');

      let currentBrand = 'All';

      function applyFilter() {
        const query = (searchInput?.value || '').toLowerCase().trim();
        const cat = catSelect?.value || 'All';

        let visibleCount = 0;
        cards.forEach(card => {
          const b = card.dataset.brand;
          const c = card.dataset.category;
          const s = (card.dataset.search || '').toLowerCase();

          const matchBrand = (currentBrand === 'All' || b === currentBrand);
          const matchCat = (cat === 'All' || c === cat);
          const matchQuery = (!query || s.includes(query));

          if (matchBrand && matchCat && matchQuery) {
            card.style.display = '';
            visibleCount++;
          } else {
            card.style.display = 'none';
          }
        });

        if (countEl) {
          countEl.textContent = `Showing ${visibleCount} of ${cards.length} products`;
        }
      }

      filters.forEach(btn => {
        btn.addEventListener('click', () => {
          filters.forEach(b => b.setAttribute('aria-pressed', 'false'));
          btn.setAttribute('aria-pressed', 'true');
          currentBrand = btn.dataset.brandFilter;
          applyFilter();
        });
      });

      if (searchInput) searchInput.addEventListener('input', applyFilter);
      if (catSelect) catSelect.addEventListener('change', applyFilter);
    });
  </script>
</body>
</html>
