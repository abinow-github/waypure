const fs = require('fs');
fs.appendFileSync('c:\\xampp\\htdocs\\waypure\\waypure.css', `
/* === Text Glaze for .reveal === */
.reveal { opacity: 1 !important; transform: none !important; position: relative; overflow: hidden; }
.reveal::after {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: linear-gradient(110deg, transparent 0%, rgba(255,255,255,0.5) 50%, transparent 100%);
  background-size: 200% 100%;
  background-position: -200% 0;
  pointer-events: none;
  z-index: 10;
  opacity: 0;
  mix-blend-mode: plus-lighter;
}
.reveal.glaze-sweep-active::after {
  opacity: 1;
  animation: text-glaze-anim 1.5s cubic-bezier(.22,1,.36,1) forwards;
}
@keyframes text-glaze-anim {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; opacity: 0; }
}
`);
