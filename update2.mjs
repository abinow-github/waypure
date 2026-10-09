import { readFileSync, writeFileSync } from 'fs';

// 1. Update index.html for buttons and header JS
let html = readFileSync('index.html', 'utf8');

// Replace "One decision" panel with buttons
const oldPanel = `<h2>One decision.<br><span class="wq-safe">Pure water. Forever.</span></h2>`;
const newPanel = `<h2>One decision.<br><span class="wq-safe">Pure water. Forever.</span></h2>
<div style="display: flex; gap: 15px; margin-top: 35px;">
  <a href="products.html" class="pill-button">Explore Products <span>↗</span></a>
  <a href="tel:+919061091810" class="pill-button dark">Call Us <span>↗</span></a>
</div>`;
html = html.replace(oldPanel, newPanel);

// Add Header scroll JS
if (!html.includes('header.classList.toggle')) {
  const headerJs = `
  <script>
    (function() {
      var header = document.querySelector('.header');
      var lastScroll = 0;
      window.addEventListener('scroll', function() {
        var currentScroll = window.pageYOffset;
        if (currentScroll > 150) {
          if (currentScroll > lastScroll) {
            header.style.transform = 'translateY(-100%)';
          } else {
            header.style.transform = 'translateY(0)';
          }
        } else {
          header.style.transform = 'translateY(0)';
        }
        lastScroll = currentScroll;
      }, { passive: true });
    })();
  </script>
</body>`;
  html = html.replace('</body>', headerJs);
}

// Move wq-scroll-bar into wq-scene if not already
if (html.includes('<div class="wq-scroll-bar">') && html.includes('<div class="wq-scene">')) {
  // It's probably already in wq-sticky. We will just CSS position it right inside wq-scene.
}
writeFileSync('index.html', html);

// 2. Update waypure.css with Lab watermark
let waypure = readFileSync('waypure.css', 'utf8');
if (!waypure.includes('.lab-facility::before')) {
  waypure += `\n.lab-facility { position: relative; overflow: hidden; }\n.lab-facility::before { content: '⚗🔬'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 350px; opacity: 0.04; pointer-events: none; z-index: 0; font-family: sans-serif; white-space: nowrap; }\n.lab-facility-inner { position: relative; z-index: 1; }\n`;
  writeFileSync('waypure.css', waypure);
}

// 3. Update wq-story.css for wq-scroll-bar
let wqCss = readFileSync('wq-story.css', 'utf8');
wqCss = wqCss.replace('.wq-scroll-bar {', '.wq-scroll-bar { position: absolute; right: 5%; top: 20%; height: 60%; width: 2px; background: rgba(0,0,0,0.05); z-index: 10;');
writeFileSync('wq-story.css', wqCss);
