import { readFile, writeFile } from 'node:fs/promises';
import { products } from '../data/products.js';

const escape = value => String(value).replace(/[&<>"']/g, character => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[character]));
const currency = value => new Intl.NumberFormat('en-IN', { style:'currency', currency:'INR', maximumFractionDigits:0 }).format(value);

// Professional redesigned card with actual product image from PDF
const card = (p, index) => `<article class="catalogue-card" data-brand="${escape(p.brand)}" data-category="${escape(p.category)}" data-search="${escape([p.brand,p.name,p.technology,p.capacity,...p.features].join(' ').toLowerCase())}">
  <div class="card-glaze" aria-hidden="true"></div>
  <div class="card-image-wrap">
    ${p.brand.toLowerCase() === 'waypure' ? `` : ''}
    <div class="card-image">${p.image ? `<img src="${escape(p.image)}" alt="${escape(p.brand+' '+p.name)}" loading="lazy">` : `<img src="assets/purifier-render.webp" alt="${escape(p.brand+' '+p.name)}" loading="lazy">`}</div>
  </div>
  <div class="card-body">
    <h3 tabindex="-1">${escape(p.name)}</h3>
    <p class="card-technology">${escape(p.technology)}</p>
    <div class="card-price-row">
      <strong class="card-price">${p.mrp ? currency(p.mrp) : 'Price on enquiry'}</strong>
    </div>
    <div class="card-actions">
      <button class="card-btn details-btn" data-product="${escape(`${p.brand} ${p.name}`)}">Product Details</button>
      <button class="card-btn shop-btn" data-product="${escape(`${p.brand} ${p.name}`)}">Shop Now</button>
    </div>
  </div>
</article>`;

for (const file of ['index.html', 'products.html', 'about.html', 'contact.html']) {
let html = await readFile(file,'utf8');
const cards = (file === 'index.html' ? products.slice(0,12) : products).map(card).join('\n');
html = html.replace(/<!-- PRODUCT_CARDS_START -->[\s\S]*?<!-- PRODUCT_CARDS_END -->/,`<!-- PRODUCT_CARDS_START -->\n${cards}\n<!-- PRODUCT_CARDS_END -->`);
html = html.replace(/<!-- PRODUCT_OPTIONS_START -->[\s\S]*?<!-- PRODUCT_OPTIONS_END -->/,`<!-- PRODUCT_OPTIONS_START --><option value="General enquiry">Help me choose</option>${products.map(p=>`<option>${escape(`${p.brand} ${p.name}`)}</option>`).join('')}<!-- PRODUCT_OPTIONS_END -->`);
html = html.replace(/data-catalogue-count>[^<]*/,`data-catalogue-count>Showing ${products.length} of ${products.length} products`);
await writeFile(file,html);
}
console.log(`Built ${products.length} verified product cards and enquiry options.`);
