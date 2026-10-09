import { readFileSync, writeFileSync } from 'fs';

// ----------------------------------------------------
// 1. UPDATE WAYPURE SHOWCASE BUTTONS IN INDEX.HTML
// ----------------------------------------------------
let html = readFileSync('index.html', 'utf8');

// Replace the single Enquire button with the dual buttons in Waypure showcase
html = html.replace(/<button class="wp-enquire enquire-button" data-product="([^"]+)">Enquire<\/button>/g, `
<div class="card-actions">
  <button class="card-btn details-btn">Product Details</button>
  <button class="card-btn shop-btn" data-product="$1">Shop Now</button>
</div>
`);

writeFileSync('index.html', html);
console.log('Fixed Waypure Showcase HTML buttons');

// ----------------------------------------------------
// 2. UPDATE CSS (waypure.css and styles.css)
// ----------------------------------------------------
let waypure = readFileSync('waypure.css', 'utf8');
let styles = readFileSync('styles.css', 'utf8');

// A) Fix catalogue-grid to show 4 cards instead of 3
waypure = waypure.replace(/grid-template-columns:\s*repeat\(3,\s*minmax\(0,1fr\)\)/, 'grid-template-columns: repeat(4, minmax(0,1fr))');

// B) Add CSS for the buttons (.card-actions and .card-btn) which got lost
const buttonCss = `
/* --- Fix Buttons CSS --- */
.card-actions {
  display: flex;
  gap: 10px;
  margin-top: auto;
  padding-top: 15px;
  border-top: 1px solid #ebf1e8;
}

.card-btn {
  flex: 1;
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.details-btn {
  background: #f0f5f2;
  color: var(--ink);
  border: 1px solid #e0eae5;
}

.details-btn:hover {
  background: #e0eae5;
}

.shop-btn {
  background: var(--ink);
  color: #fff;
  border: 1px solid var(--ink);
}

.shop-btn:hover {
  background: #2e5b4e;
  border-color: #2e5b4e;
}

.wp-product-card .card-actions {
  border-top: none;
  padding-top: 0;
  width: 100%;
}
`;

// Inject button CSS into waypure.css
if (!waypure.includes('.card-actions {')) {
  waypure += '\n' + buttonCss;
}

// C) Fix Image "Cut off" and background issues
// The images have backgrounds/baked text. We will use object-fit contain so they don't get cut off.
// And mix-blend-mode to remove white background.
const imageFixCss = `
/* --- Fix Product Image Cutting --- */
.card-image-wrap {
  width: 100%;
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  margin-bottom: 20px;
}

.card-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-image img, .wp-product-img img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain; /* Prevents cutting off */
  mix-blend-mode: multiply; /* Removes white background */
  transition: transform 0.4s var(--ease);
}
`;

if (!waypure.includes('/* --- Fix Product Image Cutting --- */')) {
  waypure += '\n' + imageFixCss;
} else {
  // Ensure we overwrite old broken ones if they exist
  waypure = waypure.replace(/\.card-image img\s*\{[\s\S]*?\}/, '.card-image img, .wp-product-img img { max-width: 100%; max-height: 100%; object-fit: contain; mix-blend-mode: multiply; transition: transform 0.4s var(--ease); }');
}

writeFileSync('waypure.css', waypure);
console.log('Fixed CSS Grid (4 columns), Buttons, and Images.');

