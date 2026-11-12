<?php
declare(strict_types=1);

/**
 * Waypure Advanced Responsive Product Details Page
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = getDb();
$sessionId = getVisitorSessionId();

// Retrieve product by slug, numeric ID, or query parameter
$slugOrId = trim((string)($_GET['id'] ?? $_GET['slug'] ?? ''));

$product = null;
if (!empty($slugOrId)) {
    // 1. Try exact slug match
    $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `slug` = :slug AND `status` = 'active' LIMIT 1");
    $stmt->execute([':slug' => $slugOrId]);
    $product = $stmt->fetch();

    // 2. Try numeric id match if not found
    if (!$product && is_numeric($slugOrId)) {
        $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = :id AND `status` = 'active' LIMIT 1");
        $stmt->execute([':id' => (int)$slugOrId]);
        $product = $stmt->fetch();
    }

    // 3. Try partial name/slug match if still not found
    if (!$product) {
        $stmt = $pdo->prepare("SELECT * FROM `products` WHERE (`slug` LIKE :like OR `name` LIKE :like) AND `status` = 'active' LIMIT 1");
        $stmt->execute([':like' => '%' . $slugOrId . '%']);
        $product = $stmt->fetch();
    }
}

// Fallback to first available active product
if (!$product) {
    $product = $pdo->query("SELECT * FROM `products` WHERE `status` = 'active' ORDER BY `is_featured` DESC, `id` ASC LIMIT 1")->fetch();
}

if (!$product) {
    http_response_code(404);
    die("Product not found. <a href='" . BASE_URL . "index.php'>Return to Home</a>");
}

// Fetch Gallery Images
$gStmt = $pdo->prepare("SELECT image_path FROM `product_gallery` WHERE product_id = :pid ORDER BY sort_order ASC, id ASC");
$gStmt->execute([':pid' => $product['id']]);
$galleryRows = $gStmt->fetchAll();

$galleryImages = [];
if (!empty($product['image'])) {
    $galleryImages[] = assetUrl($product['image']);
}
foreach ($galleryRows as $grow) {
    $galleryImages[] = assetUrl($grow['image_path']);
}

// Pricing and savings calculation
$price = (float)$product['price'];
$mrp = !empty($product['mrp']) ? (float)$product['mrp'] : round($price * 1.18);
$savings = max(0, $mrp - $price);
$discountPercent = $mrp > 0 ? (int)round(($savings / $mrp) * 100) : 0;

// Features
$features = [];
if (!empty($product['features'])) {
    $decoded = json_decode((string)$product['features'], true);
    if (is_array($decoded)) {
        $features = $decoded;
    }
}

// Fetch 4 related products
$relStmt = $pdo->prepare("
    SELECT * FROM `products` 
    WHERE `id` != :id AND `status` = 'active' AND (`brand` = :brand OR `category` = :category)
    ORDER BY RAND() LIMIT 4
");
$relStmt->execute([
    ':id' => $product['id'],
    ':brand' => $product['brand'],
    ':category' => $product['category']
]);
$relatedProducts = $relStmt->fetchAll();

if (count($relatedProducts) < 4) {
    $need = 4 - count($relatedProducts);
    $existingIds = array_merge([$product['id']], array_column($relatedProducts, 'id'));
    $inClause = implode(',', array_map('intval', $existingIds));
    $fillStmt = $pdo->query("SELECT * FROM `products` WHERE `status` = 'active' AND `id` NOT IN ($inClause) ORDER BY `is_featured` DESC LIMIT $need");
    $relatedProducts = array_merge($relatedProducts, $fillStmt->fetchAll());
}

$adminWhatsApp = getSetting($pdo, 'admin_whatsapp', '919895301810');
$storeName = getSetting($pdo, 'store_name', 'Waypure');

// WhatsApp Order message
$waOrderMsg = "🌊 *{$storeName} — Direct Product Order*\n"
            . "Product: {$product['brand']} {$product['name']}\n"
            . "Price: ₹" . number_format($price) . "\n"
            . "Technology: {$product['technology']}\n"
            . "Capacity: " . ($product['capacity'] ?: 'Standard') . "\n"
            . "Please confirm availability and schedule doorstep installation in Kerala.";
$waOrderUrl = buildWhatsAppUrl($adminWhatsApp, $waOrderMsg);

$pageTitle = e($product['brand'] . ' ' . $product['name']) . " | Waypure Water Purifiers";
$pageDesc = "Buy " . e($product['brand'] . ' ' . $product['name']) . " in Kerala. " . e($product['technology']) . ". 36 Months Warranty, Free Installation & Doorstep TDS Testing.";
$currentPage = 'products';
$bodyClass = 'inner-page product-detail-page';

require_once __DIR__ . '/includes/header.php';
?>

  <main class="product-page-main" style="padding-top: 130px; max-width: 1240px; margin: 0 auto; padding-left: 5%; padding-right: 5%; padding-bottom: 80px;">
    
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" aria-label="Breadcrumb" style="display:flex; align-items:center; gap:8px; font-size:12px; margin-bottom: 30px; color: #698379;">
      <a href="<?= BASE_URL ?>index.php" style="color: inherit; text-decoration: none;">Home</a>
      <span>/</span>
      <a href="<?= BASE_URL ?>products.html" style="color: inherit; text-decoration: none;">Products</a>
      <span>/</span>
      <span style="color: var(--ecom-ink); font-weight: 700;"><?= e($product['brand']) ?> <?= e($product['name']) ?></span>
    </nav>

    <!-- Product Details Layout -->
    <div class="product-detail-layout" style="display: grid; grid-template-columns: 1.05fr 1.25fr; gap: 48px; align-items: start; margin-bottom: 70px;">

      <!-- LEFT: Interactive Image Gallery -->
      <div class="product-gallery" style="position: sticky; top: 120px;">
        <div class="product-gallery-stage" style="background: #ffffff; border: 1px solid var(--ecom-border); border-radius: 24px; padding: 40px; display: flex; align-items: center; justify-content: center; min-height: 440px; box-shadow: var(--ecom-shadow); position: relative; overflow: hidden;">
          <img src="<?= e($galleryImages[0] ?? assetUrl($product['image'])) ?>" alt="<?= e($product['brand'] . ' ' . $product['name']) ?>" id="main-product-img" style="max-width: 90%; max-height: 380px; object-fit: contain; transition: transform 0.4s ease; mix-blend-mode: multiply;">
          
          <?php if ($discountPercent > 0): ?>
            <span style="position: absolute; top: 20px; left: 20px; background: #2e7d32; color: #fff; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 30px; letter-spacing: 0.5px;">
              <?= $discountPercent ?>% OFF
            </span>
          <?php endif; ?>
        </div>

        <?php if (count($galleryImages) > 1): ?>
          <!-- Thumbnails Row -->
          <div class="product-thumbnails" style="display: flex; gap: 12px; margin-top: 16px; overflow-x: auto; padding-bottom: 6px;">
            <?php foreach ($galleryImages as $idx => $gImg): ?>
              <button type="button" class="thumb-btn <?= $idx === 0 ? 'is-active' : '' ?>" data-img="<?= e($gImg) ?>" style="border: 2px solid <?= $idx === 0 ? 'var(--waypure-green)' : '#e2ece6' ?>; border-radius: 12px; background: #fff; width: 68px; height: 68px; padding: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.25s;">
                <img src="<?= e($gImg) ?>" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain; mix-blend-mode: multiply;">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Key Certifications -->
        <div class="product-badges" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 20px;">
          <div style="background: #f0f7f3; border: 1px solid #d5e7dd; border-radius: 12px; padding: 12px 14px; font-size: 11.5px; font-weight: 700; color: #235d44; display: flex; align-items: center; gap: 8px;">
            <span>🛡️</span> <?= e($product['warranty'] ?: '36 Months Warranty') ?>
          </div>
          <div style="background: #f0f7fc; border: 1px solid #d3e7f6; border-radius: 12px; padding: 12px 14px; font-size: 11.5px; font-weight: 700; color: #1e5a87; display: flex; align-items: center; gap: 8px;">
            <span>🚚</span> Free Kerala Delivery
          </div>
          <div style="background: #f0f7fc; border: 1px solid #d3e7f6; border-radius: 12px; padding: 12px 14px; font-size: 11.5px; font-weight: 700; color: #1e5a87; display: flex; align-items: center; gap: 8px;">
            <span>🔧</span> Free Expert Installation
          </div>
          <div style="background: #f0f7f3; border: 1px solid #d5e7dd; border-radius: 12px; padding: 12px 14px; font-size: 11.5px; font-weight: 700; color: #235d44; display: flex; align-items: center; gap: 8px;">
            <span>🧪</span> Free Doorstep TDS Test
          </div>
        </div>
      </div>

      <!-- RIGHT: Meta, Pricing & Conversion -->
      <div class="product-meta">
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
          <span style="font-size: 11px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; color: var(--waypure-blue); background: #eaf3fa; padding: 5px 14px; border-radius: 20px;">
            <?= e($product['brand']) ?> · <?= e($product['category']) ?>
          </span>

          <button type="button" class="card-wishlist-btn" data-wishlist-id="<?= $product['id'] ?>" aria-label="Save to Wishlist" style="position: static; width: 38px; height: 38px;">
            <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
          </button>
        </div>

        <h1 style="font-size: clamp(28px, 3.5vw, 42px); font-weight: 700; color: var(--ecom-ink); line-height: 1.15; margin: 12px 0 14px;">
          <?= e($product['brand']) ?> <?= e($product['name']) ?>
        </h1>

        <!-- Rating & Reviews -->
        <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; margin-bottom: 22px;">
          <div style="color: #f59e0b; letter-spacing: 2px;">★★★★★</div>
          <strong style="color: var(--ecom-ink);"><?= e((string)$product['rating']) ?></strong>
          <span style="color: #698379;">(<?= e((string)$product['reviews_count']) ?> Verified Kerala Reviews)</span>
        </div>

        <!-- Pricing Block -->
        <div style="background: #fbfdfc; border: 1px solid var(--ecom-border); border-radius: 18px; padding: 22px 24px; margin-bottom: 24px;">
          <div style="display: flex; align-items: baseline; gap: 14px; flex-wrap: wrap;">
            <span style="font-size: 34px; font-weight: 800; color: var(--ecom-ink);"><?= formatPrice($price) ?></span>
            <?php if ($mrp > $price): ?>
              <span style="font-size: 18px; color: #889e94; text-decoration: line-through;"><?= formatPrice($mrp) ?></span>
              <span style="font-size: 12px; font-weight: 800; color: #2e7d32; background: #e8f5e9; padding: 3px 10px; border-radius: 20px;">
                Save <?= formatPrice($savings) ?> (<?= $discountPercent ?>% OFF)
              </span>
            <?php endif; ?>
          </div>
          <p style="font-size: 12px; color: #627b72; margin: 8px 0 0;">
            ✓ Inclusive of all taxes &nbsp;·&nbsp; ✓ Free delivery &amp; standard installation across Kerala
          </p>
        </div>

        <!-- Tech Specifications Snippet -->
        <div style="margin-bottom: 24px; display: grid; gap: 10px; font-size: 13.5px; color: #37534b;">
          <div style="display: flex; gap: 8px;">
            <strong style="color: var(--ecom-ink); min-width: 140px;">Technology:</strong>
            <span><?= e($product['technology']) ?></span>
          </div>
          <?php if (!empty($product['capacity'])): ?>
            <div style="display: flex; gap: 8px;">
              <strong style="color: var(--ecom-ink); min-width: 140px;">Tank Capacity:</strong>
              <span><?= e($product['capacity']) ?></span>
            </div>
          <?php endif; ?>
          <div style="display: flex; gap: 8px;">
            <strong style="color: var(--ecom-ink); min-width: 140px;">Warranty:</strong>
            <span><?= e($product['warranty'] ?: '36 months on electrical parts') ?></span>
          </div>
          <div style="display: flex; gap: 8px; align-items: center;">
            <strong style="color: var(--ecom-ink); min-width: 140px;">Availability:</strong>
            <span style="color: #2e7d32; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #2e7d32; display: inline-block;"></span>
              In Stock (Dispatch in 24 hrs)
            </span>
          </div>
        </div>

        <!-- Purchase & Inquiry Actions -->
        <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 30px;">
          
          <!-- Quantity Controls -->
          <div style="display: flex; align-items: center; gap: 14px;">
            <span style="font-size: 13px; font-weight: 700; color: var(--ecom-ink);">Quantity:</span>
            <div style="display: flex; align-items: center; border: 1px solid var(--ecom-border); border-radius: 10px; background: #fff; overflow: hidden;">
              <button type="button" id="qty-minus" style="width: 38px; height: 38px; background: none; border: none; font-size: 18px; cursor: pointer;">−</button>
              <input type="number" id="product-qty-input" value="1" min="1" max="10" readonly style="width: 44px; text-align: center; border: none; font-size: 14px; font-weight: 700; background: transparent;">
              <button type="button" id="qty-plus" style="width: 38px; height: 38px; background: none; border: none; font-size: 18px; cursor: pointer;">+</button>
            </div>
          </div>

          <!-- Primary CTAs: WhatsApp Order & Add to Cart -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <a href="<?= $waOrderUrl ?>" target="_blank" rel="noopener noreferrer" style="background: #25D366; color: #fff; border-radius: 14px; padding: 14px 20px; font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; box-shadow: 0 4px 14px rgba(37,211,102,0.25); transition: transform 0.25s;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm5.78 14.16c-.24.68-1.39 1.3-1.92 1.38-.49.07-1.12.1-3.23-.77-2.69-1.12-4.42-3.86-4.56-4.04-.13-.19-1.1-1.46-1.1-2.79 0-1.32.69-1.97.94-2.24.25-.26.54-.33.72-.33.18 0 .37 0 .53.01.17.01.4.06.61.57.24.57.81 1.97.88 2.12.07.15.11.33.02.52-.1.19-.15.3-.3.47-.15.18-.32.4-.46.54-.15.15-.31.32-.13.63.18.31.79 1.31 1.7 2.12 1.17 1.04 2.15 1.36 2.46 1.51.31.15.49.13.67-.08.18-.21.78-.91.99-1.22.21-.31.42-.26.71-.15.29.11 1.83.86 2.14 1.02.31.15.52.23.6.36.08.13.08.77-.16 1.45z"/>
              </svg>
              Order on WhatsApp
            </a>

            <button type="button" class="btn-add-cart" data-add-to-cart="<?= $product['id'] ?>" style="background: var(--ecom-ink); color: #fff; border-radius: 14px; padding: 14px 20px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: transform 0.25s;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
              Add to Cart
            </button>
          </div>

          <!-- Help Me Choose / Inquiry Modal Trigger -->
          <button type="button" class="enquire-button pill-button" data-product="<?= e($product['brand'] . ' ' . $product['name']) ?>" style="background: #eef5ef; color: var(--ecom-ink); border: 1px solid #c9ddce; justify-content: center; font-weight: 700;">
            Ask a Specialist About This Model <span>↗</span>
          </button>
        </div>

        <!-- Trust Badges Strip -->
        <div style="background: #fff; border: 1px solid var(--ecom-border); border-radius: 16px; padding: 16px 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; text-align: center;">
          <div>
            <div style="font-size: 20px; margin-bottom: 4px;">🚚</div>
            <strong style="font-size: 11px; display: block; color: var(--ecom-ink);">Prompt Delivery</strong>
            <span style="font-size: 10px; color: #698379;">Across Malappuram</span>
          </div>
          <div>
            <div style="font-size: 20px; margin-bottom: 4px;">🛡️</div>
            <strong style="font-size: 11px; display: block; color: var(--ecom-ink);">Warranty Covered</strong>
            <span style="font-size: 10px; color: #698379;">36 Months Support</span>
          </div>
          <div>
            <div style="font-size: 20px; margin-bottom: 4px;">💵</div>
            <strong style="font-size: 11px; display: block; color: var(--ecom-ink);">Pay on Setup</strong>
            <span style="font-size: 10px; color: #698379;">Cash or UPI</span>
          </div>
        </div>

      </div>

    </div>

    <!-- Product Overview & Specifications -->
    <div class="product-tabs-section" style="margin-bottom: 70px;">
      
      <div style="background: #fff; border: 1px solid var(--ecom-border); border-radius: 24px; padding: 36px; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(23,62,56,0.03);">
        <h2 style="font-size: 24px; font-weight: 700; color: var(--ecom-ink); margin: 0 0 16px;">Product Overview &amp; Purification Process</h2>
        <p style="font-size: 14.5px; line-height: 1.8; color: #4b665c; margin: 0 0 24px;">
          <?= nl2br(e($product['description'] ?: 'Equipped with state-of-the-art multi-stage purification technology, this purifier removes dissolved impurities, bacteria, heavy metals, and viruses while retaining natural essential minerals for healthy living.')) ?>
        </p>

        <?php if (!empty($features)): ?>
          <h3 style="font-size: 16px; font-weight: 700; color: var(--ecom-ink); margin: 24px 0 12px;">Key Engineering Highlights</h3>
          <ul style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px;">
            <?php foreach ($features as $f): ?>
              <li style="display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #235445; font-weight: 600; background: #f3f8f5; padding: 10px 14px; border-radius: 10px;">
                <span style="color: #2e7d32; font-weight: 800;">✓</span> <?= e((string)$f) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <!-- Technical Specifications Table -->
      <div style="background: #fff; border: 1px solid var(--ecom-border); border-radius: 24px; padding: 36px; box-shadow: 0 4px 20px rgba(23,62,56,0.03);">
        <h2 style="font-size: 24px; font-weight: 700; color: var(--ecom-ink); margin: 0 0 20px;">Technical Specifications</h2>
        
        <table class="specs-table" style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
          <tbody>
            <tr style="border-bottom: 1px solid var(--ecom-border);">
              <td style="padding: 14px 0; color: #627b72; width: 35%;">Brand</td>
              <td style="padding: 14px 0; font-weight: 700; color: var(--ecom-ink);"><?= e($product['brand']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--ecom-border);">
              <td style="padding: 14px 0; color: #627b72;">Model Name</td>
              <td style="padding: 14px 0; font-weight: 700; color: var(--ecom-ink);"><?= e($product['name']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--ecom-border);">
              <td style="padding: 14px 0; color: #627b72;">Category</td>
              <td style="padding: 14px 0; font-weight: 700; color: var(--ecom-ink);"><?= e($product['category']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--ecom-border);">
              <td style="padding: 14px 0; color: #627b72;">Purification Technology</td>
              <td style="padding: 14px 0; font-weight: 700; color: var(--ecom-ink);"><?= e($product['technology']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--ecom-border);">
              <td style="padding: 14px 0; color: #627b72;">Purification Tank Capacity</td>
              <td style="padding: 14px 0; font-weight: 700; color: var(--ecom-ink);"><?= e($product['capacity'] ?: '8 - 12 Litres') ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--ecom-border);">
              <td style="padding: 14px 0; color: #627b72;">Comprehensive Warranty</td>
              <td style="padding: 14px 0; font-weight: 700; color: var(--ecom-ink);"><?= e($product['warranty'] ?: '36 months') ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--ecom-border);">
              <td style="padding: 14px 0; color: #627b72;">Suitable Water Source</td>
              <td style="padding: 14px 0; font-weight: 700; color: var(--ecom-ink);">Open Well, Borewell, Tap &amp; Municipal Supply (Up to 2000 TDS)</td>
            </tr>
            <tr>
              <td style="padding: 14px 0; color: #627b72;">Installation Support</td>
              <td style="padding: 14px 0; font-weight: 700; color: var(--ecom-ink);">Free Wall-mount or Tabletop Setup by Waypure Technician</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
      <div class="related-products-section">
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 28px;">
          <div>
            <span class="eyebrow" style="margin-bottom: 6px;"><span class="live-dot"></span> SIMILAR MODELS</span>
            <h2 style="font-size: 28px; font-weight: 700; color: var(--ecom-ink); margin: 0;">You May Also Consider</h2>
          </div>
          <a href="<?= BASE_URL ?>products.html" style="font-size: 13px; font-weight: 700; color: var(--waypure-blue); text-decoration: none;">View all models ↗</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
          <?php foreach ($relatedProducts as $rel): ?>
            <div class="catalogue-card" style="margin: 0; background: #fff; padding: 22px 20px; border-radius: 18px; border: 1px solid var(--ecom-border);">
              <button type="button" class="card-wishlist-btn" data-wishlist-id="<?= $rel['id'] ?>" aria-label="Save to Wishlist" title="Save to Wishlist">
                <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
              </button>
              
              <div class="card-image-wrap" style="height: 180px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                <img src="<?= e(assetUrl($rel['image'])) ?>" alt="<?= e($rel['brand'] . ' ' . $rel['name']) ?>" style="max-height: 160px; max-width: 85%; object-fit: contain; mix-blend-mode: multiply;">
              </div>
              
              <div class="card-body">
                <h3 style="font-size: 18px; margin: 0 0 4px;"><?= e($rel['name']) ?></h3>
                <p class="card-technology" style="font-size: 11px; margin-bottom: 12px; color: #627b72;"><?= e($rel['technology']) ?></p>
                <div class="card-price-row" style="margin-bottom: 14px;">
                  <strong class="card-price" style="font-size: 20px;"><?= formatPrice($rel['price']) ?></strong>
                </div>
                <div class="card-actions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                  <a href="<?= BASE_URL ?>product-details.php?id=<?= urlencode((string)$rel['slug']) ?>" class="card-btn details-btn" style="text-align: center; text-decoration: none; font-size: 11px; padding: 8px 10px;">Details</a>
                  <button type="button" class="card-btn shop-btn" data-add-to-cart="<?= $rel['id'] ?>" style="font-size: 11px; padding: 8px 10px;">+ Cart</button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </main>

  <!-- Interactive Gallery & Quantity Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Gallery thumbnail switcher
      const mainImg = document.getElementById('main-product-img');
      const thumbBtns = document.querySelectorAll('.thumb-btn');

      thumbBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          thumbBtns.forEach(b => {
            b.classList.remove('is-active');
            b.style.borderColor = '#e2ece6';
          });
          btn.classList.add('is-active');
          btn.style.borderColor = 'var(--waypure-green)';
          
          if (mainImg) {
            mainImg.style.opacity = '0.4';
            mainImg.src = btn.dataset.img;
            setTimeout(() => { mainImg.style.opacity = '1'; }, 150);
          }
        });
      });

      // Quantity buttons
      const qtyInput = document.getElementById('product-qty-input');
      const minusBtn = document.getElementById('qty-minus');
      const plusBtn = document.getElementById('qty-plus');

      if (qtyInput && minusBtn && plusBtn) {
        minusBtn.addEventListener('click', () => {
          let val = parseInt(qtyInput.value, 10) || 1;
          if (val > 1) qtyInput.value = val - 1;
        });
        plusBtn.addEventListener('click', () => {
          let val = parseInt(qtyInput.value, 10) || 1;
          if (val < 10) qtyInput.value = val + 1;
        });
      }
    });
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

