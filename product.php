<?php
require_once __DIR__ . '/db.php';

$pdo = getDb();
$sessionId = getSessionId();

// Retrieve product by slug, numeric ID, or query parameter
$slugOrId = trim($_GET['id'] ?? $_GET['slug'] ?? '');

$product = null;
if (!empty($slugOrId)) {
    // 1. Try exact slug match
    $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `slug` = :slug LIMIT 1");
    $stmt->execute([':slug' => $slugOrId]);
    $product = $stmt->fetch();

    // 2. Try numeric id match if not found
    if (!$product && is_numeric($slugOrId)) {
        $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => (int)$slugOrId]);
        $product = $stmt->fetch();
    }

    // 3. Try partial name/slug match if still not found
    if (!$product) {
        $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `slug` LIKE :like OR `name` LIKE :like LIMIT 1");
        $stmt->execute([':like' => '%' . $slugOrId . '%']);
        $product = $stmt->fetch();
    }
}

// Fallback to first product if query is empty or not found
if (!$product) {
    $product = $pdo->query("SELECT * FROM `products` ORDER BY `id` ASC LIMIT 1")->fetch();
}

if (!$product) {
    die("No products available in the database.");
}

// Compute discount and parsed features
$mrp = (float)($product['mrp'] ?: $product['price'] * 1.15);
$price = (float)$product['price'];
$savings = max(0, $mrp - $price);
$discountPercent = $mrp > 0 ? round(($savings / $mrp) * 100) : 0;

$features = [];
if (!empty($product['features'])) {
    $decoded = json_decode($product['features'], true);
    if (is_array($decoded)) {
        $features = $decoded;
    }
}

