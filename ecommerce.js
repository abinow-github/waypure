/**
 * Waypure E-Commerce Engine
 * Cart, Wishlist, Slide-out Drawer, Badges, and Live Interactivity
 */

(function () {
  'use strict';

  // State
  let cartState = { items: [], count: 0, subtotal: 0, formatted_subtotal: '₹0' };
  let wishlistIds = new Set();
  let isDrawerOpen = false;

  // Initialize on DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEcom);
  } else {
    initEcom();
  }

  function initEcom() {
    createDrawerMarkup();
    createToastMarkup();
    bindGlobalEvents();
    syncCart();
    syncWishlist();
  }

  /* ----------------------------------------------------
     API CLIENT
  ---------------------------------------------------- */
  async function fetchJson(url, options = {}) {
    try {
        const base = (window.WAYPURE_CONFIG && window.WAYPURE_CONFIG.baseUrl) ? window.WAYPURE_CONFIG.baseUrl : '';
        const fullUrl = (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) ? url : (base + url);
        const csrf = (window.WAYPURE_CONFIG && window.WAYPURE_CONFIG.csrfToken) ? window.WAYPURE_CONFIG.csrfToken : '';

        const res = await fetch(fullUrl, {
          headers: {
            'Content-Type': 'application/json',
            ...(csrf ? { 'X-CSRF-Token': csrf } : {}),
            ...(options.headers || {})
          },
          ...options
        });
        if(!res.ok) {
          const err = await res.json().catch(() => ({ message: 'Server error' }));
      throw new Error(err.message || 'Request failed');
    }
      return await res.json();
  } catch (e) {
    console.error('E-commerce API error:', e);
    throw e;
  }
}

  /* ----------------------------------------------------
     SYNC FUNCTIONS
  ---------------------------------------------------- */
  async function syncCart() {
  try {
    const data = await fetchJson('api/cart.php?action=get');
    if (data && data.cart) {
      cartState = data.cart;
      updateCartBadges(cartState.count);
      renderCartDrawer();
      dispatchCartEvent();
    }
  } catch (e) {
    console.warn('Could not sync cart:', e);
  }
}

async function syncWishlist() {
  try {
    const data = await fetchJson('api/wishlist.php?action=get');
    if (data && data.wishlist) {
      wishlistIds = new Set(data.wishlist.ids.map(Number));
      updateWishlistBadges(data.wishlist.count);
      updateCardWishlistButtons();
      dispatchWishlistEvent(data.wishlist);
    }
  } catch (e) {
    console.warn('Could not sync wishlist:', e);
  }
}

function updateCartBadges(count) {
  const badges = document.querySelectorAll('.cart-badge');
  badges.forEach(b => {
    b.textContent = count;
    b.classList.add('badge-pop');
    setTimeout(() => b.classList.remove('badge-pop'), 300);
    b.style.display = count > 0 ? 'flex' : 'none';
  });
}

function updateWishlistBadges(count) {
  const badges = document.querySelectorAll('.wishlist-badge');
  badges.forEach(b => {
    b.textContent = count;
    b.classList.add('badge-pop');
    setTimeout(() => b.classList.remove('badge-pop'), 300);
    b.style.display = count > 0 ? 'flex' : 'none';
  });
}

function updateCardWishlistButtons() {
  document.querySelectorAll('[data-wishlist-id]').forEach(btn => {
    const pid = Number(btn.dataset.wishlistId);
    if (wishlistIds.has(pid)) {
      btn.classList.add('is-active');
      const icon = btn.querySelector('svg');
      if (icon) icon.setAttribute('fill', 'currentColor');
    } else {
      btn.classList.remove('is-active');
      const icon = btn.querySelector('svg');
      if (icon) icon.setAttribute('fill', 'none');
    }
  });
}

function dispatchCartEvent() {
  window.dispatchEvent(new CustomEvent('waypure:cart-updated', { detail: cartState }));
}

function dispatchWishlistEvent(wishlist) {
  window.dispatchEvent(new CustomEvent('waypure:wishlist-updated', { detail: wishlist }));
}

/* ----------------------------------------------------
   DRAWER MARKUP & LOGIC
---------------------------------------------------- */
function createDrawerMarkup() {
  if (document.getElementById('cart-drawer-overlay')) return;

  const overlay = document.createElement('div');
  overlay.id = 'cart-drawer-overlay';
  overlay.className = 'cart-drawer-overlay';
  overlay.innerHTML = `
      <div class="cart-drawer" role="dialog" aria-modal="true" aria-label="Shopping Cart">
        <div class="cart-drawer-header">
          <h2>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            Your Cart (<span class="drawer-header-count">0</span>)
          </h2>
          <button type="button" class="cart-drawer-close" aria-label="Close cart">&times;</button>
        </div>
        <div class="cart-drawer-body" id="cart-drawer-items">
          <!-- Rendered via JS -->
        </div>
        <div class="cart-drawer-footer" id="cart-drawer-footer">
          <div class="cart-summary-row">
            <span>Subtotal</span>
            <span class="drawer-subtotal">₹0</span>
          </div>
          <div class="cart-summary-row">
            <span>Delivery & Installation</span>
            <span style="color:#2e7d32; font-weight:700;">FREE</span>
          </div>
          <div class="cart-total-row">
            <span>Estimated Total</span>
            <span class="drawer-total">₹0</span>
          </div>
          <a href="${(window.WAYPURE_CONFIG && window.WAYPURE_CONFIG.baseUrl ? window.WAYPURE_CONFIG.baseUrl : '')}checkout.php" class="cart-checkout-btn">
            Proceed to Checkout
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </a>
          <a href="${(window.WAYPURE_CONFIG && window.WAYPURE_CONFIG.baseUrl ? window.WAYPURE_CONFIG.baseUrl : '')}cart.php" class="cart-view-btn">View Full Cart</a>
        </div>
      </div>
    `;

  document.body.appendChild(overlay);

  // Close handlers
  overlay.addEventListener('click', e => {
    if (e.target === overlay) closeCartDrawer();
  });
  overlay.querySelector('.cart-drawer-close').addEventListener('click', closeCartDrawer);

  // Escape key
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && isDrawerOpen) closeCartDrawer();
  });
}

