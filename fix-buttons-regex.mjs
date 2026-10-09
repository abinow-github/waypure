import { readFileSync, writeFileSync } from 'fs';

let html = readFileSync('index.html', 'utf8');

// Use Regex to match the section block flexibly
const pattern = /<h2>One decision\.<br><span class="wq-safe">Pure water\. Forever\.<\/span><\/h2>[\s\S]*?(<div style="display:flex;gap:0\.8rem;flex-wrap:wrap;margin-top:0\.8rem;">[\s\S]*?<\/div>)/;

const replacement = `<h2>One decision.<br><span class="wq-safe">Pure water. Forever.</span></h2>
              <p>A Waypure RO+UV+Mineral purifier eliminates 99.9% of bacteria, viruses and heavy metals — restoring water to its purest form, with beneficial minerals added back.</p>
              <div style="display:flex;gap:15px;flex-wrap:wrap;margin-top:20px;">
                <a href="products.html" class="pill-button">Explore Products <span>↗</span></a>
                <a href="tel:+919061091810" class="pill-button dark">Call Us <span>↗</span></a>
              </div>`;

if (pattern.test(html)) {
  html = html.replace(pattern, replacement);
  writeFileSync('index.html', html);
  console.log("Successfully replaced button section via Regex.");
} else {
  console.log("Pattern not found. Checking exactly what's there.");
}
