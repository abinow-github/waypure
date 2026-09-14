import { chromium } from 'playwright';
import assert from 'node:assert/strict';
import { mkdir, stat } from 'node:fs/promises';

await mkdir('test-results', { recursive: true });
const base = process.env.TEST_URL || 'http://127.0.0.1:3000';
const browser = await chromium.launch({ channel: 'chrome', headless: true });
const errors = [];
try {
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  page.on('pageerror', error => errors.push(error.message));
  page.on('response', response => { if (response.status() >= 400) errors.push(`${response.status()} ${response.url()}`); });
  await page.goto(base, { waitUntil: 'networkidle' });
  await page.locator('.scene-wrap.is-rendered').waitFor();
  await page.waitForFunction(() => !document.documentElement.classList.contains('booting'));
  assert.equal(await page.locator('.footer-wave').count(), 2, 'Footer has two visible water layers');
  assert.equal(await page.locator('[data-contact=call]').getAttribute('href'), 'tel:+919061091810');
  assert((await page.locator('[data-contact=whatsapp]').getAttribute('href')).startsWith('https://wa.me/919895301810?text='));
  const drop = page.locator('[data-contact=whatsapp]');
  const waterBefore = await drop.locator('.contact-fill').evaluate(el => getComputedStyle(el).transform);
  await drop.hover();
  await page.waitForTimeout(750);
  assert.notEqual(await drop.locator('.contact-fill').evaluate(el => getComputedStyle(el).transform), waterBefore, 'Hover raises water inside the contact drop');
  assert.equal(await drop.locator('.contact-tooltip').evaluate(el => getComputedStyle(el).opacity), '1');
  await page.screenshot({ path: 'test-results/mint-banner-contact.png' });
  const cta = page.locator('.chapter-0 .pill-button');
  const buttonBefore = await cta.evaluate(el => getComputedStyle(el, '::before').transform);
  await cta.hover();
  await page.waitForTimeout(800);
  assert.notEqual(await cta.evaluate(el => getComputedStyle(el, '::before').transform), buttonBefore, 'CTA hover has a moving liquid surface');
  await page.mouse.move(500, 450);
  await page.mouse.wheel(0, 900);
  await page.waitForTimeout(70);
  const earlyScroll = await page.evaluate(() => scrollY);
  await page.waitForTimeout(1300);
  const settledScroll = await page.evaluate(() => scrollY);
  assert(settledScroll > earlyScroll + 50, `Wheel input moves smoothly over multiple frames (early: ${earlyScroll}, settled: ${settledScroll})`);
  assert(Math.abs(settledScroll - 900) < 12, 'Smooth wheel scroll reaches the requested distance');
  await page.locator('.nav-cta').click();
  await page.waitForTimeout(1500);
  await page.locator('.product-copy .enquire-button').click();
  const modalPosition = await page.evaluate(() => scrollY);
  await page.mouse.move(40, 450);
  await page.mouse.wheel(0, 500);
  await page.waitForTimeout(400);
  assert.equal(await page.evaluate(() => scrollY), modalPosition, 'Enquiry modal locks background smooth scrolling');
  await page.keyboard.press('Escape');
  await page.evaluate(() => document.querySelector('#footer').scrollIntoView());
  await page.waitForFunction(() => document.querySelector('.footer-heading').dataset.flowState === 'complete');
  await page.screenshot({ path: 'test-results/mint-footer-two-waves.png' });
  for (const [width, height] of [[320,568],[390,844],[768,1024],[844,390]]) {
    await page.setViewportSize({ width, height });
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(700);
    const box = await page.locator('.floating-contact').boundingBox();
    assert(box.x >= 0 && box.x + box.width <= width && box.y >= 0 && box.y + box.height < height - 40, 'Contact controls stay inside the viewport and above scroll navigation');
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
    if (width === 390) await page.screenshot({ path: 'test-results/mint-banner-mobile.png' });
  }
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.waitForFunction(() => document.documentElement.dataset.smoothScroll === 'native');
  assert.equal(await page.locator('.water-preloader').evaluate(el => getComputedStyle(el).display), 'none');
  assert.equal(await drop.locator('.contact-wave').evaluate(el => getComputedStyle(el).animationName), 'none');
  const sizes = { three: (await stat('vendor/three.module.js')).size, preview: (await stat('assets/purifier-render.webp')).size };
  assert(sizes.three < 700000 && sizes.preview < 150000, 'Shipping assets stay below the optimized size budget');
  console.log('Asset sizes:', sizes);
  console.log('Transfer:', await page.evaluate(() => performance.getEntriesByType('resource').filter(e => /three.module|purifier-render/.test(e.name)).map(e => ({ asset: e.name.split('/').pop(), encoded: e.encodedBodySize, decoded: e.decodedBodySize }))));

  // The loading screen must release even when the independent loader script fails.
  const fallback = await browser.newPage({ viewport: { width: 390, height: 844 } });
  await fallback.route('**/preloader.js', route => route.abort());
  await fallback.route('**/app.js', route => route.abort());
  await fallback.goto(base, { waitUntil: 'domcontentloaded' });
  await fallback.screenshot({ path: 'test-results/mint-preloader.png' });
  await fallback.waitForFunction(() => !document.documentElement.classList.contains('booting'), null, { timeout: 4000 });
  assert.equal(await fallback.locator('.water-preloader').evaluate(el => getComputedStyle(el).pointerEvents), 'none');
  assert.deepEqual(errors, []);
  console.log('PASS: contact destinations, water hover, CTA hover, wheel interpolation, modal scroll lock, two footer layers, responsive contact controls, reduced motion, preloader fail-safe, and asset sizes.');
} finally { await browser.close(); }
