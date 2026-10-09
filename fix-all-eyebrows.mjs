import { readFileSync, writeFileSync } from 'fs';

let css = readFileSync('waypure.css', 'utf8');

const oldBlockRegex = /\/\* --- Centralized Headers --- \*\/[\s\S]*?\.wp-showcase-header \.eyebrow\s*\{\s*justify-content:\s*center;\s*\}/;

const newBlock = `/* --- Centralized Headers --- */
.lab-facility-header,
.wp-showcase-header,
.wt-header,
.reviews-header {
  text-align: center;
  margin-left: auto;
  margin-right: auto;
}
.lab-facility-header .eyebrow,
.wp-showcase-header .eyebrow,
.wt-header .eyebrow,
.reviews-header .eyebrow {
  justify-content: center;
}`;

if (oldBlockRegex.test(css)) {
  css = css.replace(oldBlockRegex, newBlock);
} else {
  css += '\n' + newBlock;
}

writeFileSync('waypure.css', css);
console.log('Fixed centered eyebrows globally!');
