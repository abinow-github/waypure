const html = require('fs').readFileSync('index.html', 'utf8');
const start = html.indexOf('<section class="difference');
console.log(html.substring(start, start + 300));