function renderCartDrawer() {
  const container = document.getElementById('cart-drawer-items');
  const footer = document.getElementById('cart-drawer-footer');
  const headerCount = document.querySelector('.drawer-header-count');
  if (!container || !footer) return;

  if (headerCount) headerCount.textContent = cartState.count;

  if (!cartState.items || cartState.items.length === 0) {
    container.innerHTML = `
        <div class="cart-drawer-empty">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          <p style="font-size:16px; font-weight:700; color:var(--ecom-ink); margin-bottom:6px;">Your cart is empty</p>
          <p style="font-size:13px; margin-bottom:20px;">Explore our purifiers and find your pure match.</p>
          <a href="products.html" class="cart-checkout-btn" style="width:auto; padding:10px 24px; display:inline-flex;">Explore Purifiers</a>
        </div>
      `;
    footer.style.display = 'none';
    return;
  }

  footer.style.display = 'flex';
  document.querySelectorAll('.drawer-subtotal, .drawer-total').forEach(el => {
    el.textContent = cartState.formatted_subtotal;
  });

  container.innerHTML = cartState.items.map(item => `
      <div class="cart-item" data-product-id="${item.product_id}">
        <div class="cart-item-img">
          <img src="${item.image || 'assets/purifier-render.webp'}" alt="${escapeHtml(item.name)}">
        </div>
        <div class="cart-item-info">
          <a href="${(window.WAYPURE_CONFIG && window.WAYPURE_CONFIG.baseUrl ? window.WAYPURE_CONFIG.baseUrl : '')}product-details.php?id=${encodeURIComponent(item.slug)}" class="cart-item-title">${escapeHtml(item.brand)} ${escapeHtml(item.name)}</a>
          <div class="cart-item-meta">${escapeHtml(item.technology || 'RO + UV')} ${item.capacity ? '· ' + escapeHtml(item.capacity) : ''}</div>
          <div style="display:flex; align-items:center; justify-content:space-between; margin-top:6px;">
            <div class="cart-item-price">₹${Number(item.price).toLocaleString('en-IN')}</div>
            <div class="cart-qty-ctrl">
              <button type="button" class="cart-qty-btn" data-action="dec" data-id="${item.product_id}" aria-label="Decrease">&minus;</button>
              <span class="cart-qty-val">${item.quantity}</span>
              <button type="button" class="cart-qty-btn" data-action="inc" data-id="${item.product_id}" aria-label="Increase">+</button>
            </div>
          </div>
        </div>
        <button type="button" class="cart-item-del" data-action="del" data-id="${item.product_id}" aria-label="Remove item" title="Remove">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
        </button>
      </div>
    `).join('');

  // Attach event listeners for item quantity & remove buttons
  container.querySelectorAll('.cart-qty-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const pid = Number(btn.dataset.id);
      const item = cartState.items.find(it => Number(it.product_id) === pid);
      if (!item) return;
      const newQty = btn.dataset.action === 'inc' ? item.quantity + 1 : item.quantity - 1;
      updateCartQuantity(pid, newQty);
    });
  });

  container.querySelectorAll('.cart-item-del').forEach(btn => {
    btn.addEventListener('click', () => {
      const pid = Number(btn.dataset.id);
      removeCartItem(pid);
    });
  });
}

