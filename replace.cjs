const fs = require('fs');
let text = fs.readFileSync('c:\\xampp\\htdocs\\waypure\\index.html', 'utf8');
text = text.replace(/var bgColors = \[[\s\S]*?\];/, `var bgColors = [
      { mid: '#ffffff', outer: '#f4faff' },  // 0
      { mid: '#ffffff', outer: '#f4faff' },  // 1
      { mid: '#ffffff', outer: '#f4faff' },  // 2
      { mid: '#ffffff', outer: '#f4faff' },  // 3
      { mid: '#ffffff', outer: '#f4faff' },  // 4
    ];`);
fs.writeFileSync('c:\\xampp\\htdocs\\waypure\\index.html', text);
