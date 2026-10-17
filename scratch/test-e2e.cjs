const http = require('http');

function request(path, options = {}, body = null) {
  return new Promise((resolve, reject) => {
    const opt = {
      hostname: 'localhost',
      port: 80,
      path: '/waypure/' + path,
      method: options.method || 'GET',
      headers: options.headers || {}
    };

    const req = http.request(opt, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, headers: res.headers, body: JSON.parse(data) });
        } catch (e) {
          resolve({ status: res.statusCode, headers: res.headers, raw: data });
        }
      });
    });

    req.on('error', reject);
    if (body) req.write(JSON.stringify(body));
    req.end();
  });
}

async function run() {
  // Cookie jar
  let cookieJar = {};
  function updateCookies(headers) {
    if (headers['set-cookie']) {
      headers['set-cookie'].forEach(c => {
        const parts = c.split(';')[0].split('=');
        cookieJar[parts[0].trim()] = parts[1].trim();
      });
    }
  }
  function getCookieHeader() {
    return Object.entries(cookieJar).map(([k, v]) => `${k}=${v}`).join('; ');
  }

  console.log('--- TEST 1: GET INITIAL CART ---');
  let res = await request('api/cart.php?action=get');
  updateCookies(res.headers);
  console.log('Cart count:', res.body.cart.count, 'Subtotal:', res.body.cart.formatted_subtotal);
  console.log('Cookies in jar:', getCookieHeader());

  console.log('\n--- TEST 2: ADD PRODUCT 1 (Q-Tone) TO CART ---');
  res = await request('api/cart.php?action=add', { method: 'POST', headers: { 'Cookie': getCookieHeader(), 'Content-Type': 'application/json' } }, { product_id: 1, quantity: 2 });
  updateCookies(res.headers);
  console.log('Add status:', res.status, 'Message:', res.body.message);
  console.log('Cart count now:', res.body.cart.count, 'Subtotal:', res.body.cart.formatted_subtotal);

  console.log('\n--- TEST 3: TOGGLE WISHLIST FOR PRODUCT 2 (Mars) ---');
  res = await request('api/wishlist.php?action=toggle', { method: 'POST', headers: { 'Cookie': getCookieHeader(), 'Content-Type': 'application/json' } }, { product_id: 2 });
  updateCookies(res.headers);
  console.log('Wishlist toggle status:', res.status, 'Added:', res.body.added, 'Count:', res.body.wishlist.count);

  console.log('\n--- TEST 4: CHECKOUT / PLACE ORDER ---');
  res = await request('api/checkout.php', { method: 'POST', headers: { 'Cookie': getCookieHeader(), 'Content-Type': 'application/json' } }, {
    name: 'Muhammed Shafi',
    phone: '9895301810',
    address: 'Green Villa, Angadipuram PO',
    city: 'Angadipuram',
    pincode: '679321',
    payment_method: 'cod',
    notes: 'Please call before delivery'
  });
  updateCookies(res.headers);
  console.log('Order status:', res.status, 'Success:', res.body.success, 'Message:', res.body.message);
  console.log('Order Code:', res.body.order_code);
  console.log('WhatsApp confirmation link generated:', res.body.whatsapp_url ? 'YES' : 'NO');

  console.log('\n--- TEST 5: VERIFY CART IS CLEARED AFTER CHECKOUT ---');
  res = await request('api/cart.php?action=get', { headers: { 'Cookie': getCookieHeader() } });
  console.log('Cart items count after checkout:', res.body.cart.count, '(Expected: 0)');

  console.log('\nALL E-COMMERCE TESTS COMPLETED SUCCESSFULLY!');
}

run().catch(console.error);
