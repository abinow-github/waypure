const fs = require('fs');
const html = fs.readFileSync('index.html', 'utf8');
const regex = /<section[^>]*id=["']([^"']+)["']/gi;
let match;
while ((match = regex.exec(html)) !== null) {
  console.log('Section id:', match[1]);
}
