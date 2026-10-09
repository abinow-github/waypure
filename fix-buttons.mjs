import { readFileSync, writeFileSync } from 'fs';

let html = readFileSync('index.html', 'utf8');

// Find the section that has the duplicate buttons
const oldSection = `<h2>One decision.<br><span class="wq-safe">Pure water. Forever.</span></h2>
<div style="display: flex; gap: 15px; margin-top: 35px;">
  <a href="products.html" class="pill-button">Explore Products <span>↗</span></a>
  <a href="tel:+919061091810" class="pill-button dark">Call Us <span>↗</span></a>
</div>
              <p>A Waypure RO+UV+Mineral purifier eliminates 99.9% of bacteria, viruses and heavy metals — restoring water to its purest form, with beneficial minerals added back.</p>
              <div style="display:flex;gap:0.8rem;flex-wrap:wrap;margin-top:0.8rem;">
                <button class="hs-btn hs-btn--primary enquire-button" style="border:none;">Get pure water ↗</button>
                <a href="products.html" class="hs-btn hs-btn--outline">Browse models ↗</a>
              </div>`;

const newSection = `<h2>One decision.<br><span class="wq-safe">Pure water. Forever.</span></h2>
              <p>A Waypure RO+UV+Mineral purifier eliminates 99.9% of bacteria, viruses and heavy metals — restoring water to its purest form, with beneficial minerals added back.</p>
              <div style="display:flex;gap:15px;flex-wrap:wrap;margin-top:20px;">
                <a href="products.html" class="pill-button">Explore Products <span>↗</span></a>
                <a href="tel:+919061091810" class="pill-button dark">Call Us <span>↗</span></a>
              </div>`;

if (html.includes(oldSection)) {
  html = html.replace(oldSection, newSection);
} else {
  // Try fixing line endings if needed
  html = html.replace(oldSection.replace(/\r\n/g, '\n'), newSection);
}

writeFileSync('index.html', html);
