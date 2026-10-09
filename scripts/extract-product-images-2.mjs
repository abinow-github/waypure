/**
 * Extract product images from pages 12-16 of the catalogue.
 */
import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { createCanvas, loadImage } from '@napi-rs/canvas';
import { existsSync } from 'node:fs';

const PRODUCTS = [
  // Page 12: Jade, Rider (alt), Terron (alt), Q-Tone (alt)
  { page: 12, col: 0, slug: 'jade' },
  // skip col 1,2,3 — duplicates of rider, terron, q-tone

  // Page 13: Glance, Waternet, Aqua Clan, Aqua Nine (zinc)
  { page: 13, col: 0, slug: 'glance' },
  { page: 13, col: 1, slug: 'waternet' },
  { page: 13, col: 2, slug: 'aqua-clan' },
  { page: 13, col: 3, slug: 'aqua-nine' },

  // Page 14: Aqua Nine Cu+Zn, Roma, Purosis, Water Cooler
  { page: 14, col: 0, slug: 'aqua-nine-copper' },
  { page: 14, col: 1, slug: 'roma' },
  { page: 14, col: 2, slug: 'purosis' },
  { page: 14, col: 3, slug: 'water-cooler' },

  // Page 15: UTC Under Sink, 4 Stage, Iron Remover, (empty)
  { page: 15, col: 0, slug: 'utc-under-sink' },
  { page: 15, col: 1, slug: '4-stage' },
  { page: 15, col: 2, slug: 'iron-remover' },

  // Page 16: Chrome products (4)
  { page: 16, col: 0, slug: 'chrome-1st-copper' },
  { page: 16, col: 1, slug: 'chrome-1st-alkaline' },
  { page: 16, col: 2, slug: 'chrome-1st-mineral' },
  { page: 16, col: 3, slug: 'chrome-smart-mineral' },
];

const OUT_DIR = 'assets/img/products';
await mkdir(OUT_DIR, { recursive: true });

for (const { page, col, slug } of PRODUCTS) {
  const src = `test-results/brochures/product-catalouge-2023-oct-copy-${page}.png`;
  if (!existsSync(src)) {
    console.warn(`Missing: ${src}`);
    continue;
  }

  const outPath = `${OUT_DIR}/${slug}.png`;
  if (existsSync(outPath)) {
    console.log(`Skip (exists): ${slug}`);
    continue;
  }

  const img = await loadImage(await readFile(src));
  const W = img.width;
  const H = img.height;

  const cropTop = Math.round(H * 0.02);
  const cropH = Math.round(H * 0.50);
  const colW = Math.round(W / 4);
  const cropLeft = col * colW;

  const padX = Math.round(colW * 0.04);
  const padY = Math.round(cropH * 0.01);

  const outW = colW - padX * 2;
  const outH = cropH - padY * 2;

  const canvas = createCanvas(outW, outH);
  const ctx = canvas.getContext('2d');

  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0, 0, outW, outH);

  ctx.drawImage(img, cropLeft + padX, cropTop + padY, outW, outH, 0, 0, outW, outH);

  await writeFile(outPath, canvas.toBuffer('image/png'));
  console.log(`✓ ${slug} → ${outPath}`);
}

console.log('\nDone!');