function openCartDrawer() {
  createDrawerMarkup();
  const overlay = document.getElementById('cart-drawer-overlay');
  if (!overlay) return;
  renderCartDrawer();
  overlay.classList.add('is-open');
  document.body.style.overflow = 'hidden';
  isDrawerOpen = true;
}

function closeCartDrawer() {
  const overlay = document.getElementById('cart-drawer-overlay');
  if (!overlay) return;
  overlay.classList.remove('is-open');
  document.body.style.overflow = '';
  isDrawerOpen = false;
}

/* ----------------------------------------------------
   CART ACTIONS
---------------------------------------------------- */
async function addToCart(productId, quantity = 1, openDrawerAfter = true) {
  try {
    const data = await fetchJson('api/cart.php?action=add', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId, quantity: quantity })
    });
    if (data && data.success) {
      cartState = data.cart;
      updateCartBadges(cartState.count);
      renderCartDrawer();
      dispatchCartEvent();
      showToast(data.message || 'Added to cart! 🛒');
      if (openDrawerAfter) {
        setTimeout(openCartDrawer, 250);
      }
    }
  } catch (e) {
    showToast(e.message || 'Error adding to cart', 'error');
  }
}

async function updateCartQuantity(productId, quantity) {
  try {
    const data = await fetchJson('api/cart.php?action=update', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId, quantity: quantity })
    });
    if (data && data.success) {
      cartState = data.cart;
      updateCartBadges(cartState.count);
      renderCartDrawer();
      dispatchCartEvent();
    }
  } catch (e) {
    showToast('Could not update quantity', 'error');
  }
}

async function removeCartItem(productId) {
  try {
    const data = await fetchJson('api/cart.php?action=remove', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId })
    });
    if (data && data.success) {
      cartState = data.cart;
      updateCartBadges(cartState.count);
      renderCartDrawer();
      dispatchCartEvent();
      showToast('Removed from cart');
    }
  } catch (e) {
    showToast('Could not remove item', 'error');
  }
}

/* ----------------------------------------------------
   WISHLIST ACTIONS
---------------------------------------------------- */
async function toggleWishlist(productId, triggerBtn = null) {
  try {
    const data = await fetchJson('api/wishlist.php?action=toggle', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId })
    });
    if (data && data.success) {
      wishlistIds = new Set(data.wishlist.ids.map(Number));
      updateWishlistBadges(data.wishlist.count);
      updateCardWishlistButtons();
      dispatchWishlistEvent(data.wishlist);

      if (triggerBtn) {
        triggerBtn.classList.toggle('is-active', data.added);
      }

      showToast(data.added ? 'Saved to Wishlist ❤️' : 'Removed from Wishlist');
    }
  } catch (e) {
    showToast('Could not update wishlist', 'error');
  }
}

