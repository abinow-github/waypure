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
  <meta name="description" content="Secure checkout for your Waypure water purifiers. Pay on delivery or installation across Kerala.">
  <title>Checkout & Order Placement | Waypure Pure Living</title>

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
<body class="inner-page checkout-page">

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

  <!-- Main Checkout Page Content -->
  <main class="ecom-page-wrap">
    
    <div class="ecom-page-title-wrap">
      <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <a href="index.html">Home</a>
        <span>/</span>
        <a href="cart.php">Cart</a>
        <span>/</span>
        <span style="color:var(--ecom-ink); font-weight:700;">Checkout</span>
      </nav>
      <h1>Express Checkout</h1>
      <p>Enter your delivery address in Kerala and choose your preferred payment option.</p>
    </div>

    <?php if (empty($cart['items'])): ?>
      <div class="ecom-empty-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <h3>Your cart is empty</h3>
        <p>Please add a water purifier before checking out.</p>
        <a href="products.html" class="checkout-action-btn" style="display:inline-flex; width:auto; padding:12px 30px;">
          Explore Purifiers
        </a>
      </div>
    <?php else: ?>
      <div class="checkout-page-grid">
        
        <!-- Left: Delivery Details Form -->
        <div class="checkout-form-box">
          <h2>1. Delivery Address</h2>
          <form id="checkout-form" novalidate>
            <div class="form-grid-2">
              <div class="form-field">
                <label for="cust-name">Full Name <span class="req">*</span></label>
                <input type="text" id="cust-name" name="name" placeholder="e.g. Rahul Menon" required>
              </div>
              <div class="form-field">
                <label for="cust-phone">Phone Number <span class="req">*</span></label>
                <input type="tel" id="cust-phone" name="phone" placeholder="e.g. 98953 01810" required>
              </div>
            </div>

            <div class="form-field">
              <label for="cust-email">Email Address <span style="font-weight:400; color:#859b91;">(Optional for invoice)</span></label>
              <input type="email" id="cust-email" name="email" placeholder="e.g. rahul@example.com">
            </div>

            <div class="form-field">
              <label for="cust-address">Street Address / House Name <span class="req">*</span></label>
              <textarea id="cust-address" name="address" rows="2" placeholder="House name, Landmark, Road name" required></textarea>
            </div>

            <div class="form-grid-2">
              <div class="form-field">
                <label for="cust-city">City / Town <span class="req">*</span></label>
                <input type="text" id="cust-city" name="city" placeholder="e.g. Angadipuram, Perinthalmanna" required>
              </div>
              <div class="form-field">
                <label for="cust-pincode">Pincode <span class="req">*</span></label>
                <input type="text" id="cust-pincode" name="pincode" placeholder="e.g. 679321" required>
              </div>
            </div>

            <h2 style="margin-top:30px;">2. Payment Method</h2>
            <div class="payment-method-selector">
              <label class="pay-radio-card is-selected" id="pay-cod-label">
                <input type="radio" name="payment_method" value="cod" checked>
                <div class="pay-radio-info">
                  <div class="pay-radio-title">Cash on Delivery / Pay after Installation</div>
                  <div class="pay-radio-sub">Inspect and test purifier at your home, then pay via cash or UPI to technician</div>
                </div>
              </label>

              <label class="pay-radio-card" id="pay-upi-label">
                <input type="radio" name="payment_method" value="upi">
                <div class="pay-radio-info">
                  <div class="pay-radio-title">UPI / QR Code on Delivery (GPay / PhonePe / Paytm)</div>
                  <div class="pay-radio-sub">Scan UPI QR directly upon arrival of the Waypure service team</div>
                </div>
              </label>
            </div>

            <div class="form-field" style="margin-top:20px;">
              <label for="cust-notes">Order Notes / Water Type <span style="font-weight:400; color:#859b91;">(Optional)</span></label>
              <input type="text" id="cust-notes" name="notes" placeholder="e.g. Open well water, please test TDS on arrival">
            </div>

            <button type="submit" id="submit-order-btn" class="checkout-action-btn" style="margin-top:24px;">
              Place Order Now 🛡️
            </button>
          </form>
        </div>

        <!-- Right: Order Summary -->
        <div class="cart-summary-box">
          <h2>Order Summary (<?= $cart['count'] ?> <?= $cart['count'] === 1 ? 'item' : 'items' ?>)</h2>
          
          <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:20px;">
            <?php foreach ($cart['items'] as $it): ?>
              <div style="display:flex; align-items:center; gap:12px; font-size:13px;">
                <div style="width:48px; height:48px; background:#f4f9f6; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <img src="<?= htmlspecialchars($it['image'] ?: 'assets/purifier-render.webp') ?>" alt="" style="max-width:80%; max-height:80%; object-fit:contain; mix-blend-mode:multiply;">
                </div>
                <div style="flex:1; min-width:0;">
                  <div style="font-weight:700; color:var(--ecom-ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    <?= htmlspecialchars($it['brand']) ?> <?= htmlspecialchars($it['name']) ?>
                  </div>
                  <div style="font-size:11px; color:#6b827a;">Qty: <?= $it['quantity'] ?> × ₹<?= number_format((float)$it['price']) ?></div>
                </div>
                <div style="font-weight:800; color:var(--ecom-ink);">
                  ₹<?= number_format((float)$it['subtotal']) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="summary-line">
            <span>Subtotal</span>
            <span><?= $cart['formatted_subtotal'] ?></span>
          </div>
          <div class="summary-line">
            <span>Standard Kerala Delivery</span>
            <span style="color:#2e7d32; font-weight:700;">FREE</span>
          </div>
          <div class="summary-line">
            <span>Standard Installation</span>
            <span style="color:#2e7d32; font-weight:700;">FREE</span>
          </div>
          <div class="summary-line total-line">
            <span>Total to Pay</span>
            <span><?= $cart['formatted_subtotal'] ?></span>
          </div>

          <div style="margin-top:20px; padding:16px; border-radius:12px; background:#edf8f2; font-size:12px; color:#2e6b48; line-height:1.5;">
            ✓ 36 Months Comprehensive Warranty<br>
            ✓ Professional Installation by Certified Technicians<br>
            ✓ Free Water Quality / TDS Testing Included
          </div>
        </div>

      </div>
    <?php endif; ?>

  </main>

  <!-- Order Success Modal Container (Injected on submit) -->
  <div id="order-success-modal" style="display:none;"></div>

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

  <!-- Checkout Submission Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Radio selection styling
      const radios = document.querySelectorAll('input[name="payment_method"]');
      radios.forEach(r => {
        r.addEventListener('change', () => {
          document.querySelectorAll('.pay-radio-card').forEach(c => c.classList.remove('is-selected'));
          r.closest('.pay-radio-card').classList.add('is-selected');
        });
      });

      const form = document.getElementById('checkout-form');
      const submitBtn = document.getElementById('submit-order-btn');
      if (!form) return;

      form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const name = document.getElementById('cust-name').value.trim();
        const phone = document.getElementById('cust-phone').value.trim();
        const address = document.getElementById('cust-address').value.trim();
        const city = document.getElementById('cust-city').value.trim();
        const pincode = document.getElementById('cust-pincode').value.trim();
        const email = document.getElementById('cust-email').value.trim();
        const notes = document.getElementById('cust-notes').value.trim();
        const payment_method = document.querySelector('input[name="payment_method"]:checked')?.value || 'cod';

        if (!name || !phone || !address || !city || !pincode) {
          alert('Please fill all required fields (*)');
          return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing Order...';

        try {
          const res = await fetch('api/checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              name, phone, email, address, city, pincode, notes, payment_method
            })
          });

          const data = await res.json();
          if (data && data.success) {
            // Show Success Modal
            const modal = document.getElementById('order-success-modal');
            modal.style.display = 'block';
            modal.innerHTML = `
              <div class="order-modal-backdrop">
                <div class="order-modal-content">
                  <div class="order-success-icon">✓</div>
                  <h2 style="font-size:24px; font-weight:800; color:var(--ecom-ink); margin:0 0 6px;">Order Confirmed!</h2>
                  <p style="color:#5f7870; font-size:14px; margin:0;">Thank you, ${escapeHtml(name)}. Your order has been placed successfully in our system.</p>
                  <div class="order-modal-id">Order ID: ${data.order_code}</div>
                  <div style="font-size:18px; font-weight:800; color:var(--ecom-ink); margin-bottom:20px;">
                    Total: ${data.formatted_total}
                  </div>
                  
                  <a href="${data.whatsapp_url}" target="_blank" rel="noopener noreferrer" class="btn-wa-confirm">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm5.78 14.16c-.24.68-1.39 1.3-1.92 1.38-.49.07-1.12.1-3.23-.77-2.69-1.12-4.42-3.86-4.56-4.04-.13-.19-1.1-1.46-1.1-2.79 0-1.32.69-1.97.94-2.24.25-.26.54-.33.72-.33.18 0 .37 0 .53.01.17.01.4.06.61.57.24.57.81 1.97.88 2.12.07.15.11.33.02.52-.1.19-.15.3-.3.47-.15.18-.32.4-.46.54-.15.15-.31.32-.13.63.18.31.79 1.31 1.7 2.12 1.17 1.04 2.15 1.36 2.46 1.51.31.15.49.13.67-.08.18-.21.78-.91.99-1.22.21-.31.42-.26.71-.15.29.11 1.83.86 2.14 1.02.31.15.52.23.6.36.08.13.08.77-.16 1.45z"/>
                    </svg>
                    Confirm Order on WhatsApp ↗
                  </a>

                  <a href="products.html" style="display:inline-block; margin-top:10px; font-size:13px; font-weight:700; color:#5b786f; text-decoration:none;">
                    Back to Products
                  </a>
                </div>
              </div>
            `;

            // Update header badges
            document.querySelectorAll('.cart-badge').forEach(b => {
              b.textContent = '0';
              b.style.display = 'none';
            });
          } else {
            alert(data.message || 'Error submitting order');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Place Order Now 🛡️';
          }
        } catch (err) {
          alert('Network or server error while placing order.');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Place Order Now 🛡️';
        }
      });
    });

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
  </script>
</body>
</html>
