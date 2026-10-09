import { readFileSync, writeFileSync } from 'fs';

let html = readFileSync('index.html', 'utf8');

// 1. Shorten Lab Section Title and Center the Eyebrow
html = html.replace(
  /<div class="lab-facility-header reveal">\s*<span class="eyebrow"><span class="live-dot"><\/span> WAYPURE EXCLUSIVE<\/span>\s*<h2>Seeing is Believing\.<br><em>Our Testing Lab &amp; Experience Center\.<\/em><\/h2>/g,
  `<div class="lab-facility-header reveal">
          <span class="eyebrow" style="display:flex; justify-content: center;"><span class="live-dot"></span> WAYPURE EXCLUSIVE</span>
          <h2>Our Testing Lab<br><em>& Experience Center.</em></h2>`
);

// 2. The user had an inline style for wp-showcase eyebrow, let's just make sure both headers are centralized in CSS
let waypure = readFileSync('waypure.css', 'utf8');

const alignCss = `
/* --- Centralized Headers --- */
.lab-facility-header,
.wp-showcase-header {
  text-align: center;
  margin-left: auto;
  margin-right: auto;
}
.lab-facility-header .eyebrow,
.wp-showcase-header .eyebrow {
  justify-content: center;
}
`;

if (!waypure.includes('/* --- Centralized Headers --- */')) {
  waypure += '\n' + alignCss;
  writeFileSync('waypure.css', waypure);
}
writeFileSync('index.html', html);

console.log('Fixed lab title and centered eyebrows!');