async function moveToCart(productId) {
  try {
    const data = await fetchJson('api/wishlist.php?action=move_to_cart', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId })
    });
    if (data && data.success) {
      wishlistIds = new Set(data.wishlist.ids.map(Number));
      cartState = data.cart;
      updateWishlistBadges(data.wishlist.count);
      updateCartBadges(cartState.count);
      renderCartDrawer();
      dispatchWishlistEvent(data.wishlist);
      dispatchCartEvent();
      showToast('Moved to cart! 🛒');
      setTimeout(openCartDrawer, 250);
    }
  } catch (e) {
    showToast('Could not move item to cart', 'error');
  }
}

/* ----------------------------------------------------
   TOAST SYSTEM
---------------------------------------------------- */
let toastTimeout = null;
function createToastMarkup() {
  if (document.getElementById('ecom-toast')) return;
  const toast = document.createElement('div');
  toast.id = 'ecom-toast';
  toast.className = 'ecom-toast';
  document.body.appendChild(toast);
}

function showToast(message, type = 'success') {
  const toast = document.getElementById('ecom-toast');
  if (!toast) return;

  clearTimeout(toastTimeout);
  toast.textContent = message;
  toast.style.borderColor = type === 'error' ? 'var(--ecom-danger)' : 'var(--ecom-bright)';
  toast.classList.add('is-show');

  toastTimeout = setTimeout(() => {
    toast.classList.remove('is-show');
  }, 3200);
}

/* ----------------------------------------------------
   GLOBAL EVENT DELEGATION
---------------------------------------------------- */
function bindGlobalEvents() {
  // Open cart drawer triggers
  document.addEventListener('click', e => {
    const openBtn = e.target.closest('#open-cart-drawer, .cart-nav-btn, [data-open-cart]');
    if (openBtn) {
      e.preventDefault();
      openCartDrawer();
      return;
    }

    // Add to Cart buttons
    const addBtn = e.target.closest('[data-add-to-cart]');
    if (addBtn) {
      e.preventDefault();
      const pid = Number(addBtn.dataset.addToCart);
      const qtyInput = document.querySelector(`[data-qty-for="${pid}"]`) || document.getElementById('product-qty-input');
      const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;
      addToCart(pid, qty, true);
      return;
    }

    // Buy Now buttons
    const buyBtn = e.target.closest('[data-buy-now]');
    if (buyBtn) {
      e.preventDefault();
      const pid = Number(buyBtn.dataset.buyNow);
      const qtyInput = document.getElementById('product-qty-input');
      const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;
      addToCart(pid, qty, false).then(() => {
        window.location.href = 'checkout.php';
      });
      return;
    }

    // Wishlist toggle buttons
    const wishBtn = e.target.closest('[data-wishlist-toggle], .card-wishlist-btn');
    if (wishBtn) {
      e.preventDefault();
      e.stopPropagation();
      const pid = Number(wishBtn.dataset.wishlistId || wishBtn.dataset.wishlistToggle);
      if (pid) {
        toggleWishlist(pid, wishBtn);
      }
      return;
    }

    // Move from Wishlist to Cart
    const moveBtn = e.target.closest('[data-move-to-cart]');
    if (moveBtn) {
      e.preventDefault();
      const pid = Number(moveBtn.dataset.moveToCart);
      if (pid) {
        moveToCart(pid);
      }
      return;
    }
  });
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

// Export to window for custom usage
window.WaypureEcom = {
  addToCart,
  updateCartQuantity,
  removeCartItem,
  toggleWishlist,
  moveToCart,
  openCartDrawer,
  closeCartDrawer,
  showToast,
  getCart: () => cartState,
  getWishlistIds: () => Array.from(wishlistIds)
};

}) ();
