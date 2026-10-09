import { products } from '../data/products.js';
const waypure = products.filter(p => p.brand === 'Waypure');
console.log('Waypure count:', waypure.length);
waypure.forEach(p => console.log(JSON.stringify({name: p.name, tech: p.technology, mrp: p.mrp, img: p.image || null, features: p.features})));
