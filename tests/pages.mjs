import {chromium} from 'playwright';
import assert from 'node:assert/strict';
const base=process.env.TEST_URL||'http://localhost/water-purifying/';
const url=file=>new URL(file,base.endsWith('/')?base:base+'/').href;
const browser=await chromium.launch({channel:'chrome',headless:true});
const errors=[];
try{
 const page=await browser.newPage({viewport:{width:1440,height:900}});
 page.on('pageerror',e=>errors.push(e.message));
 page.on('response',r=>{if(r.status()>=400)errors.push(`${r.status()} ${r.url()}`)});
 for(const file of ['about.html','products.html','contact.html']){
  await page.goto(url(file),{waitUntil:'networkidle'});
  await page.waitForFunction(()=>document.querySelector('h1').dataset.flowState==='complete');
  assert.equal(await page.locator('h1').count(),1);
  assert.equal(await page.locator('.desktop-nav [aria-current=page]').getAttribute('href'),file);
  assert.equal(await page.locator('.footer-wave').count(),2);
  assert.equal(await page.locator('.contact-tooltip').count(),0);
  assert.equal(await page.evaluate(()=>performance.getEntriesByType('resource').some(e=>/three.module|scene.js/.test(e.name))),false,'Inner pages do not load 3D code');
  assert.equal(await page.locator('[data-contact=call]').getAttribute('href'),'tel:+919061091810');
  for(const [width,height] of [[320,568],[390,844],[768,1024],[1440,900]]){
   await page.setViewportSize({width,height});
   await page.evaluate(()=>scrollTo(0,0));
   await page.waitForTimeout(150);
   assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,`${file} overflow at ${width}`);
   await page.screenshot({path:`test-results/${file.replace('.html','')}-${width}.png`});
  }
  if(file==='contact.html'){
   await page.locator('.contact-intro .enquire-button').click();
   assert(await page.locator('#enquiry-dialog').isVisible());
   assert.equal(await page.locator('#enquiry-product').inputValue(),'General enquiry');
   await page.keyboard.press('Escape');
   assert.equal(await page.locator('.location-card').count(),2);
  }
 }
 await page.goto(url('index.html'),{waitUntil:'networkidle'});
 assert.equal(await page.locator('.catalogue-card').count(),6);
 const fill=()=>page.locator('.body-water-level').evaluate(e=>window.gsap.getProperty(e,'y'));
 const initial=await fill();
 await page.locator('.water-life').evaluate(e=>scrollTo(0,e.getBoundingClientRect().top+scrollY+100));
 await page.waitForTimeout(1800);
 assert((await fill())<initial-150,'Human silhouettes fill with water during scroll');
 await page.waitForFunction(()=>document.querySelector('#life-title').dataset.flowState==='complete');
 await page.screenshot({path:'test-results/water-life-desktop.png'});
 const surface=page.locator('.body-surface').first();
 const before=await surface.getAttribute('transform');
 await page.waitForTimeout(400);
 assert.notEqual(await surface.getAttribute('transform'),before,'Water surface flows');
 await page.evaluate(()=>scrollTo(0,0));
 await page.waitForTimeout(1600);
 assert((await fill())>250,'Fill reverses when scrolling back');
 assert.equal(await page.locator('.water-life.life-in-view').count(),0,'Offscreen water motion pauses');
 for(const width of [320,390,768]){
  await page.setViewportSize({width,height:844});
  await page.locator('.life-visual').scrollIntoViewIfNeeded();
  await page.waitForTimeout(1500);
  assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,`Hydration section overflow at ${width}`);
  await page.screenshot({path:`test-results/water-life-${width}.png`});
 }
 await page.emulateMedia({reducedMotion:'reduce'});
 assert.equal(await page.locator('.body-bubbles').evaluate(e=>getComputedStyle(e).animationName),'none');
 assert.equal(await fill(),0,'Reduced-motion illustration remains filled');
 const touch=await browser.newPage({viewport:{width:390,height:844},deviceScaleFactor:2,isMobile:true,hasTouch:true});
 await touch.goto(url('contact.html'),{waitUntil:'networkidle'});
 await touch.locator('.contact-intro .enquire-button').tap();
 assert.equal(await touch.locator('input[name=name]').evaluate(el=>getComputedStyle(el).fontSize),'16px','Touch form fields prevent automatic focus zoom');
 assert.equal(await touch.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);
 await touch.keyboard.press('Escape');
 await touch.evaluate(()=>scrollTo(0,0));
 const menuBox=await touch.locator('.menu-toggle').boundingBox();
 assert(menuBox.width>=44&&menuBox.height>=44,'Mobile navigation has a comfortable touch target');
 await touch.locator('.menu-toggle').tap();
 await touch.locator('#mobile-nav a[href="about.html"]').tap();
 await touch.waitForURL('**/about.html');
 assert.equal(await touch.locator('#mobile-nav').isVisible(),false);
 await touch.close();
 const noJS=await browser.newPage({javaScriptEnabled:false});
 for(const file of ['index.html','about.html','products.html','contact.html']){
  await noJS.goto(url(file));
  assert(await noJS.locator('h1').isVisible());
  if(file==='index.html') {assert.equal(await noJS.locator('.catalogue-card:visible').count(),6);assert(await noJS.locator('#catalogue-more').isVisible());}
  if(file==='products.html') assert.equal(await noJS.locator('.catalogue-card:visible').count(),56);
 }
 assert.deepEqual(errors,[]);
 console.log('PASS: separate pages, active navigation, six home cards, responsive layouts, contact enquiry, scroll-linked human water fill, surface flow, reverse motion, offscreen pause, reduced motion and no-JS content.');
}finally{await browser.close()}