// Fetch 4 related products
$relStmt = $pdo->prepare("
    SELECT * FROM `products` 
    WHERE `id` != :id AND (`brand` = :brand OR `category` = :category)
    ORDER BY RAND() LIMIT 4
");
$relStmt->execute([
    ':id' => $product['id'],
    ':brand' => $product['brand'],
    ':category' => $product['category']
]);
$relatedProducts = $relStmt->fetchAll();

// If related count is less than 4, fill with other products
if (count($relatedProducts) < 4) {
    $need = 4 - count($relatedProducts);
    $existingIds = array_merge([$product['id']], array_column($relatedProducts, 'id'));
    $inClause = implode(',', array_map('intval', $existingIds));
    $fillStmt = $pdo->query("SELECT * FROM `products` WHERE `id` NOT IN ($inClause) LIMIT $need");
    $relatedProducts = array_merge($relatedProducts, $fillStmt->fetchAll());
}

$pageTitle = htmlspecialchars($product['brand'] . ' ' . $product['name']) . " | Waypure Water Purifiers";
$waText = "Hello Waypure, I am interested in purchasing " . $product['brand'] . " " . $product['name'] . " (₹" . number_format($price) . "). Please confirm stock and delivery to my location.";
$waUrl = "https://wa.me/919895301810?text=" . urlencode($waText);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#edf9f5">
  <meta name="description" content="<?= htmlspecialchars(substr($product['description'] ?: 'Pure water solutions for home & commercial use by Waypure.', 0, 160)) ?>">
  <title><?= $pageTitle ?></title>

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
<body class="inner-page product-detail-page">

  <!-- Main Header -->
  <header class="header">
    <a class="brand" href="index.html" aria-label="Waypure home">
      <img class="brand-logo" src="assets/img/logo/logo.png" alt="Waypure Logo" width="1746" height="1724" fetchpriority="high">
      <span class="brand-name">way<span>pure</span><small>DRINK · EAT · BREATHE PURE</small></span>
    </a>
    <nav class="desktop-nav" aria-label="Main navigation">
      <a href="index.html">Home</a>
      <a href="about.html">About</a>
      <a href="products.html" class="is-active">Products</a>
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

  <!-- Main Product Detail Container -->
  <main class="product-page-main">
    
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
      <a href="index.html">Home</a>
      <span>/</span>
      <a href="products.html">Products</a>
      <span>/</span>
      <a href="products.html?brand=<?= urlencode($product['brand']) ?>"><?= htmlspecialchars($product['brand']) ?></a>
      <span>/</span>
      <span style="color:var(--ecom-ink); font-weight:700;"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Product Details 2-Column Grid -->
    <div class="product-detail-layout">

      <!-- LEFT: Image Stage & Guarantees -->
      <div class="product-gallery">
        <div class="product-gallery-stage">
          <img src="<?= htmlspecialchars($product['image'] ?: 'assets/purifier-render.webp') ?>" alt="<?= htmlspecialchars($product['brand'] . ' ' . $product['name']) ?>" id="main-product-img">
        </div>
        <div class="product-badges">
          <span class="p-badge p-badge-green">🛡️ <?= htmlspecialchars($product['warranty'] ?: '36 months') ?> Warranty</span>
          <span class="p-badge p-badge-blue">✨ 100% Genuine Certified</span>
          <span class="p-badge p-badge-green">🚚 Free Kerala Delivery</span>
          <span class="p-badge p-badge-blue">🔧 Free Standard Installation</span>
        </div>
      </div>

      <!-- RIGHT: Product Meta & Purchase Options -->
      <div class="product-meta">
        
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
          <div class="p-brand-tag"><?= htmlspecialchars($product['brand']) ?> · <?= htmlspecialchars($product['category']) ?></div>
          <button type="button" class="card-wishlist-btn" data-wishlist-id="<?= $product['id'] ?>" aria-label="Toggle Wishlist" style="position:static;">
            <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
          </button>
        </div>

        <h1><?= htmlspecialchars($product['brand']) ?> <?= htmlspecialchars($product['name']) ?></h1>

        <div class="product-rating-row">
          <div class="p-stars">★★★★★</div>
          <strong style="color:var(--ecom-ink);"><?= htmlspecialchars($product['rating']) ?></strong>
          <span>(<?= htmlspecialchars($product['reviews_count']) ?> Verified Kerala Customer Reviews)</span>
        </div>

        <!-- Pricing Card -->
        <div class="product-price-box">
          <div class="p-current-price">₹<?= number_format($price) ?></div>
          <?php if ($mrp > $price): ?>
            <div class="p-original-mrp">₹<?= number_format($mrp) ?></div>
            <div class="p-discount-pill"><?= $discountPercent ?>% OFF</div>
          <?php endif; ?>
          <div style="width:100%; font-size:12px; color:#5c786e; margin-top:4px;">
            Inclusive of all taxes · Free doorstep installation across Malappuram & Kerala
          </div>
        </div>

        <!-- Tech Spec Highlights -->
        <div class="p-tech-highlight">
          <div style="font-weight:700; color:var(--ecom-ink); margin-bottom:4px;">Purification Technology:</div>
          <div><?= htmlspecialchars($product['technology']) ?></div>
          <?php if (!empty($product['capacity'])): ?>
            <div style="margin-top:6px;"><strong style="color:var(--ecom-ink);">Storage Capacity:</strong> <?= htmlspecialchars($product['capacity']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Stock Indicator -->
        <div style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#2e7d32;">
          <span style="width:8px; height:8px; border-radius:50%; background:#2e7d32; display:inline-block;"></span>
          In Stock &mdash; Ready for dispatch within 24 hours
        </div>

        <!-- Action Buttons Group -->
        <div class="p-action-group">
          
          <!-- Quantity Row -->
          <div class="p-qty-row">
            <span class="p-qty-label">Quantity:</span>
            <div class="p-qty-selector">
              <button type="button" class="p-qty-btn" id="qty-dec" aria-label="Decrease quantity">&minus;</button>
              <input type="number" id="product-qty-input" class="p-qty-input" value="1" min="1" max="10" readonly>
              <button type="button" class="p-qty-btn" id="qty-inc" aria-label="Increase quantity">+</button>
            </div>
          </div>

          <!-- Main CTA Buttons: Add to Cart & Buy Now -->
          <div class="p-btn-row">
            <button type="button" class="btn-add-cart" data-add-to-cart="<?= $product['id'] ?>">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
              Add to Cart
            </button>
            <button type="button" class="btn-buy-now" data-buy-now="<?= $product['id'] ?>">
              ⚡ Buy Now
            </button>
          </div>

          <!-- Secondary Buttons: WhatsApp Order & Wishlist -->
          <div class="p-sub-btns">
            <a href="<?= $waUrl ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-order">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm5.78 14.16c-.24.68-1.39 1.3-1.92 1.38-.49.07-1.12.1-3.23-.77-2.69-1.12-4.42-3.86-4.56-4.04-.13-.19-1.1-1.46-1.1-2.79 0-1.32.69-1.97.94-2.24.25-.26.54-.33.72-.33.18 0 .37 0 .53.01.17.01.4.06.61.57.24.57.81 1.97.88 2.12.07.15.11.33.02.52-.1.19-.15.3-.3.47-.15.18-.32.4-.46.54-.15.15-.31.32-.13.63.18.31.79 1.31 1.7 2.12 1.17 1.04 2.15 1.36 2.46 1.51.31.15.49.13.67-.08.18-.21.78-.91.99-1.22.21-.31.42-.26.71-.15.29.11 1.83.86 2.14 1.02.31.15.52.23.6.36.08.13.08.77-.16 1.45z"/>
              </svg>
              Enquire on WhatsApp
            </a>
            <button type="button" class="btn-wishlist-toggle" data-wishlist-toggle="<?= $product['id'] ?>" data-wishlist-id="<?= $product['id'] ?>">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
              </svg>
              Save to Wishlist
            </button>
          </div>

        </div>

        <!-- Trust Badges Strip -->
        <div class="trust-strip">
          <div class="trust-item">
            <div class="trust-icon">🛡️</div>
            <div>
              <strong>3-Year Warranty</strong>
              <div style="font-size:10px; color:#60756e;">Complete service coverage</div>
            </div>
          </div>
          <div class="trust-item">
            <div class="trust-icon">🚚</div>
            <div>
              <strong>Free Setup</strong>
              <div style="font-size:10px; color:#60756e;">Professional installation</div>
            </div>
          </div>
          <div class="trust-item">
            <div class="trust-icon">🧪</div>
            <div>
              <strong>Lab Certified</strong>
              <div style="font-size:10px; color:#60756e;">Guaranteed pure output</div>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- Product Description & Specifications -->
    <div class="product-tabs-section">
      
      <div style="background:#fff; border:1px solid var(--ecom-border); border-radius:20px; padding:32px; margin-bottom:30px;">
        <h2 style="font-size:22px; font-weight:800; color:var(--ecom-ink); margin:0 0 16px;">Product Overview</h2>
        <p style="color:#49655c; line-height:1.7; font-size:15px; margin:0 0 20px;">
          <?= nl2br(htmlspecialchars($product['description'] ?: 'Equipped with state-of-the-art multi-stage purification technology, this purifier removes dissolved impurities, bacteria, heavy metals, and viruses while retaining natural essential minerals for healthy living.')) ?>
        </p>

        <?php if (!empty($features)): ?>
          <h3 style="font-size:16px; font-weight:700; color:var(--ecom-ink); margin:20px 0 10px;">Key Features</h3>
          <ul style="list-style:none; padding:0; display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:10px;">
            <?php foreach ($features as $f): ?>
              <li style="display:flex; align-items:center; gap:8px; font-size:14px; color:#2e5b4e; font-weight:600;">
                <span style="color:#2e7d32;">✓</span> <?= htmlspecialchars($f) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <!-- Specifications Table -->
      <div style="background:#fff; border:1px solid var(--ecom-border); border-radius:20px; padding:32px;">
        <h2 style="font-size:22px; font-weight:800; color:var(--ecom-ink); margin:0 0 20px;">Technical Specifications</h2>
        <table class="specs-table">
          <tbody>
            <tr>
              <td>Brand</td>
              <td><?= htmlspecialchars($product['brand']) ?></td>
            </tr>
            <tr>
              <td>Model</td>
              <td><?= htmlspecialchars($product['name']) ?></td>
            </tr>
            <tr>
              <td>Category</td>
              <td><?= htmlspecialchars($product['category']) ?></td>
            </tr>
            <tr>
              <td>Purification Technology</td>
              <td><?= htmlspecialchars($product['technology']) ?></td>
            </tr>
            <tr>
              <td>Storage Capacity</td>
              <td><?= htmlspecialchars($product['capacity'] ?: '8 - 12 Litres') ?></td>
            </tr>
            <tr>
              <td>Warranty</td>
              <td><?= htmlspecialchars($product['warranty'] ?: '36 months on electrical parts') ?></td>
            </tr>
            <tr>
              <td>Installation Type</td>
              <td>Wall Mount / Countertop Placement</td>
            </tr>
            <tr>
              <td>Suitable For</td>
              <td>Borewell, Municipal, Tanker, and Well Water</td>
            </tr>
            <tr>
              <td>Service Availability</td>
              <td>Angadipuram, Pulamanthole, Perinthalmanna & all Kerala districts</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Related Products Section -->
    <div style="margin-top:70px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
        <div>
          <span class="eyebrow" style="color:var(--ecom-bright); font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase;">YOU MAY ALSO LIKE</span>
          <h2 style="font-size:26px; font-weight:800; color:var(--ecom-ink); margin:4px 0 0;">Related Purifiers</h2>
        </div>
        <a href="products.html" style="font-size:13px; font-weight:700; color:var(--ecom-ink); text-decoration:none;">View All <span>↗</span></a>
      </div>

      <div class="catalogue-grid" style="grid-template-columns:repeat(auto-fill, minmax(240px, 1fr));">
        <?php foreach ($relatedProducts as $rel): ?>
          <article class="catalogue-card" style="position:relative;">
            <button type="button" class="card-wishlist-btn" data-wishlist-id="<?= $rel['id'] ?>" aria-label="Toggle Wishlist">
              <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </button>
            <div class="card-image-wrap">
              <div class="card-image">
                <img src="<?= htmlspecialchars($rel['image'] ?: 'assets/purifier-render.webp') ?>" alt="<?= htmlspecialchars($rel['brand'] . ' ' . $rel['name']) ?>" loading="lazy">
              </div>
            </div>
            <div class="card-body">
              <h3><?= htmlspecialchars($rel['name']) ?></h3>
              <p class="card-technology"><?= htmlspecialchars($rel['technology']) ?></p>
              <div class="card-price-row">
                <strong class="card-price">₹<?= number_format((float)$rel['price']) ?></strong>
              </div>
              <div class="card-actions">
                <a href="product.php?id=<?= urlencode($rel['slug']) ?>" class="card-btn details-btn" style="text-align:center; text-decoration:none;">Product Details</a>
                <button type="button" class="card-btn shop-btn" data-product="<?= htmlspecialchars($rel['brand'] . ' ' . $rel['name']) ?>" onclick="window.open('https://wa.me/919895301810?text=' + encodeURIComponent('Hello Waypure, I am interested in <?= htmlspecialchars($rel['brand'] . ' ' . $rel['name']) ?>. Please share current pricing and details.'), '_blank')">Shop Now</button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
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

  <!-- Quantity controller helper script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const input = document.getElementById('product-qty-input');
      const dec = document.getElementById('qty-dec');
      const inc = document.getElementById('qty-inc');
      if (input && dec && inc) {
        dec.addEventListener('click', () => {
          let v = parseInt(input.value, 10) || 1;
          if (v > 1) input.value = v - 1;
        });
        inc.addEventListener('click', () => {
          let v = parseInt(input.value, 10) || 1;
          if (v < 10) input.value = v + 1;
        });
      }
    });
  </script>
</body>
</html>
