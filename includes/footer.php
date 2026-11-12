<?php
declare(strict_types=1);

/**
 * Waypure Frontend Shared Footer & Modals
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/functions.php';

$pdo = getDb();
$adminPhone = getSetting($pdo, 'admin_phone', '+91 90610 91810');
$adminWhatsApp = getSetting($pdo, 'admin_whatsapp', '919895301810');
$angadipuramAddr = getSetting($pdo, 'address_angadipuram', 'Opp. Nexa Showroom, Angadipuram<br>Perinthalmanna, Malappuram · 679322');
$pulamantholeAddr = getSetting($pdo, 'address_pulamanthole', 'Tangalpadi, UP Pulamanthole');

// Fetch products for dynamic enquiry select dropdown
$enquiryProds = $pdo->query("SELECT id, brand, name FROM `products` WHERE status = 'active' ORDER BY brand ASC, name ASC")->fetchAll();
?>
  <!-- Dynamic Footer -->
  <footer class="water-footer" id="footer">
    <div class="footer-locations">
      <div>
        <span class="eyebrow">ANGADIPURAM SHOWROOM</span>
        <address><?= $angadipuramAddr ?></address>
      </div>
      <div>
        <span class="eyebrow">PULAMANTHOLE SHOWROOM</span>
        <address><?= $pulamantholeAddr ?></address>
      </div>
      <div class="footer-phones">
        <a href="tel:<?= preg_replace('/\D/', '', $adminPhone) ?>"><?= e($adminPhone) ?> <span>Call ↗</span></a>
        <a href="https://wa.me/<?= preg_replace('/\D/', '', $adminWhatsApp) ?>" target="_blank" rel="noopener">+91 <?= substr(preg_replace('/\D/', '', $adminWhatsApp), -10) ?> <span>WhatsApp ↗</span></a>
      </div>
    </div>
    
    <nav class="footer-page-links" aria-label="Footer navigation">
      <a href="<?= BASE_URL ?>index.php">Home</a>
      <a href="<?= BASE_URL ?>about.html">About</a>
      <a href="<?= BASE_URL ?>products.html">Products</a>
      <a href="<?= BASE_URL ?>contact.html">Contact</a>
      <a href="<?= BASE_URL ?>cart.php">Cart</a>
      <a href="<?= BASE_URL ?>wishlist.php">Wishlist</a>
      <a href="<?= BASE_URL ?>admin/login.php" style="opacity:0.6;">Admin Area</a>
    </nav>
    
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> Waypure Water Purifiers. Pure Living Solutions.</span>
      <button class="footer-contact enquire-button" type="button">Let’s talk ↗</button>
    </div>
  </footer>

  <!-- Floating Contact Action Drops -->
  <nav class="floating-contact" aria-label="Contact Waypure">
    <a class="contact-drop contact-call" href="tel:<?= preg_replace('/\D/', '', $adminPhone) ?>" data-contact="call" aria-label="Call Waypure">
      <svg class="contact-shell" viewBox="0 0 64 80" aria-hidden="true"><defs><clipPath id="call-drop"><path d="M32 3C26 18 6 35 6 51a26 26 0 0 0 52 0C58 35 38 18 32 3Z"/></clipPath></defs><path class="contact-surface" d="M32 3C26 18 6 35 6 51a26 26 0 0 0 52 0C58 35 38 18 32 3Z"/><g clip-path="url(#call-drop)"><g class="contact-fill"><path class="contact-wave" d="M-64 36Q-48 24-32 36T0 36T32 36T64 36T96 36V180H-64Z"/></g></g><path class="contact-shine" d="M14 53c0 10 7 16 15 16"/></svg>
      <svg class="contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m8.2 3.5 2 4.5-2.4 2a15 15 0 0 0 6.2 6.2l2-2.4 4.5 2v3.5c0 .8-.7 1.5-1.5 1.4C10.5 20.1 3.9 13.5 3.3 5c-.1-.8.6-1.5 1.4-1.5Z"/></svg>
    </a>
    <a class="contact-drop contact-whatsapp" href="https://wa.me/<?= preg_replace('/\D/', '', $adminWhatsApp) ?>" target="_blank" rel="noopener" data-contact="whatsapp" aria-label="WhatsApp Waypure">
      <svg class="contact-shell" viewBox="0 0 64 80" aria-hidden="true"><defs><clipPath id="whatsapp-drop"><path d="M32 3C26 18 6 35 6 51a26 26 0 0 0 52 0C58 35 38 18 32 3Z"/></clipPath></defs><path class="contact-surface" d="M32 3C26 18 6 35 6 51a26 26 0 0 0 52 0C58 35 38 18 32 3Z"/><g clip-path="url(#whatsapp-drop)"><g class="contact-fill"><path class="contact-wave" d="M-64 36Q-48 24-32 36T0 36T32 36T64 36T96 36V180H-64Z"/></g></g><path class="contact-shine" d="M14 53c0 10 7 16 15 16"/></svg>
      <svg class="contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.4-4.7a8.5 8.5 0 1 1 16.1-4.2Z"/><path d="m8.3 7.7 1.5 2.6-1 1.1a10 10 0 0 0 3.7 3.1l1-1.2 2.6 1.3c-.1 1.5-1.3 2.2-2.6 1.9-3.6-.8-6.7-3.8-7-6.7-.1-1.1.6-1.9 1.8-2.1Z"/></svg>
    </a>
  </nav>

  <!-- Product Enquiry & Help-Me-Choose Dialog -->
  <dialog id="enquiry-dialog" aria-labelledby="enquiry-title">
    <button class="dialog-close" type="button" aria-label="Close enquiry">×</button>
    <div id="enquiry-form-view">
      <span class="eyebrow">LET’S FIND YOUR WAYPURE</span>
      <h2 id="enquiry-title">Your needs.<br><em>Our team.</em></h2>
      <p>Tell us which product or service you are interested in. We will prepare your enquiry and connect you directly with our water specialists on WhatsApp.</p>
      
      <form id="enquiry-form">
        <?= csrfField() ?>
        <input type="hidden" name="type" value="product_enquiry">
        <label>
          Product or service
          <select name="product" id="enquiry-product">
            <option value="General enquiry">Help me choose the right purifier</option>
            <?php foreach ($enquiryProds as $ep): ?>
              <option value="<?= e($ep['brand'] . ' ' . $ep['name']) ?>" data-id="<?= $ep['id'] ?>"><?= e($ep['brand'] . ' ' . $ep['name']) ?></option>
            <?php endforeach; ?>
            <option value="Air purifier enquiry">Air purifier enquiry</option>
            <option value="Ozone detoxifier enquiry">Ozone detoxifier enquiry</option>
            <option value="Water treatment / service enquiry">Water treatment / service enquiry</option>
          </select>
        </label>
        
        <label>
          Your name
          <input name="name" autocomplete="name" required maxlength="100" placeholder="Full name">
        </label>
        
        <label>
          Mobile Number (WhatsApp)
          <input name="phone" type="tel" autocomplete="tel" required maxlength="15" placeholder="+91 98765 43210">
        </label>
        
        <label>
          City or Location
          <input name="city" autocomplete="address-level2" required maxlength="100" placeholder="e.g. Angadipuram, Perinthalmanna">
        </label>
        
        <label>
          Email address <small style="color:#718078;">(Optional)</small>
          <input name="email" type="email" autocomplete="email" maxlength="150" placeholder="you@example.com">
        </label>
        
        <label>
          Questions or Water Source Details
          <textarea name="message" rows="2" maxlength="1500" placeholder="e.g. Well water or borewell, current TDS, pricing inquiry..."></textarea>
        </label>
        
        <button class="pill-button dark" type="submit" id="enquiry-submit-btn">
          Connect on WhatsApp <span>↗</span>
        </button>
      </form>
    </div>
    
    <div id="enquiry-success" hidden>
      <span class="success-mark">✓</span>
      <span class="eyebrow">ONE STEP CLOSER</span>
      <h2>Your enquiry<br>is <em>ready.</em></h2>
      <p id="enquiry-success-msg">Your enquiry has been securely logged with reference <strong id="enquiry-code-display">WP-ENQ</strong>. Continue to WhatsApp to chat with our technical expert.</p>
      <div style="display:flex; flex-direction:column; gap:12px; margin-top:20px;">
        <a id="whatsapp-enquiry" class="pill-button whatsapp-enquiry" target="_blank" rel="noopener" style="justify-content:center; background:#25D366; color:#fff; font-weight:700;">
          Open in WhatsApp Chat <span>↗</span>
        </a>
        <button class="text-button" type="button" id="edit-enquiry">Back to edit details</button>
      </div>
    </div>
  </dialog>

  <!-- Global Waypure Configuration & Event Bridge -->
  <script>
    window.WAYPURE_CONFIG = {
      baseUrl: '<?= BASE_URL ?>',
      uploadUrl: '<?= UPLOAD_URL ?>',
      csrfToken: '<?= generateCsrfToken() ?>',
      adminPhone: '<?= e($adminPhone) ?>',
      adminWhatsapp: '<?= e($adminWhatsApp) ?>'
    };

    // Attach AJAX handler to Enquiry Dialog Form
    document.addEventListener('DOMContentLoaded', function() {
      const eqForm = document.getElementById('enquiry-form');
      const eqDialog = document.getElementById('enquiry-dialog');
      const formView = document.getElementById('enquiry-form-view');
      const successView = document.getElementById('enquiry-success');
      const waLink = document.getElementById('whatsapp-enquiry');
      const codeDisplay = document.getElementById('enquiry-code-display');
      const submitBtn = document.getElementById('enquiry-submit-btn');

      if (eqForm) {
        eqForm.addEventListener('submit', async function(e) {
          e.preventDefault();
          if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Connecting... <span>⏳</span>';
          }

          const formData = new FormData(eqForm);
          const payload = Object.fromEntries(formData.entries());

          try {
            const res = await fetch('<?= BASE_URL ?>api/submit_enquiry.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= generateCsrfToken() ?>'
              },
              body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
              if (codeDisplay) codeDisplay.textContent = data.enquiry_code;
              if (waLink) {
                waLink.href = data.whatsapp_url;
              }
              if (formView) formView.hidden = true;
              if (successView) successView.hidden = false;

              // Also open WhatsApp directly in new tab after 400ms
              if (data.whatsapp_url) {
                setTimeout(() => {
                  window.open(data.whatsapp_url, '_blank', 'noopener,noreferrer');
                }, 400);
              }
            } else {
              alert(data.message || 'Error preparing enquiry.');
            }
          } catch (err) {
            console.error('Enquiry error:', err);
            alert('Could not submit enquiry. Please try again or WhatsApp us directly.');
          } finally {
            if (submitBtn) {
              submitBtn.disabled = false;
              submitBtn.innerHTML = 'Connect on WhatsApp <span>↗</span>';
            }
          }
        });
      }

      const editBtn = document.getElementById('edit-enquiry');
      if (editBtn) {
        editBtn.addEventListener('click', function() {
          if (formView) formView.hidden = false;
          if (successView) successView.hidden = true;
        });
      }
    });
  </script>

