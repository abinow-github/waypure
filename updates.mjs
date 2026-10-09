import { readFileSync, writeFileSync, readdirSync } from 'fs';

// 1. Update HTML files
const htmlFiles = readdirSync('.').filter(f => f.endsWith('.html'));
for (const file of htmlFiles) {
  let html = readFileSync(file, 'utf8');
  
  // Remove preloader logo & text
  html = html.replace(/<img class="preloader-logo"[^>]*>/g, '');
  html = html.replace(/<span class="preloader-wordmark">.*?<\/span>/g, '');
  html = html.replace(/<span class="preloader-caption">.*?<\/span>/g, '');
  
  // Replace footer text with logo
  html = html.replace(/<h2 class="footer-heading">Your next step.<br><em>Think Waypure.<\/em><\/h2>/g, '<div class="footer-heading"><img src="assets/img/logo/logo.png" alt="Waypure" style="width: 250px; filter: brightness(0) invert(1);"></div>');
  
  // Remove hero slider counter
  html = html.replace(/<div class="hero-slider__counter" aria-hidden="true">[\s\S]*?<\/div>/, '');
  
  // Remove services section
  html = html.replace(/<section class="services-section section-space" id="services">[\s\S]*?<\/section>/, '');
  
  writeFileSync(file, html);
}

// 2. Update build-catalogue.mjs
const buildScript = 'scripts/build-catalogue.mjs';
let scriptContent = readFileSync(buildScript, 'utf8');
const newCardGenerator = `const card = (p, index) => \`<article class="catalogue-card" data-brand="\${escape(p.brand)}" data-category="\${escape(p.category)}" data-search="\${escape([p.brand,p.name,p.technology,p.capacity,...p.features].join(' ').toLowerCase())}">
  <div class="card-glaze" aria-hidden="true"></div>
  <div class="card-image-wrap">
    \${p.brand.toLowerCase() === 'waypure' ? \`<img class="card-brand-logo" src="assets/img/logo/logo.png" alt="Waypure" loading="lazy">\` : ''}
    <div class="card-image">\${p.image ? \`<img src="\${escape(p.image)}" alt="\${escape(p.brand+' '+p.name)}" loading="lazy">\` : \`<img src="assets/purifier-render.webp" alt="\${escape(p.brand+' '+p.name)}" loading="lazy">\`}</div>
  </div>
  <div class="card-body">
    <h3 tabindex="-1">\${escape(p.name)}</h3>
    <p class="card-technology">\${escape(p.technology)}</p>
    <div class="card-price-row">
      <strong class="card-price">\${p.mrp ? currency(p.mrp) : 'Price on enquiry'}</strong>
    </div>
    <div class="card-actions">
      <button class="card-btn details-btn">Product Details</button>
      <button class="card-btn shop-btn" data-product="\${escape(\`\${p.brand} \${p.name}\`)}">Shop Now</button>
    </div>
  </div>
</article>\`;`;

scriptContent = scriptContent.replace(/const card = \(p, index\) => `[\s\S]*?<\/article>`;/, newCardGenerator);
writeFileSync(buildScript, scriptContent);

// 3. Update waypure.css for 4 columns and new card styles
let css = readFileSync('waypure.css', 'utf8');
css = css.replace(/\.catalogue-grid\{display:grid;grid-template-columns:repeat\(3,minmax\(0,1fr\)\)/, '.catalogue-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr))');

// Add new card styles
const newStyles = `
/* Redesigned Card Styles */
.card-image-wrap { position: relative; padding: 20px 20px 0; background: linear-gradient(180deg,rgba(240,250,235,0.6) 0%,rgba(255,255,255,0) 100%); overflow: hidden; display: flex; justify-content: center; align-items: center; border-radius: 16px 16px 0 0; }
.card-brand-logo { position: absolute; top: 15px; right: 15px; width: 40px; opacity: 0.5; z-index: 2; mix-blend-mode: multiply; }
.card-image { position: relative; height: 180px; width: 100%; display: flex; justify-content: center; align-items: center; z-index: 3; }
.card-image img { max-height: 100%; max-width: 100%; object-fit: contain; transform: scale(1.1); transition: transform 0.4s var(--ease); }
.catalogue-card:hover .card-image img { transform: scale(1.18) translateY(-5px); }
.card-body { padding: 20px; display: flex; flex-direction: column; gap: 8px; }
.card-body h3 { font-size: 18px; font-weight: 700; color: #173e38; margin: 0; line-height: 1.2; }
.card-technology { font-size: 11px; color: #718078; font-weight: 500; line-height: 1.4; margin-bottom: 10px; min-height: 30px;}
.card-price-row { margin-bottom: 15px; }
.card-price { font-size: 18px; color: #173e38; font-weight: 800; }
.card-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.card-btn { padding: 10px; font-size: 10px; font-weight: 600; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s ease; text-align: center; }
.details-btn { background: #f0f5eb; color: #173e38; border: 1px solid #d9ded2; }
.details-btn:hover { background: #e5eacb; }
.shop-btn { background: #173e38; color: #fff; border: 1px solid #173e38; }
.shop-btn:hover { background: #2e5b4e; border-color: #2e5b4e; box-shadow: 0 4px 10px rgba(23,62,56,0.2); }
@media(max-width:1100px) { .catalogue-grid{grid-template-columns:repeat(2,minmax(0,1fr)) !important;} }
@media(max-width:600px) { .catalogue-grid{grid-template-columns:1fr !important;} }
`;
css += newStyles;
writeFileSync('waypure.css', css);
