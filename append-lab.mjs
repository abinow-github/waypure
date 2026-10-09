import fs from 'fs';
let css = fs.readFileSync('wq-story.css', 'utf8');
css += `
/* Lab Background */
.wq-sticky::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(18, 57, 86, 0.08) 1.5px, transparent 1.5px);
  background-size: 28px 28px;
  pointer-events: none;
  z-index: 0;
}
.wq-sticky::after {
  content: '⚗🔬';
  position: absolute;
  top: 10%;
  left: 5%;
  font-size: 150px;
  opacity: 0.03;
  pointer-events: none;
  z-index: 0;
  font-family: sans-serif;
}
`;
fs.writeFileSync('wq-story.css', css);
