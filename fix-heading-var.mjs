import { readFileSync, writeFileSync } from 'fs';

let css = readFileSync('waypure.css', 'utf8');

const rootVars = `
:root {
  --heading-xl: clamp(2.3rem, 5.5vw, 4.2rem); 
}
`;

if (!css.includes('--heading-xl:')) {
  css = rootVars + '\n' + css;
} else {
  css = css.replace(/--heading-xl:[^;\}]+/, '--heading-xl: clamp(2.3rem, 5.5vw, 4.2rem)');
}

writeFileSync('waypure.css', css);
console.log('Fixed heading-xl variable!');
