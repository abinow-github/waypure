import { chromium } from 'playwright';
const browser = await chromium.launch({channel:'chrome',headless:true,args:process.env.LIVE_GPU ? ['--enable-webgl'] : ['--enable-webgl','--use-angle=swiftshader']});
try {
  const page = await browser.newPage({viewport:{width:1440,height:960}});
  page.on('pageerror', error => console.log('PAGE ERROR:', error.stack));
  page.on('console', message => { if (message.type() !== 'log') console.log('CONSOLE:', message.type(), message.text().slice(0,400)); });
  await page.goto(process.env.TEST_URL || 'http://127.0.0.1:3000', {waitUntil:'networkidle'});
  await page.waitForTimeout(3500);
  console.log(await page.evaluate(()=>({gsap:typeof window.gsap,scrollTrigger:typeof window.ScrollTrigger,scene:document.querySelector('.scene-wrap').className,body:document.body.className})));
  await page.screenshot({path:'test-results/theme-desktop.png'});
  await page.setViewportSize({width:390,height:844});
  await page.waitForTimeout(1500);
  await page.screenshot({path:'test-results/theme-mobile.png'});
  await page.setViewportSize({width:375,height:667});
  await page.waitForTimeout(1300);
  await page.screenshot({path:'test-results/theme-small-phone.png'});
  await page.evaluate(()=>{document.documentElement.style.scrollBehavior='auto';window.scrollTo(0,(document.querySelector('.experience').offsetHeight-innerHeight)*.66);});
  await page.waitForTimeout(2200);
  await page.screenshot({path:'test-results/theme-small-phone-pour.png'});
  console.log(await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth,controlsBottom:document.querySelector('.experience-bottom').getBoundingClientRect().bottom,viewport:innerHeight,pouring:document.querySelector('canvas').dataset.pouring})));
  await page.goto('http://localhost/water-purifying/',{waitUntil:'networkidle'});
  await page.locator('.scene-wrap.is-rendered').waitFor();
  console.log('XAMPP subdirectory: 3D scene rendered.');
} finally {await browser.close();}
