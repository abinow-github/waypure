import { readFileSync, writeFileSync } from 'fs';

let waypure = readFileSync('waypure.css', 'utf8');

const globalBlock = `
section h2, 
.hero-slide__title,
.awareness-intro h2, 
.awareness-solution__copy h2, 
dialog h2 {
  font-size: var(--heading-xl) !important;
  font-family: var(--body-font) !important;
}
`;

if (waypure.includes('.difference h2,')) {
  waypure = waypure.replace(/\.difference h2,[\s\S]*?\{[\s\S]*?\}/, globalBlock);
} else {
  waypure = globalBlock + '\n' + waypure;
}

waypure = waypure.replace(/\.wt-header h2\s*\{\s*font-size:[^;\}]+;\s*/g, '.wt-header h2 { ');
waypure = waypure.replace(/\.reviews-header h2\s*\{\s*font-size:[^;\}]+;\s*/g, '.reviews-header h2 { ');
waypure = waypure.replace(/\.wp-showcase-header h2\s*\{\s*font-size:[^;\}]+;\s*/g, '.wp-showcase-header h2 { ');
writeFileSync('waypure.css', waypure);

let styles = readFileSync('styles.css', 'utf8');
styles = styles.replace(/\.chapter h2\{font-size:[^}]+\}/g, '');
styles = styles.replace(/\.difference-heading h2\{font-size:[^}]+\}/g, '');
styles = styles.replace(/\.ritual h2\{font-size:[^}]+\}/g, '');
styles = styles.replace(/\.product-copy h2\{font-size:[^}]+\}/g, '');
styles = styles.replace(/\.faq-intro h2\{font-size:[^}]+\}/g, '');
styles = styles.replace(/\.closing h2\{font-size:[^}]+\}/g, '');
styles = styles.replace(/dialog h2\{font-size:[^}]+\}/g, '');
writeFileSync('styles.css', styles);

let hero = readFileSync('hero-awareness.css', 'utf8');
hero = hero.replace(/font-size:\s*clamp\([^)]+\);\s*\n/g, '');
writeFileSync('hero-awareness.css', hero);

console.log('Uniform heading sizes applied safely!');
