import { products } from '../data/products.js';
import { writeFileSync } from 'fs';

const enriched = products.map((p, idx) => {
  const slug = p.id;
  const mrp = p.mrp || 15999;
  const discount = p.mrp ? Math.round(p.mrp * 0.08) : 0;
  const price = p.mrp ? (p.mrp - discount) : 14999;
  const image = p.image || 'assets/purifier-render.webp';
  const desc = `The ${p.brand} ${p.name} is engineered for superior water purification, combining ${p.technology} with high performance filtration to deliver pure, healthy drinking water for Indian homes.`;
  return {
    ...p,
    slug,
    mrp,
    price,
    image,
    description: desc,
    stock: 25,
    rating: +(4.7 + (idx % 4) * 0.1).toFixed(1),
    reviews_count: 18 + (idx * 3) % 45
  };
});

writeFileSync('./data/products.json', JSON.stringify(enriched, null, 2), 'utf8');
console.log('Successfully saved', enriched.length, 'products to data/products.json');
