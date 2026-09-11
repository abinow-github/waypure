import { chromium } from 'playwright';
import { writeFile } from 'node:fs/promises';

// Export the same procedural model used in the live scroll experience.
const browser = await chromium.launch({ channel: 'chrome', headless: true });
try {
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1.5 });
  await page.goto(process.env.TEST_URL || 'http://127.0.0.1:3000', { waitUntil: 'networkidle' });
  const data = await page.evaluate(async () => {
    const host = document.createElement('div');
    host.style.cssText = 'position:fixed;left:-1000px;top:0;width:520px;height:640px';
    const canvas = document.createElement('canvas');
    host.append(canvas); document.body.append(host);
    const { createScene } = await import('./scene.js');
    const scene = await createScene(canvas);
    scene.update(.3, 0, .016);
    return canvas.toDataURL('image/png').split(',')[1];
  });
  await writeFile('assets/purifier-render.png', Buffer.from(data, 'base64'));
  console.log('Exported assets/purifier-render.png');
} finally { await browser.close(); }
