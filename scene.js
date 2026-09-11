import * as THREE from 'three';

const clamp = (v, min = 0, max = 1) => Math.max(min, Math.min(max, v));
const smooth = (start, end, value) => { const t = clamp((value - start) / (end - start)); return t * t * (3 - 2 * t); };
const mix = THREE.MathUtils.lerp;

export async function createScene(canvas) {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(devicePixelRatio, 1.75));
  renderer.setClearColor(0x000000, 0);
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = .95;
  renderer.shadowMap.enabled = false;
  renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(32, 1, .1, 60);
  camera.position.set(2.5, 1.9, 9.5);
  camera.lookAt(0, .3, 0);

  const environmentScene = new THREE.Scene();
  environmentScene.background = new THREE.Color(0x63839f);
  const panel = (color, intensity, x, y, z, sx, sy) => {
    const mesh = new THREE.Mesh(new THREE.PlaneGeometry(sx, sy), new THREE.MeshBasicMaterial({ color, colorWrite: true }));
    mesh.material.color.multiplyScalar(intensity); mesh.position.set(x, y, z); mesh.lookAt(0, 0, 0); environmentScene.add(mesh);
  };
  panel(0xffffff, 4, -4, 3, 3, 2, 6); panel(0xbce5ff, 3, 4, 1, -3, 1.5, 5); panel(0xffffff, 3, 0, 5, 0, 6, 4);
  const pmrem = new THREE.PMREMGenerator(renderer);
  const envTarget = pmrem.fromScene(environmentScene, .04);
  scene.environment = envTarget.texture;
  environmentScene.traverse(object => { object.geometry?.dispose(); object.material?.dispose(); });
  pmrem.dispose();
  scene.add(new THREE.HemisphereLight(0xeef8ff, 0x315270, 1.5));
  const key = new THREE.DirectionalLight(0xffffff, 2.7);
  key.position.set(-3, 6, 5); key.castShadow = true; key.shadow.mapSize.set(1024, 1024);
  key.shadow.camera.left = -4; key.shadow.camera.right = 4; key.shadow.camera.top = 5; key.shadow.camera.bottom = -4; key.shadow.normalBias = .035; key.shadow.bias = -.0003; key.shadow.radius = 5;
  scene.add(key);
  const rim = new THREE.DirectionalLight(0xc8edfa, 1.8); rim.position.set(4, 2, -3); scene.add(rim);
  const frontLight = new THREE.DirectionalLight(0xffffff, 1); frontLight.position.set(0, 0, 5); scene.add(frontLight);

  const ivory = new THREE.MeshPhysicalMaterial({ color: 0xf6faff, roughness: .22, metalness: .025, clearcoat: .85, clearcoatRoughness: .16, envMapIntensity: .85 });
  const ivoryEdge = new THREE.MeshPhysicalMaterial({ color: 0xdde9f2, roughness: .18, metalness: .12 });
  const darkBlue = new THREE.MeshPhysicalMaterial({ color: 0x045477, roughness: .19, metalness: .2, clearcoat: .9 });
  const tankMat = new THREE.MeshPhysicalMaterial({ color: 0x0366a1, roughness: .095, metalness: .08, transparent: true, opacity: .58, side: THREE.DoubleSide, depthWrite: false, clearcoat: .9, envMapIntensity: .8 });
  const waterMat = new THREE.MeshPhysicalMaterial({ color: 0x0575b8, roughness: .12, metalness: .14, transparent: true, opacity: .72, side: THREE.DoubleSide, depthWrite: false, clearcoat: .8, envMapIntensity: .75 });
  const surfaceMat = new THREE.MeshPhysicalMaterial({ color: 0x47b3d1, roughness: .2, metalness: .1, transparent: true, opacity: .64, side: THREE.DoubleSide, depthWrite: false, clearcoat: .5, envMapIntensity: .6 });
  const root = new THREE.Group(); scene.add(root);
  function lathe(points, material, parent = root, depth = .76) {
    const geo = new THREE.LatheGeometry(points.map(([x, y]) => new THREE.Vector2(x, y)), 96);
    const mesh = new THREE.Mesh(geo, material); mesh.scale.z = depth; mesh.castShadow = true; mesh.receiveShadow = true; parent.add(mesh); return mesh;
  }
  function cylinder(radiusTop, radiusBottom, height, material, x, y, z, parent = root) {
    const mesh = new THREE.Mesh(new THREE.CylinderGeometry(radiusTop, radiusBottom, height, 64), material);
    mesh.position.set(x, y, z); mesh.castShadow = true; mesh.receiveShadow = true; parent.add(mesh); return mesh;
  }
  function ring(radius, tube, material, x, y, z, parent = root, depth = .76) {
    const mesh = new THREE.Mesh(new THREE.TorusGeometry(radius, tube, 12, 96), material);
    mesh.rotation.x = Math.PI / 2; mesh.scale.y = depth; mesh.position.set(x, y, z); parent.add(mesh); return mesh;
  }
  lathe([[0,-1.16],[.78,-1.16],[.88,-1.14],[.94,-1.09],[.96,-1],[1.015,-.1],[1.035,.02],[1.015,.07],[0,.07]], ivory);
  lathe([[.81,-1.164],[.87,-1.17],[.89,-1.155],[.91,-1.135]], ivoryEdge);
  lathe([[0,.02],[1.025,.02],[1.056,.065],[1.065,.13],[1.06,.19],[1.035,.22],[0,.22]], darkBlue);
  ring(1.059,.012,surfaceMat,0,.19,0);
  lathe([[1.035,.18],[1.065,.22],[1.07,.32],[1.08,1.79],[1.075,1.86]], tankMat);
  lathe([[0,1.86],[1.075,1.86],[1.103,1.88],[1.115,1.92],[1.115,2.01],[1.085,2.04],[.95,2.065],[0,2.075]], ivory);
  ring(1.111,.008,ivoryEdge,0,1.92,0);
  ring(.961,.006,ivoryEdge,0,-1.01,0);
  ring(1.042,.009,ivoryEdge,0,.025,0);
  const lid = lathe([[0,2.065],[.93,2.065],[1.04,2.057],[1.08,2.046]], ivory);
  const filterMat = new THREE.MeshPhysicalMaterial({ color: 0xdde7de, roughness: .5, transparent: true, opacity: .43, depthWrite: false });
  cylinder(.17,.14,.58,filterMat,0,1.52,-.1);
  cylinder(.24,.24,.055,darkBlue,0,1.81,-.1);
  const water = cylinder(1.035,1.025,1,waterMat,0,.7,0); water.geometry.dispose(); water.geometry = new THREE.CylinderGeometry(1.035,1.025,1,80,1,true); water.scale.z = .755; water.castShadow = false; water.renderOrder = 1;
  const surfaceGeo = new THREE.CircleGeometry(1.03, 80); surfaceGeo.rotateX(-Math.PI / 2);
  const surface = new THREE.Mesh(surfaceGeo, surfaceMat); surface.scale.z = .755; root.add(surface); surface.renderOrder = 2;
  const baseSurfacePositions = surfaceGeo.attributes.position.array.slice();
  const waterRings = [];
  for (let i = 0; i < 3; i++) { const material = new THREE.MeshBasicMaterial({ color: 0xc7f6fa, transparent: true, opacity: .16, depthWrite: false }); const r = ring(.35 + i * .24,.003,material,0,1,0); r.renderOrder = 3; waterRings.push(r); }
  const reflectionMaterial = new THREE.MeshBasicMaterial({ color: 0xeaffff, transparent: true, opacity: .19, depthWrite: false, side: THREE.DoubleSide });
  const reflection = new THREE.Mesh(new THREE.CylinderGeometry(1.082,1.069,1.5,32,1,true,-.65,.055),reflectionMaterial);
  reflection.scale.z = .76; reflection.position.y = 1.02; root.add(reflection);
  const reflection2 = new THREE.Mesh(new THREE.CylinderGeometry(1.083,1.07,1.48,32,1,true,.7,.022),reflectionMaterial); reflection2.scale.z = .76; reflection2.position.y = 1.02; root.add(reflection2);
  const tankTicks = new THREE.Group(); root.add(tankTicks);
  const tickMat = new THREE.MeshBasicMaterial({ color: 0xc4edff, transparent: true, opacity: .48, depthWrite: false });
  for (let i = 0; i < 9; i++) {
    const tick = new THREE.Mesh(new THREE.PlaneGeometry(i % 2 ? .027 : .047, .006), tickMat);
    tick.position.set(.64, .46 + i * .135, .665); tick.rotation.y = .5; tankTicks.add(tick);
  }

  // The tap and glass share a world-space pour position, so the stream stays attached while rotating.
  const tap = new THREE.Group(); tap.position.set(0,.09,.8); root.add(tap);
  const collar = cylinder(.12,.12,.14,darkBlue,0,0,.025,tap); collar.rotation.x = Math.PI / 2;
  const pipe = cylinder(.075,.075,.29,ivory,0,0,.19,tap); pipe.rotation.x = Math.PI / 2;
  cylinder(.092,.068,.25,ivory,0,-.095,.32,tap);
  cylinder(.065,.065,.026,ivoryEdge,0,-.229,.32,tap);
  cylinder(.045,.045,.012,darkBlue,0,-.248,.32,tap);
  const handle = new THREE.Group(); handle.position.set(0,.05,.16); tap.add(handle);
  cylinder(.025,.035,.16,ivory,0,.09,0,handle);
  const lever = new THREE.Mesh(new THREE.CapsuleGeometry(.027,.19,5,12),darkBlue); lever.rotation.z = Math.PI/2; lever.position.y = .17; handle.add(lever);
  const nozzle = new THREE.Object3D(); nozzle.position.set(0,-.255,.32); tap.add(nozzle);

  function textTexture(draw) { const c = document.createElement('canvas'); c.width = 512; c.height = 256; draw(c.getContext('2d')); const texture = new THREE.CanvasTexture(c); texture.colorSpace = THREE.SRGBColorSpace; return texture; }
  const logo = textTexture(ctx => { ctx.clearRect(0,0,512,256); ctx.fillStyle = '#153f69'; ctx.textAlign = 'center'; ctx.font = '500 60px Arial'; ctx.fillText('aqua royal',256,146); ctx.font = '15px Arial'; ctx.fillStyle = '#6d93b3'; ctx.fillText('A  B E T T E R  E V E R Y D A Y',256,182); ctx.beginPath(); ctx.moveTo(256,35); ctx.bezierCurveTo(247,50,237,55,237,67); ctx.bezierCurveTo(237,92,275,92,275,67); ctx.bezierCurveTo(275,55,264,49,256,35); ctx.fill(); });
  const brand = new THREE.Mesh(new THREE.PlaneGeometry(.74,.37),new THREE.MeshBasicMaterial({ map: logo, transparent: true, depthWrite: false })); brand.position.set(0,-.68,.744); brand.rotation.x = -.06; root.add(brand);
  const badgeMap = textTexture(ctx => { ctx.clearRect(0,0,512,256); ctx.fillStyle = '#082e5b'; ctx.beginPath(); ctx.arc(256,128,104,0,Math.PI*2); ctx.fill(); ctx.strokeStyle = '#a9dbff'; ctx.lineWidth = 3; ctx.beginPath(); ctx.arc(256,128,94,0,Math.PI*2); ctx.stroke(); ctx.fillStyle = '#e8f6ff'; ctx.textAlign = 'center'; ctx.font = '24px Georgia'; ctx.fillText('AQUA',256,112); ctx.font = '29px Georgia'; ctx.fillText('ROYAL',256,144); ctx.font = '12px Arial'; ctx.fillText('O R I G I N A L',256,174); });
  const badge = new THREE.Mesh(new THREE.PlaneGeometry(.65,.325),new THREE.MeshBasicMaterial({ map: badgeMap, transparent:true, depthWrite:false })); badge.position.set(-.44,1.47,.76); badge.rotation.y = -.18; root.add(badge);

  const bubbles = new THREE.Group(); root.add(bubbles);
  const bubbleGeo = new THREE.SphereGeometry(.016,8,8);
  const bubbleMat = new THREE.MeshPhysicalMaterial({ color:0xd2f7fa,transparent:true,opacity:.48,roughness:.05,metalness:.1 });
  for (let i = 0; i < 24; i++) { const mesh = new THREE.Mesh(bubbleGeo,bubbleMat); const a = i * 2.39996; const r = .25 + (i % 6) * .11; mesh.position.set(Math.cos(a)*r,.2,Math.sin(a)*r*.7); mesh.userData.seed = i / 24; mesh.scale.setScalar(.5 + (i % 4) * .28); bubbles.add(mesh); }

  const glass = new THREE.Group(); scene.add(glass);
  const glassMat = new THREE.MeshPhysicalMaterial({ color:0xd7efff,roughness:.045,metalness:.08,transparent:true,opacity:.3,side:THREE.DoubleSide,depthWrite:false,clearcoat:1,envMapIntensity:1.6 });
  lathe([[.255,0],[.28,.015],[.295,.07],[.35,.78],[.352,.81],[.338,.816],[.335,.78],[.28,.09],[.257,.05],[0,.05]],glassMat,glass,1);
  ring(.345,.009,new THREE.MeshPhysicalMaterial({color:0xf1fffa,roughness:.05,metalness:.2,transparent:true,opacity:.64}),0,.81,0,glass,1);
  ring(.273,.015,glassMat,0,.045,0,glass,1);
  const glassWaterMat = new THREE.MeshPhysicalMaterial({color:0x5bb8c8,roughness:.07,metalness:.09,transparent:true,opacity:.66,side:THREE.DoubleSide,depthWrite:false,clearcoat:1});
  const glassWater = cylinder(.31,.268,1,glassWaterMat,0,.1,0,glass); glassWater.geometry.dispose(); glassWater.geometry = new THREE.CylinderGeometry(.31,.268,1,64,1,true); glassWater.castShadow = false;
  const glassSurface = new THREE.Mesh(new THREE.CircleGeometry(.31,64),surfaceMat); glassSurface.rotation.x=-Math.PI/2; glass.add(glassSurface);
  const glassRipples = [];
  for(let i=0;i<3;i++){const r=ring(.1,.003,new THREE.MeshBasicMaterial({color:0xeafffb,transparent:true,opacity:.4,depthWrite:false}),0,.1,0,glass,1);glassRipples.push(r);}
  const streamGeo = new THREE.CylinderGeometry(.03,.036,1,16,36,true);
  const stream = new THREE.Mesh(streamGeo,new THREE.MeshPhysicalMaterial({color:0x90dbe3,roughness:.04,metalness:.1,transparent:true,opacity:.69,side:THREE.DoubleSide,depthWrite:false,clearcoat:1}));scene.add(stream);
  const streamBase = streamGeo.attributes.position.array.slice();
  const splashes = new THREE.Group();scene.add(splashes);
  for(let i=0;i<12;i++){const drop=new THREE.Mesh(new THREE.SphereGeometry(.016,6,6),bubbleMat);drop.userData.seed=i/12;drop.scale.y=1.6;splashes.add(drop);}
  const shadowPlane = new THREE.Mesh(new THREE.PlaneGeometry(30,30),new THREE.ShadowMaterial({color:0x304a3e,opacity:.12})); shadowPlane.rotation.x=-Math.PI/2;shadowPlane.position.y=-1.18;shadowPlane.receiveShadow=true;scene.add(shadowPlane);
  const shadowCanvas=document.createElement('canvas');shadowCanvas.width=128;shadowCanvas.height=128;const ctx=shadowCanvas.getContext('2d');const gradient=ctx.createRadialGradient(64,64,0,64,64,64);gradient.addColorStop(0,'rgba(35,70,50,0.18)');gradient.addColorStop(1,'rgba(35,70,50,0)');ctx.fillStyle=gradient;ctx.fillRect(0,0,128,128);
  const contactShadow = new THREE.Mesh(new THREE.PlaneGeometry(3.7,2.7),new THREE.MeshBasicMaterial({map:new THREE.CanvasTexture(shadowCanvas),transparent:true,depthWrite:false}));contactShadow.rotation.x=-Math.PI/2;contactShadow.position.y=-1.175;scene.add(contactShadow);
  let mobile = innerWidth <= 760;
  let pointerX=0,pointerY=0,tiltX=0,tiltY=0;
  const onPointer=event=>{if(event.pointerType==='mouse'){pointerX=(event.clientX/innerWidth-.5)*2;pointerY=(event.clientY/innerHeight-.5)*2;}};
  window.addEventListener('pointermove',onPointer,{passive:true});
  const nozzlePosition=new THREE.Vector3();
  const targetLook=new THREE.Vector3();
  const pose = { turn: -.3, zoom: 0, lift: 0 };
  const poseTimeline = window.gsap.timeline({ paused: true })
    .to(pose, { turn: .24, zoom: .15, lift: .04, duration: .34, ease: 'power2.inOut' }, .02)
    .to(pose, { turn: -.03, zoom: 1, lift: .5, duration: .2, ease: 'power2.inOut' }, .36)
    .to(pose, { turn: .1, duration: .15, ease: 'sine.inOut' }, .84)
    .to({}, { duration: .01 }, .99);
  let contextLost=false;
  canvas.addEventListener('webglcontextlost',event=>{event.preventDefault();contextLost=true;canvas.parentElement.classList.remove('is-rendered');});
  canvas.addEventListener('webglcontextrestored',()=>{contextLost=false;canvas.parentElement.classList.add('is-rendered');});
  function resize(){const box=canvas.parentElement.getBoundingClientRect();mobile=innerWidth<=760;renderer.setSize(box.width,box.height,false);camera.aspect=box.width/box.height;camera.updateProjectionMatrix();}
  function update(progress,time,dt){
    if(contextLost)return;
    poseTimeline.progress(progress);
    const fill=smooth(.05,.39,progress);
    const pour=smooth(.47,.86,progress);
    const revealGlass=smooth(.38,.55,progress);
    const finish=smooth(.84,.98,progress);
    tiltX=mix(tiltX,pointerX,Math.min(1,dt*2));tiltY=mix(tiltY,pointerY,Math.min(1,dt*2));
    root.rotation.y=pose.turn+(time ? tiltX*.085+Math.sin(time*.25)*.025 : 0);
    root.rotation.z=(time ? Math.sin(time*.42)*.007 : 0)*(1-revealGlass);
    root.position.y=pose.lift+(time ? Math.sin(time*.75)*.018 : 0)*(1-revealGlass);
    root.position.x=mix(-.06,-.1,revealGlass);
    lid.position.y=Math.sin(fill*Math.PI)*.055*(1-revealGlass);
    const waterHeight=.52+fill*.94-pour*.49;
    water.scale.y=waterHeight;water.position.y=.22+waterHeight/2;
    surface.position.y=.22+waterHeight;
    const verts=surfaceGeo.attributes.position;
    for(let i=0;i<verts.count;i++){const x=baseSurfacePositions[i*3],z=baseSurfacePositions[i*3+2];verts.array[i*3+1]=Math.sin(x*7+time*1.6)*Math.cos(z*8+time)*.009;}
    verts.needsUpdate=true;
    waterRings.forEach((r,i)=>{r.position.y=surface.position.y+.003;r.scale.setScalar(.9+Math.sin(time*1.3+i)*.03);r.material.opacity=.09+Math.sin(time+i)*.035;});
    bubbles.children.forEach(b=>{b.position.y=.24+((time*.07+b.userData.seed)%1)*waterHeight*.95;b.visible=progress<.85;});
    handle.rotation.y=-smooth(.45,.55,progress)*.6+smooth(.82,.88,progress)*.6;
    root.updateMatrixWorld(true);nozzle.getWorldPosition(nozzlePosition);
    glass.visible=revealGlass>.005;glass.scale.setScalar(Math.max(.001,revealGlass));glass.position.set(nozzlePosition.x,-2.05,nozzlePosition.z);
    const glassHeight=.018+pour*.62;
    glassWater.scale.y=glassHeight;glassWater.position.y=.055+glassHeight/2;
    const topRadius=.267+glassHeight*.087;glassWater.scale.x=topRadius/.31;glassWater.scale.z=topRadius/.31;
    glassSurface.position.y=.055+glassHeight;glassSurface.scale.setScalar(topRadius/.31);
    glassWater.visible=pour>.005;glassSurface.visible=pour>.005;
    const flowing=progress>.485&&progress<.87;
    stream.visible=flowing;splashes.visible=flowing;
    const impactY=glass.position.y+(glassHeight+.058)*revealGlass;
    if(flowing){
      const streamLength=nozzlePosition.y-impactY;
      stream.position.set(nozzlePosition.x,impactY+streamLength/2,nozzlePosition.z);stream.scale.y=streamLength;
      const strength=smooth(.485,.51,progress)*(1-smooth(.835,.87,progress));stream.scale.x=stream.scale.z=Math.max(.05,strength);
      const positions=streamGeo.attributes.position;
      for(let i=0;i<positions.count;i++){const y=streamBase[i*3+1];positions.array[i*3]=streamBase[i*3]+Math.sin(y*31-time*14)*.004;positions.array[i*3+2]=streamBase[i*3+2]+Math.cos(y*28-time*12)*.003;}
      positions.needsUpdate=true;
      splashes.children.forEach((drop,i)=>{const t=(time*1.4+drop.userData.seed)%1;const angle=i*2.39996;drop.position.set(nozzlePosition.x+Math.cos(angle)*t*.14,impactY+Math.sin(t*Math.PI)*.13,nozzlePosition.z+Math.sin(angle)*t*.14);drop.scale.setScalar((1-t)*.8+.15);});
    }
    glassRipples.forEach((r,i)=>{r.visible=pour>.01;const cycle=(time*.75+i/3)%1;r.position.y=glassSurface.position.y+.005;r.scale.setScalar(.2+cycle*2.5);r.material.opacity=(1-cycle)*.32*(1-finish*.8);});
    shadowPlane.position.y=mix(-1.18,-2.065,revealGlass);contactShadow.position.y=shadowPlane.position.y+.005;contactShadow.position.z=mix(0,nozzlePosition.z,revealGlass);contactShadow.scale.setScalar(mix(1,.5,revealGlass));
    const distance=mobile?8.05:7.65;
    camera.position.set(mix(2.15,1.55,revealGlass),mix(1.75,1.35,revealGlass)+(time?tiltY*.07:0),distance+pose.zoom*(mobile?2.25:2.65));
    targetLook.set(0,mix(.39,.05,revealGlass),mix(0,.22,revealGlass));camera.lookAt(targetLook);
    renderer.render(scene,camera);
    canvas.dataset.tankFill=waterHeight.toFixed(3);canvas.dataset.glassFill=pour.toFixed(3);canvas.dataset.pouring=String(flowing);
    canvas.dataset.productTurn=pose.turn.toFixed(3);
  }
  resize();
  return {resize,update};
}
