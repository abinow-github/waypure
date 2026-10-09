const fs = require('fs');
let css = fs.readFileSync('wq-story.css', 'utf8');
const idx = css.indexOf('/* Lab Background */');
if (idx !== -1) {
  fs.writeFileSync('wq-story.css', css.substring(0, idx));
}
