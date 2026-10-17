const fs = require('fs');

const productsJson = JSON.parse(fs.readFileSync('data/products.json', 'utf8'));

// Build lookup maps
const productLookup = new Map();
productsJson.forEach((p, idx) => {
  const dbId = idx + 1;
  const slug = p.slug || p.id;
  const key = `${p.brand.toLowerCase()}_${p.name.toLowerCase()}`;
  productLookup.set(key, { dbId, slug, ...p });
});

function findProduct(brand, name) {
  const key = `${(brand || '').toLowerCase().trim()}_${(name || '').toLowerCase().trim()}`;
  if (productLookup.has(key)) return productLookup.get(key);

  // Partial match by name
  for (const [k, p] of productLookup.entries()) {
    if (k.endsWith(`_${name.toLowerCase().trim()}`)) return p;
  }
  return null;
}

const headerActionsHtml = `    <div class="header-actions">
      <a href="wishlist.php" class="ecom-nav-btn wishlist-nav-btn" aria-label="Wishlist" title="Wishlist">
        <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        <span class="ecom-badge wishlist-badge" style="display:none;">0</span>
      </a>
      <button type="button" class="ecom-nav-btn cart-nav-btn" id="open-cart-drawer" aria-label="Shopping Cart" title="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <span class="ecom-badge cart-badge" style="display:none;">0</span>
      </button>
    </div>\n`;

['index.html', 'products.html', 'about.html', 'contact.html'].forEach(filename => {
  if (!fs.existsSync(filename)) return;
  let html = fs.readFileSync(filename, 'utf8');

  // 1. Add ecommerce.css and ecommerce.js in <head> if missing
  if (!html.includes('ecommerce.css')) {
    html = html.replace('</head>', '  <link rel="stylesheet" href="ecommerce.css">\n</head>');
  }
  if (!html.includes('ecommerce.js')) {
    html = html.replace('</head>', '  <script defer src="ecommerce.js"></script>\n</head>');
  }

  // 2. Add header-actions into <header class="header"> before nav-cta if missing
  if (!html.includes('header-actions')) {
    html = html.replace(/(<a [^>]*class="nav-cta"[^>]*>)/, headerActionsHtml + '    $1');
  }

  // 3. Update mobile-nav if missing ecom links
  if (!html.includes('cart.php') && html.includes('id="mobile-nav"')) {
    html = html.replace('</nav>', '<a href="cart.php">Shopping Cart</a><a href="wishlist.php">Saved Wishlist</a></nav>');
  }

  // 4. Update catalogue cards if in file
  if (html.includes('catalogue-card')) {
    html = html.replace(/<article class="catalogue-card"([\s\S]*?)<\/article>/g, (cardMatch) => {
      // Extract brand and name
      const brandMatch = cardMatch.match(/data-brand="([^"]+)"/);
      const nameMatch = cardMatch.match(/<h3[^>]*>([^<]+)<\/h3>/);
      const brand = brandMatch ? brandMatch[1] : 'Waypure';
      const name = nameMatch ? nameMatch[1].trim() : '';

      const prod = findProduct(brand, name);
      const dbId = prod ? prod.dbId : 1;
      const slug = prod ? prod.slug : (name.toLowerCase().replace(/[^a-z0-9]+/g, '-'));

      let updatedCard = cardMatch;

      // Add wishlist heart if not already present
      if (!updatedCard.includes('card-wishlist-btn')) {
        const heartHtml = `<button type="button" class="card-wishlist-btn" data-wishlist-id="${dbId}" data-wishlist-toggle="${dbId}" aria-label="Save to Wishlist" title="Save to Wishlist"><svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></button>\n  `;
        updatedCard = updatedCard.replace(/(<article class="catalogue-card"[^>]*>\s*)/, `$1${heartHtml}`);
      }

      // Convert Product Details button to <a> link pointing to product.php?id={slug}
      updatedCard = updatedCard.replace(
        /<button class="card-btn details-btn"[^>]*>Product Details<\/button>/g,
        `<a href="product.php?id=${encodeURIComponent(slug)}" class="card-btn details-btn" style="text-align:center; text-decoration:none;">Product Details</a>`
      );

      return updatedCard;
    });
  }

  fs.writeFileSync(filename, html, 'utf8');
  console.log(`Updated ${filename} successfully.`);
});
