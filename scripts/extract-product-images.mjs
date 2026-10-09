/**
 * Extract individual product images from catalogue PDF page renders.
 * Each catalogue page has 4 products in a row (top half = product images).
 * We crop the top ~48% of each page, then split into 4 equal columns.
 * Output: assets/img/products/<slug>.webp
 */
import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { createCanvas, loadImage } from '@napi-rs/canvas';
import { existsSync } from 'node:fs';

// Product mapping: which page, which column (0-3 left to right), slug name
// Based on visual inspection of extracted catalogue pages
const PRODUCTS = [
  // Page 8 (index 8): Aqua Pure, Klickpro, Bravo, Kairox
  { page: 8, col: 0, slug: 'aqua-pure' },
  { page: 8, col: 1, slug: 'klickpro' },
  { page: 8, col: 2, slug: 'bravo' },
  { page: 8, col: 3, slug: 'kairox' },

  // Page 9 (index 9): Xzora, Rider, Terron, Q-Tone
  { page: 9, col: 0, slug: 'xzora' },
  { page: 9, col: 1, slug: 'rider' },
  { page: 9, col: 2, slug: 'terron' },
  { page: 9, col: 3, slug: 'q-tone' },

  // Page 10 (index 10): Xpure, Water Lily, Onix, 5G Pro
  { page: 10, col: 0, slug: 'xpure' },
  { page: 10, col: 1, slug: 'water-lily' },
  { page: 10, col: 2, slug: 'onix' },
  { page: 10, col: 3, slug: '5g-pro' },

  // Page 11 (index 11): 35 LPH Commercial, Mars, Epic, Plus Dispenser
  { page: 11, col: 0, slug: '35-lph-commercial' },
  { page: 11, col: 1, slug: 'mars' },
  { page: 11, col: 2, slug: 'epic' },
  { page: 11, col: 3, slug: 'plus-dispenser' },

  // Page 12 (index 12): Glance + others
  { page: 12, col: 0, slug: 'glance-p12c0' },
  { page: 12, col: 1, slug: 'glance-p12c1' },
  { page: 12, col: 2, slug: 'glance-p12c2' },
  { page: 12, col: 3, slug: 'glance-p12c3' },
];

const OUT_DIR = 'assets/img/products';
await mkdir(OUT_DIR, { recursive: true });

for (const { page, col, slug } of PRODUCTS) {
  const src = `test-results/brochures/product-catalouge-2023-oct-copy-${page}.png`;
  if (!existsSync(src)) {
    console.warn(`Missing: ${src}`);
    continue;
  }

  const img = await loadImage(await readFile(src));
  const W = img.width;
  const H = img.height;

  // Top 48% = product image area (images are in the top half of each page)
  // with a small top padding (~4% of height)
  const cropTop = Math.round(H * 0.02);
  const cropH = Math.round(H * 0.50);
  const colW = Math.round(W / 4);
  const cropLeft = col * colW;

  // Add a tiny inner padding to avoid the green decorative lines
  const padX = Math.round(colW * 0.04);
  const padY = Math.round(cropH * 0.01);

  const outW = colW - padX * 2;
  const outH = cropH - padY * 2;

  const canvas = createCanvas(outW, outH);
  const ctx = canvas.getContext('2d');

  // Draw white background first
  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0, 0, outW, outH);

  ctx.drawImage(
    img,
    cropLeft + padX, cropTop + padY, outW, outH,
    0, 0, outW, outH
  );

  const outPath = `${OUT_DIR}/${slug}.png`;
  await writeFile(outPath, canvas.toBuffer('image/png'));
  console.log(`✓ ${slug} → ${outPath} (${outW}×${outH})`);
}

console.log('\nDone! Product images extracted.');
