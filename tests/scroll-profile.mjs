import {chromium} from 'playwright';
import {mkdir,writeFile} from 'node:fs/promises';
const base=process.env.TEST_URL||'http://localhost/water-purifying/';
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1440,height:900}});
 await page.addInitScript(()=>{
  window.profileDraws=0;
  for(const method of ['drawElements','drawArrays','drawElementsInstanced','drawArraysInstanced']){
   const original=WebGL2RenderingContext.prototype[method];
   WebGL2RenderingContext.prototype[method]=function(...args){window.profileDraws++;return original.apply(this,args)};
  }
 });
 await page.goto(base,{waitUntil:'networkidle'});
 await page.locator('.scene-wrap.is-rendered').waitFor();
 await page.waitForTimeout(2200);
 const session=await page.context().newCDPSession(page);
 await session.send('Performance.enable');
 const metrics=async()=>Object.fromEntries((await session.send('Performance.getMetrics')).metrics.map(m=>[m.name,m.value]));
 const start=await metrics();
 const frames=await page.evaluate(()=>new Promise(resolve=>{
  const end=document.querySelector('.experience').offsetHeight-innerHeight;
  const deltas=[];let start=0,last=0;window.profileDraws=0;
  function frame(now){if(!start)start=now;if(last)deltas.push(now-last);last=now;const p=Math.min(1,(now-start)/6000);scrollTo(0,end*p);if(p<1)requestAnimationFrame(frame);else resolve({deltas,draws:window.profileDraws});}
  requestAnimationFrame(frame);
 }));
 const finish=await metrics();
 const sorted=frames.deltas.toSorted((a,b)=>a-b);
 const report={frames:sorted.length,medianFrameMs:sorted[Math.floor(sorted.length*.5)],p95FrameMs:sorted[Math.floor(sorted.length*.95)],framesOver34ms:sorted.filter(n=>n>34).length,drawCallsPerFrame:Math.round(frames.draws/sorted.length),metrics:Object.fromEntries(['TaskDuration','ScriptDuration','LayoutDuration','RecalcStyleDuration','LayoutCount','RecalcStyleCount'].map(k=>[k,+(finish[k]-start[k]).toFixed(4)]))};
 await mkdir('test-results',{recursive:true});await writeFile(`test-results/scroll-${process.argv[2]||'profile'}.json`,JSON.stringify(report,null,2));console.log(report);
}finally{await browser.close()}
