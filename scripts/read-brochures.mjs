import { readdir, readFile, writeFile, mkdir } from 'node:fs/promises';
import { createCanvas } from '@napi-rs/canvas';
import { createRequire } from 'node:module';
const require = createRequire(import.meta.url);
process.getBuiltinModule ??= name => require(name);
const { getDocument } = await import('pdfjs-dist/legacy/build/pdf.mjs');

await mkdir('test-results/brochures', { recursive: true });
for (const file of (await readdir('info')).filter(file => file.endsWith('.pdf'))) {
  const pdf = await getDocument({ data: new Uint8Array(await readFile(`info/${file}`)), useSystemFonts: true, isEvalSupported: false }).promise;
  const pages = [];
  const slug = file.replace(/\.pdf$/i, '').replace(/[^a-z0-9]+/gi, '-').toLowerCase();
  for (let i = 1; i <= pdf.numPages; i++) {
    const page = await pdf.getPage(i);
    const content = await page.getTextContent();
    const text = content.items.map(item => item.str + (item.hasEOL ? '\n' : ' ')).join('');
    pages.push({ page: i, text });
    const initial = page.getViewport({ scale: 1 });
    const viewport = page.getViewport({ scale: 1600 / Math.max(initial.width, initial.height) });
    const canvas = createCanvas(Math.ceil(viewport.width), Math.ceil(viewport.height));
    await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
    await writeFile(`test-results/brochures/${slug}-${i}.png`, canvas.toBuffer('image/png'));
    console.log(`${file}: page ${i}/${pdf.numPages}, ${text.length} text characters`);
    page.cleanup();
  }
  await writeFile(`test-results/brochures/${slug}.json`, JSON.stringify(pages, null, 2));
  await pdf.destroy();
}
