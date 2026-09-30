import { chromium } from 'playwright';
import assert from 'node:assert/strict';
import { mkdir } from 'node:fs/promises';
await mkdir('test-results', { recursive: true });
const browser = await chromium.launch({channel:'chrome',headless:true,args:['--enable-webgl']});
const base=process.env.TEST_URL || 'http://127.0.0.1:3000';
const errors=[];
try {
  const page=await browser.newPage();
  page.on('pageerror',e=>errors.push(e.message));
  await page.goto(base,{waitUntil:'networkidle'});
  await page.locator('.scene-wrap.is-rendered').waitFor();
  assert.equal(await page.evaluate(()=>typeof window.AOS.init),'function');
  assert(await page.locator('h1 .flow-letter').count()>20,'Hero uses individual letters');
  for(const [width,height] of [[320,568],[375,667],[390,844],[430,932],[768,1024],[820,1180],[1024,768],[1366,768],[1440,900],[1920,1080],[667,375],[844,390]]) {
    await page.setViewportSize({width,height});
    for(const progress of [0,.66]) {
      await page.evaluate(p=>{document.documentElement.style.scrollBehavior='auto';window.scrollTo(0,(document.querySelector('.experience').offsetHeight-innerHeight)*p);},progress);
      await page.waitForFunction(p=>Math.abs(Number(document.querySelector('.scroll-progress span').dataset.progress)-p)<.003,progress);
      await page.waitForTimeout(250);
      const metrics=await page.evaluate(()=>{
        const chapter=document.querySelector('.chapter.is-active');
        const content=[...chapter.children].filter(el=>getComputedStyle(el).display!=='none').map(el=>el.getBoundingClientRect());
        const controls=document.querySelector('.experience-bottom').getBoundingClientRect();
        return {overflow:document.documentElement.scrollWidth>innerWidth,contentTop:Math.min(...content.map(r=>r.top)),contentBottom:Math.max(...content.map(r=>r.bottom)),controlsTop:controls.top,controlsBottom:controls.bottom,height:innerHeight,rendered:document.querySelector('.scene-wrap').classList.contains('is-rendered')};
      });
      assert(!metrics.overflow,`${width}x${height}: no horizontal overflow`);
      assert(metrics.rendered,`${width}x${height}: 3D remains active`);
      assert(metrics.controlsBottom<=height+1,`${width}x${height}: scroll controls stay onscreen`);
      assert(metrics.contentTop>=0,`${width}x${height}: chapter starts onscreen`);
      assert(metrics.contentBottom<metrics.controlsTop,`${width}x${height}: copy clears navigation (${JSON.stringify(metrics)})`);
      if([320,768,820,844,1440].includes(width)) await page.screenshot({path:`test-results/responsive-${width}-${height}-${progress?'pour':'hero'}.png`});
    }
    console.log(`PASS: ${width}x${height}, hero + pouring`);
  }
  assert.deepEqual(errors,[]);
} finally {await browser.close();}
