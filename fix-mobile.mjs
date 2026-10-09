import { readFileSync, writeFileSync } from 'fs';

let css = readFileSync('waypure.css', 'utf8');

// 1. Fix the product grid mobile view (2 cards per row) and adjust sizes
css = css.replace('@media(max-width:600px) { .catalogue-grid{grid-template-columns:1fr !important;} }', '');

const mobileStyles = `
/* Better Mobile Product Cards (2 per row) */
@media(max-width:600px) {
  .catalogue-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 10px; }
  .card-image-wrap { padding: 10px 10px 0; }
  .card-image { height: 110px; }
  .card-body { padding: 12px 10px; gap: 4px; }
  .card-body h3 { font-size: 13px; margin-bottom: 2px; }
  .card-technology { font-size: 9px; line-height: 1.2; margin-bottom: 8px; min-height: 24px; }
  .card-price-row { margin-bottom: 10px; }
  .card-price { font-size: 14px; }
  .card-actions { grid-template-columns: 1fr; gap: 6px; }
  .card-btn { padding: 8px 4px; font-size: 8px; }
  
  /* Reduce lab section whitespace on mobile */
  .lab-facility.section-space { padding: 40px 5%; }
  .lab-facility-header { margin-bottom: 30px; }
}

/* Lab Section Professional Icon Animations & Spacing Fixes */
.lab-facility.section-space { padding-top: 60px; padding-bottom: 60px; }
.lab-facility-header { margin-bottom: 40px; }

@keyframes float-icon {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}
.bento-icon {
  display: inline-block;
  font-size: 42px;
  margin-bottom: 15px;
  animation: float-icon 4s ease-in-out infinite;
  filter: drop-shadow(0 10px 10px rgba(0,0,0,0.1));
}
.bento-tall .bento-icon { animation-delay: 1.5s; }
.bento-wide .bento-icon { animation-delay: 2.5s; }

/* Remove duplicated Text Glaze blocks that were causing clutter */
`;

writeFileSync('waypure.css', css + mobileStyles);
