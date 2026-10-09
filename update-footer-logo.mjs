import { readFileSync, writeFileSync, readdirSync } from 'fs';

const htmlFiles = readdirSync('.').filter(f => f.endsWith('.html'));
for (const file of htmlFiles) {
  let html = readFileSync(file, 'utf8');
  
  // Replace the white logo in footer-heading with logo-v.png
  html = html.replace(
    '<div class="footer-heading"><img src="assets/img/logo/logo.png" alt="Waypure" style="width: 250px; filter: brightness(0) invert(1);"></div>',
    '<div class="footer-heading"><img src="assets/img/logo/logo-v.png" alt="Waypure" style="width: 150px;"></div>'
  );
  
  // Remove the brand-logo img from footer-bottom to ensure there's only 1 logo in the footer
  html = html.replace('<img class="brand-logo" src="assets/img/logo/logo.png" alt="" width="1746" height="1724" loading="lazy">', '');
  
  writeFileSync(file, html);
}
