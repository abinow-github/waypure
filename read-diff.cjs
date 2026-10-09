const html = require('fs').readFileSync('index.html', 'utf8');
const lines = html.split('\n');
const start = lines.findIndex(l => l.includes('<section class="difference'));
const end = lines.findIndex((l, i) => i > start && l.includes('</section>'));
console.log(lines.slice(start, end + 1).join('\n'));
