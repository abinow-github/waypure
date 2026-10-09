import { readFileSync, writeFileSync } from 'fs';

let css = readFileSync('waypure.css', 'utf8');

const labBentoCss = `
/* =====================================================
   LAB FACILITY BENTO GRID
   ===================================================== */
.lab-facility {
  background: var(--cream);
}
.lab-facility-inner {
  max-width: 1200px;
  margin: 0 auto;
}
.lab-facility-header {
  max-width: 650px;
  margin-bottom: 40px;
}
.lab-facility-header p {
  font-size: 14px;
  line-height: 1.7;
  color: #55718b;
  margin-top: 15px;
}
.lab-bento {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-template-rows: 400px 300px;
  gap: 20px;
}
.bento-card {
  position: relative;
  border-radius: 24px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #fff;
  border: 1px solid #e0eae5;
  box-shadow: 0 10px 30px rgba(23,62,56,0.05);
}
.bento-card img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 1;
  transition: transform 0.6s var(--ease);
}
.bento-card:hover img {
  transform: scale(1.05);
}
.bento-glass-panel {
  position: absolute;
  bottom: 20px;
  left: 20px;
  right: 20px;
  z-index: 2;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  padding: 24px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.5);
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}
.bento-glass--dark {
  background: rgba(23, 62, 56, 0.85);
  border-color: rgba(255,255,255,0.1);
  color: #fff;
}
.bento-glass-panel h3 {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--ink);
}
.bento-glass--dark h3 {
  color: #fff;
}
.bento-glass-panel p {
  font-size: 13px;
  line-height: 1.6;
  color: #55718b;
  margin-bottom: 15px;
}
.bento-glass--dark p {
  color: #a4b3b0;
}
.bento-glass-panel .text-link {
  font-size: 12px;
  font-weight: 700;
  color: #173e38;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 5px;
}
.bento-glass-panel .text-link:hover {
  text-decoration: underline;
}

/* Grid Placement */
.bento-large {
  grid-column: 1 / 3;
  grid-row: 1 / 2;
}
.bento-tall {
  grid-column: 3 / 4;
  grid-row: 1 / 3;
}
.bento-wide {
  grid-column: 1 / 2;
  grid-row: 2 / 3;
}
.bento-cta {
  grid-column: 2 / 3;
  grid-row: 2 / 3;
  padding: 40px;
  justify-content: center;
  align-items: flex-start;
  background: #f0f5f2;
}
.bento-cta h3 {
  font-size: 28px;
  line-height: 1.2;
  font-weight: 800;
  color: var(--ink);
  margin-bottom: 12px;
}
.bento-cta p {
  font-size: 14px;
  line-height: 1.6;
  color: #55718b;
  margin-bottom: 24px;
}

/* Responsive */
@media(max-width: 900px) {
  .lab-bento {
    grid-template-columns: 1fr 1fr;
    grid-template-rows: auto;
  }
  .bento-large { grid-column: 1 / 3; grid-row: auto; height: 350px; }
  .bento-tall { grid-column: 1 / 2; grid-row: auto; height: 350px; }
  .bento-wide { grid-column: 2 / 3; grid-row: auto; height: 350px; }
  .bento-cta { grid-column: 1 / 3; grid-row: auto; }
}
@media(max-width: 600px) {
  .lab-bento {
    grid-template-columns: 1fr;
  }
  .bento-large, .bento-tall, .bento-wide, .bento-cta { 
    grid-column: 1 / 2; 
    height: 300px;
  }
  .bento-cta {
    height: auto;
  }
}
`;

if (!css.includes('.lab-bento {')) {
  css += '\n' + labBentoCss;
  writeFileSync('waypure.css', css);
  console.log('Lab bento CSS restored!');
} else {
  console.log('Lab bento CSS already exists.');
}
