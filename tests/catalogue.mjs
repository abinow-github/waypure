import {chromium} from 'playwright';
import assert from 'node:assert/strict';
import {mkdir} from 'node:fs/promises';

await mkdir('test-results',{recursive:true});
const base=process.env.TEST_URL||'http://localhost/water-purifying/';
const browser=await chromium.launch({channel:'chrome',headless:true});
const errors=[];
try {
  const page=await browser.newPage({viewport:{width:1440,height:960}});
  page.on('pageerror',error=>errors.push(error.message));
  page.on('response',response=>{if(response.status()>=400)errors.push(`${response.status()} ${response.url()}`);});
  await page.goto(base,{waitUntil:'networkidle'});
  await page.locator('.scene-wrap.is-rendered').waitFor();
  assert.match(await page.title(),/^Waypure/);
  assert(!/Aqua Royal|AQUA ROYAL|Original Series|99467|Lorem ipsum/i.test(await page.locator('body').textContent()),'No old brand or placeholder content');
  assert.equal(await page.locator('.catalogue-card').count(),6,'Home contains only six cards');
  assert.equal(await page.locator('.catalogue-card img').count(),0,'Product cards intentionally have no photographs');
  assert.equal(await page.locator('.catalogue-card:visible').count(),6);
  assert.equal(await page.locator('.card-price').first().evaluate(el=>getComputedStyle(el).color),'rgb(67, 131, 49)','Product prices use accessible brand green');
  assert(await page.locator('.brand-logo').first().evaluate(img=>img.naturalWidth>0&&img.getAttribute('src')==='assets/img/logo/logo.png'));
  await page.locator('#catalogue-more').click();
  await page.waitForURL('**/products.html');
  assert.equal(await page.locator('.catalogue-card:visible').count(),56);
  await page.locator('#details').scrollIntoViewIfNeeded();
  await page.waitForFunction(()=>!document.documentElement.classList.contains('lenis-scrolling'));
  await page.waitForFunction(()=>document.querySelector('#catalogue-title').dataset.flowState==='complete');
  await page.screenshot({path:'test-results/waypure-products-desktop.png'});
  const brand=async name=>page.locator(`[data-brand-filter="${name}"]`).click();
  await brand('Prolife');
  assert.equal(await page.locator('.catalogue-card:visible').count(),4);
  assert(await page.getByRole('heading',{name:'Fiesta Blue',exact:true}).isVisible());
  assert(await page.getByRole('heading',{name:'Viber',exact:true}).isVisible());
  await brand('Chrome');
  assert.equal(await page.locator('.catalogue-card:visible').count(),4);
  const copper=page.locator('.catalogue-card').filter({has:page.getByRole('heading',{name:'1st Choice Copper',exact:true})});
  assert.equal(await copper.locator('.card-price').textContent(),'₹23,999');
  await brand('All');
  assert.equal(await page.locator('.catalogue-card:visible').count(),56,'All products shown by default');
  const search=page.locator('#product-search');
  await search.fill('kairox');
  await page.waitForFunction(()=>document.querySelector('[data-catalogue-count]').textContent==='Showing 1 of 1 products');
  assert.equal(await page.locator('.catalogue-card:visible .card-price').textContent(),'₹17,899','Kairox uses the visually verified price, not PDF text extraction order');
  await search.fill('aqua pure');
  await page.waitForFunction(()=>document.querySelector('.catalogue-card:not([hidden]) h3').textContent==='Aqua Pure');
  assert.equal(await page.locator('.catalogue-card:visible .card-price').textContent(),'₹14,499');
  await search.fill('not-a-real-product');
  await page.locator('.catalogue-empty').waitFor();
  assert.equal(await page.locator('.catalogue-card:visible').count(),0);
  await search.fill('');
  await page.locator('#product-category').selectOption('Commercial & treatment');
  await page.waitForFunction(()=>document.querySelector('[data-catalogue-count]').textContent==='Showing 2 of 2 products');
  await page.locator('#product-category').selectOption('All');
  await search.fill('q-tone');
  await page.waitForFunction(()=>document.querySelector('[data-catalogue-count]').textContent==='Showing 1 of 1 products');
  const selected=page.locator('.catalogue-card:visible');
  assert.equal(await selected.locator('.card-capacity strong').textContent(),'15 L');
  assert.equal(await selected.locator('.card-price').textContent(),'₹19,990');
  await selected.locator('.card-details summary').click();
  assert.match(await selected.locator('.card-detail-body').textContent(),/10-stage purification/);
  await selected.locator('.card-enquiry').click();
  assert.equal(await page.locator('#enquiry-product').inputValue(),'Waypure Q-Tone');
  await page.locator('input[name=name]').fill('Test Customer');
  await page.locator('input[name=email]').fill('test@example.com');
  await page.locator('input[name=city]').fill('Angadipuram');
  await page.locator('#enquiry-form button[type=submit]').click();
  const destination=new URL(await page.locator('#whatsapp-enquiry').getAttribute('href'));
  assert.equal(destination.hostname,'wa.me');assert.equal(destination.pathname,'/919895301810');
  assert.match(destination.searchParams.get('text'),/Product \/ service: Waypure Q-Tone/);
  assert(!destination.searchParams.get('text').includes('Aqua Royal'));
  assert.equal(await page.locator('#download-enquiry').getAttribute('download'),'waypure-enquiry.txt');
  await page.keyboard.press('Escape');
  await search.fill('');
  await page.waitForFunction(()=>document.querySelector('[data-catalogue-count]').textContent==='Showing 56 of 56 products');
  for(const [width,height] of [[320,700],[390,844],[768,1024],[1024,768]]) {
    await page.setViewportSize({width,height});
    for(const target of ['#details','#services','#footer']){
      await page.locator(target).scrollIntoViewIfNeeded();
      await page.waitForTimeout(500);
      assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,`${target}: no horizontal overflow at ${width}px`);
    }
    if(width===390){
      await page.locator('#details').scrollIntoViewIfNeeded();
      await page.screenshot({path:'test-results/waypure-products-mobile.png'});
      await page.locator('#footer').scrollIntoViewIfNeeded();
      await page.screenshot({path:'test-results/waypure-contact-mobile.png'});
    }
  }
  assert.equal(await page.evaluate(()=>performance.getEntriesByType('resource').filter(entry=>entry.name.endsWith('.pdf')).length),0,'Large PDFs never load with the page');
  const noJS=await browser.newPage({javaScriptEnabled:false});
  await noJS.goto(new URL('products.html',base.endsWith('/')?base:base+'/').href);
  assert.equal(await noJS.locator('.catalogue-card:visible').count(),56,'Full catalogue remains readable without JavaScript');
  assert.deepEqual(errors,[]);
  console.log('PASS: Waypure branding, 56 image-free products, source-verified prices, separate Prolife models, filters/search/full collection, product enquiry handoff, responsive catalogue/contact sections, no eager PDFs, and no-JS content.');
} finally {await browser.close();}
