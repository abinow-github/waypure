const fs = require('fs');
const html = fs.readFileSync('products.html', 'utf8');
const cards = html.match(/<article class="catalogue-card"[\s\S]*?<\/article>/g);
if (cards && cards.length) {
  console.log('Sample card from products.html:');
  console.log(cards[0]);
}
