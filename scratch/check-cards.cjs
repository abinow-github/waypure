const fs = require('fs');

['index.html', 'products.html'].forEach(file => {
  const html = fs.readFileSync(file, 'utf8');
  const cards = html.match(/<article[^>]*class=["'][^"']*catalogue-card[^"']*["']/gi) || [];
  const detailsBtns = html.match(/class=["'][^"']*details-btn[^"']*["']/gi) || [];
  const shopBtns = html.match(/class=["'][^"']*shop-btn[^"']*["']/gi) || [];
  console.log(file, '-> Cards:', cards.length, 'Details btns:', detailsBtns.length, 'Shop btns:', shopBtns.length);
});
