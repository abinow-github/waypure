const fs = require('fs');

let html = fs.readFileSync('index.html', 'utf8');
const target = `<p class="life-lead">From the first sip of the morning to the moments that keep you moving. Water is woven into your everyday.</p><p>Something so essential deserves thoughtful care. At Waypure, our commitment to quality brings together purification choices, attentive service and continuous improvement.</p><a class="pill-button dark" href="about.html">Meet the Waypure promise <span>↗</span></a>`;

const replacement = `<p class="life-lead">Water is part of every day and every stage of life. At WayPure, we believe everyone deserves access to clean, safe water at a cost they can afford.</p><p>Inspired by our founder, Shabeel, to help people find dependable solutions for the water they use and the families they care for — guided by Eat Pure, Drink Pure, and Breathe Pure.</p><a class="pill-button dark" href="about.html">Read our About Us story <span>↗</span></a>`;

if (html.includes(target)) {
  html = html.replace(target, replacement);
  fs.writeFileSync('index.html', html, 'utf8');
  console.log('Successfully updated index.html with new About Us story preview!');
} else {
  console.log('Target string not found in index.html');
}
