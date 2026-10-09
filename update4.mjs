import { readFileSync, writeFileSync } from 'fs';

// 1. Fix styles.css (header background)
let styles = readFileSync('styles.css', 'utf8');
if (!styles.includes('background:#ffffff;position:fixed')) {
  styles = styles.replace('.header{position:fixed', '.header{background:#ffffff;position:fixed');
  writeFileSync('styles.css', styles);
}

// 2. Fix waypure.css (Product Image mix-blend-mode)
let waypure = readFileSync('waypure.css', 'utf8');
if (!waypure.includes('mix-blend-mode: multiply;')) {
  waypure = waypure.replace('.card-image img { ', '.card-image img { mix-blend-mode: multiply; ');
}
writeFileSync('waypure.css', waypure);

// 3. Fix build-catalogue.mjs (Remove card-brand-logo)
let builder = readFileSync('scripts/build-catalogue.mjs', 'utf8');
// Target the exact line injecting the logo.
builder = builder.replace(/<img class="card-brand-logo".*?>/, '');
writeFileSync('scripts/build-catalogue.mjs', builder);

// 4. Update index.html (Remove existing logos and update scroll JS for floating contact)
let indexHtml = readFileSync('index.html', 'utf8');

// Remove hardcoded card-brand-logo
indexHtml = indexHtml.replace(/<img class="card-brand-logo"[^>]*>/g, '');

// Update scroll script
const oldScript = `<script>
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
  </script>`;

const newScript = `<script>
    (function() {
      var header = document.querySelector('.header');
      var floatingContact = document.querySelector('.floating-contact');
      if (floatingContact) {
        floatingContact.style.opacity = '0';
        floatingContact.style.transform = 'translateY(20px) scale(0.9)';
        floatingContact.style.pointerEvents = 'none';
        floatingContact.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      }
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
        
        if (floatingContact) {
          if (currentScroll > 600) {
            floatingContact.style.opacity = '1';
            floatingContact.style.transform = 'translateY(0) scale(1)';
            floatingContact.style.pointerEvents = 'auto';
          } else {
            floatingContact.style.opacity = '0';
            floatingContact.style.transform = 'translateY(20px) scale(0.9)';
            floatingContact.style.pointerEvents = 'none';
          }
        }
        lastScroll = currentScroll;
      }, { passive: true });
    })();
  </script>`;

if (indexHtml.includes(oldScript)) {
  indexHtml = indexHtml.replace(oldScript, newScript);
} else {
  // If exact match fails, try replacing ignoring whitespace
  const oldScriptNormalized = oldScript.replace(/\\s+/g, '');
  const currentIndexNormalized = indexHtml.replace(/\\s+/g, '');
  if (currentIndexNormalized.includes(oldScriptNormalized)) {
     // fallback regex or manual replace
     indexHtml = indexHtml.replace(/<script>\s*\(function\(\)\s*\{\s*var header = document\.querySelector\('\.header'\);[\s\S]*?\}\)\(\);\s*<\/script>/, newScript);
  }
}

writeFileSync('index.html', indexHtml);
