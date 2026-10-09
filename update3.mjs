import { readFileSync, writeFileSync } from 'fs';

let html = readFileSync('index.html', 'utf8');

// Move wq-scroll-bar inside wq-scene
const scrollBarHtml = `<div class="wq-scroll-bar" aria-hidden="true">
          <div class="wq-scroll-bar-fill" id="wq-scroll-bar-fill"></div>
        </div>`;

if (html.includes(scrollBarHtml)) {
  html = html.replace(scrollBarHtml, '');
  html = html.replace('</div> <!-- .wq-scene end (assuming it ends before phase-nav or somewhere) -->', ''); // Actually we just append it before wq-phase-nav which is also inside wq-sticky
  
  // Find <div class="wq-scene"> and its closing tag, or just replace wq-phase-nav which is right after wq-scene
  const phaseNav = `<div class="wq-phase-nav" aria-hidden="true">`;
  html = html.replace(phaseNav, scrollBarHtml + '\n        ' + phaseNav);
  
  writeFileSync('index.html', html);
}
