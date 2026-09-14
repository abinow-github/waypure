import { readFile, writeFile } from 'node:fs/promises';
import { products } from '../data/products.js';

const escape = value => String(value).replace(/[&<>"']/g, character => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[character]));
const currency = value => new Intl.NumberFormat('en-IN', { style:'currency', currency:'INR', maximumFractionDigits:0 }).format(value);
const card = (p, index) => `<article class="catalogue-card" data-brand="${escape(p.brand)}" data-category="${escape(p.category)}" data-search="${escape([p.brand,p.name,p.technology,p.capacity,...p.features].join(' ').toLowerCase())}">
  <div class="card-top"><span class="catalogue-brand">${escape(p.brand)}</span><span class="card-index">${String(index+1).padStart(2,'0')}</span></div>
  <span class="card-category">${escape(p.category)}</span><h3 tabindex="-1">${escape(p.name)}</h3><p class="card-technology">${escape(p.technology)}</p>
  <div class="card-capacity">${p.capacity ? `<span>${p.capacity === 'Inline' ? 'Configuration' : 'Storage'}</span><strong>${escape(p.capacity)}</strong>` : `<span>Specifications</span><strong>Ask our team</strong>`}</div>
  <details class="card-details"><summary>Product details <span>+</span></summary><div class="card-detail-body">${p.features.length ? `<ul>${p.features.map(feature=>`<li>${escape(feature)}</li>`).join('')}</ul>` : '<p>Contact Waypure for the full specifications of this model.</p>'}${p.dimensions ? `<p><strong>W × D × H:</strong> ${p.dimensions}<br><small>Dimensions as printed; confirm units with our team.</small></p>` : ''}${p.warranty ? `<p><strong>Listed warranty:</strong> ${p.warranty}</p>` : ''}${p.tankWarranty ? `<p><strong>Tank:</strong> ${escape(p.tankWarranty)}</p>` : ''}<p class="card-source">October 2023 catalogue · p. ${p.pages.join(', ')}</p></div></details>
  <div class="card-bottom"><div><span class="card-price-label">${p.mrp ? '2023 catalogue MRP' : 'Current price'}</span><strong class="card-price">${p.mrp ? currency(p.mrp) : 'On enquiry'}</strong></div><button class="card-enquiry" data-product="${escape(`${p.brand} ${p.name}`)}" aria-label="Enquire about ${escape(`${p.brand} ${p.name}`)}"><span>Enquire</span><span aria-hidden="true">↗</span></button></div>
</article>`;
for (const file of ['index.html', 'products.html', 'about.html', 'contact.html']) {
let html = await readFile(file,'utf8');
const cards = (file === 'index.html' ? products.slice(0,6) : products).map(card).join('\n');
html = html.replace(/<!-- PRODUCT_CARDS_START -->[\s\S]*?<!-- PRODUCT_CARDS_END -->/,`<!-- PRODUCT_CARDS_START -->\n${cards}\n<!-- PRODUCT_CARDS_END -->`);
html = html.replace(/<!-- PRODUCT_OPTIONS_START -->[\s\S]*?<!-- PRODUCT_OPTIONS_END -->/,`<!-- PRODUCT_OPTIONS_START --><option value="General enquiry">Help me choose</option>${products.map(p=>`<option>${escape(`${p.brand} ${p.name}`)}</option>`).join('')}<!-- PRODUCT_OPTIONS_END -->`);
html = html.replace(/data-catalogue-count>[^<]*/,`data-catalogue-count>Showing ${products.length} of ${products.length} products`);
await writeFile(file,html);
}
console.log(`Built ${products.length} verified product cards and enquiry options.`);
