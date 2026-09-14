import { createWaterEffects } from './water-effects.js';
import { createInterface } from './interface.js';
const { gsap, ScrollTrigger } = window;
gsap.registerPlugin(ScrollTrigger);
gsap.ticker.lagSmoothing(0);
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const experience = document.querySelector('.experience');
const chapters = [...document.querySelectorAll('.chapter')];
const stageButtons = [...document.querySelectorAll('.chapter-nav button')];
const progressBar = document.querySelector('.scroll-progress span');
const sceneWrap = document.querySelector('#scene-wrap');
const waterLabel = document.querySelector('#water-label');
let targetProgress = 0, smoothProgress = 0, activeChapter = 0, scene = null, previousTime = 0, staticRendered = false;
let siteInterface;
let viewportHeight = innerHeight, experienceHeight = experience.offsetHeight;
const clamp = (n, a = 0, b = 1) => Math.max(a, Math.min(b, n));
const chapterPositions = [0, .31, .61, .92];
const storyTimeline = gsap.timeline({ paused: true });
storyTimeline.set(chapters.slice(1), { opacity: 0, y: 32 });
storyTimeline.to(chapters[0], { opacity: 0, y: -24, duration: .11, ease: 'power2.inOut' }, .12)
  .to(chapters[1], { opacity: 1, y: 0, duration: .1, ease: 'power2.out' }, .16)
  .to(chapters[1], { opacity: 0, y: -24, duration: .12, ease: 'power2.inOut' }, .4)
  .to(chapters[2], { opacity: 1, y: 0, duration: .1, ease: 'power2.out' }, .45)
  .to(chapters[2], { opacity: 0, y: -24, duration: .12, ease: 'power2.inOut' }, .7)
  .to(chapters[3], { opacity: 1, y: 0, duration: .11, ease: 'power2.out' }, .75)
  .to('.experience-sticky', { backgroundColor: '#04244b', '--scene-ink': '#f1f9ff', '--scene-muted': '#b9d9f1', '--scene-rule': '#4978a1', '--scene-accent': '#7cd5ff', duration: .17, ease: 'power2.inOut' }, .36)
  .to('.water-atmosphere', { opacity: .12, duration: .17 }, .36)
  .to('.scene-halo', { opacity: .15, duration: .17 }, .36)
  .to('.product-label', { backgroundColor: '#ffffff0a', borderColor: '#8ccaff26', duration: .17 }, .36)
  .to('.product-plinth', { opacity: .2, y: 24, duration: .15 }, .38)
  .to('.hero-watermark', { opacity: .07, x: -45, duration: .5, ease: 'none' }, .4)
  .to({}, { duration: .01 }, .99);

function measure() {
  viewportHeight = innerHeight;
  experienceHeight = experience.offsetHeight;
  updateScroll();
  scene?.resize(); staticRendered = false;
}
function updateScroll() {
  targetProgress = reduceMotion.matches ? 0 : clamp(-experience.getBoundingClientRect().top / Math.max(1, experienceHeight - viewportHeight));
}
function goToChapter(index) {
  if (reduceMotion.matches) { document.querySelector('#difference').scrollIntoView({ behavior: 'auto' }); return; }
  const top = experience.getBoundingClientRect().top + scrollY;
  siteInterface.scrollTo(top + (experienceHeight - viewportHeight) * chapterPositions[index]);
}
document.querySelectorAll('[data-chapter]').forEach(button => button.addEventListener('click', event => {
  event.preventDefault(); goToChapter(Number(button.dataset.chapter)); closeMenu();
}));
document.querySelector('[data-next-chapter]').addEventListener('click', () => {
  if (activeChapter < 3 && !reduceMotion.matches) goToChapter(activeChapter + 1);
  else siteInterface.scrollTo(document.querySelector('#difference'));
});
window.addEventListener('scroll', updateScroll, { passive: true });
window.addEventListener('resize', measure, { passive: true });
reduceMotion.addEventListener('change', () => { smoothProgress = 0; measure(); });

const menuToggle = document.querySelector('.menu-toggle');
const mobileNav = document.querySelector('#mobile-nav');
function closeMenu() { menuToggle.setAttribute('aria-expanded', 'false'); mobileNav.hidden = true; }
menuToggle.addEventListener('click', () => { const open = menuToggle.getAttribute('aria-expanded') !== 'true'; menuToggle.setAttribute('aria-expanded', String(open)); mobileNav.hidden = !open; });
mobileNav.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });

document.body.classList.add('js-ready', 'gsap-active');
const waterEffects = createWaterEffects({ gsap, ScrollTrigger, storyTimeline });
document.fonts.ready.then(() => waterEffects.refresh());
gsap.matchMedia().add('(prefers-reduced-motion: no-preference)', () => {
  document.querySelectorAll('.reveal').forEach(el => {
    if (el.matches('.liquid-text') || el.querySelector('.liquid-text')) { gsap.set(el, { opacity: 1, y: 0 }); return; }
    gsap.fromTo(el, { opacity: 0, y: 40 }, { opacity: 1, y: 0, duration: 1.1, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 91%', once: true } });
  });
  if (scrollY < 100) gsap.fromTo('.chapter-0 > :not(.liquid-text)', { opacity: .85, y: 18 }, { opacity: 1, y: 0, duration: .9, stagger: .06, ease: 'power3.out' });
  const ambientTweens = [
    gsap.to('.water-caustics', { xPercent: 4, yPercent: 3, rotation: -7, scale: 1.12, duration: 13, yoyo: true, repeat: -1, ease: 'sine.inOut' }),
    gsap.to('.water-light', { rotation: 5, opacity: .35, duration: 11, yoyo: true, repeat: -1, ease: 'sine.inOut' }),
    gsap.to('.water-orb', { y: -24, x: 7, duration: 5, stagger: 1.3, yoyo: true, repeat: -1, ease: 'sine.inOut' }),
    gsap.to('.banner-current', { xPercent: 1.5, yPercent: -1, duration: 9, repeat: -1, yoyo: true, ease: 'sine.inOut' }),
  ];
  let heroVisible = true;
  const pauseAmbient = () => ambientTweens.forEach(tween => tween.paused(!heroVisible || document.hidden));
  const heroObserver = new IntersectionObserver(([entry]) => { heroVisible = entry.isIntersecting; pauseAmbient(); });
  heroObserver.observe(experience);
  document.addEventListener('visibilitychange', pauseAmbient);
  gsap.to('.ritual-art', { y: -22, rotation: 2, ease: 'none', scrollTrigger: { trigger: '.ritual', start: 'top bottom', end: 'bottom top', scrub: 1.2 } });
  return () => { heroObserver.disconnect(); document.removeEventListener('visibilitychange', pauseAmbient); };
});
document.querySelector('#year').textContent = new Date().getFullYear();

const dialog = document.querySelector('#enquiry-dialog');
const form = document.querySelector('#enquiry-form');
const formView = document.querySelector('#enquiry-form-view');
const successView = document.querySelector('#enquiry-success');
let enquiryUrl = null;
function openEnquiry() {
  closeMenu(); siteInterface?.stop(); dialog.showModal(); document.body.style.overflow = 'hidden';
}
siteInterface = createInterface({ gsap, ScrollTrigger, reduceMotion, dialog, openEnquiry });
document.querySelectorAll('.enquire-button').forEach(button => button.addEventListener('click', openEnquiry));
function closeDialog() { dialog.close(); }
document.querySelector('.dialog-close').addEventListener('click', closeDialog);
dialog.addEventListener('click', event => { if (event.target === dialog) { const box = dialog.getBoundingClientRect(); if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) closeDialog(); } });
dialog.addEventListener('close', () => { document.body.style.overflow = ''; });
form.addEventListener('submit', event => {
  event.preventDefault();
  if (!form.reportValidity()) return;
  const data = new FormData(form);
  const name = String(data.get('name')).trim();
  const city = String(data.get('city')).trim();
  if (!name || !city) { const field = form.elements[!name ? 'name' : 'city']; field.setCustomValidity('Please enter a value.'); field.reportValidity(); field.addEventListener('input', () => field.setCustomValidity(''), { once: true }); return; }
  const content = `AQUA ROYAL — PRODUCT ENQUIRY\n\nProduct: Aqua Royal Original\nFinish: Ocean blue / Warm ivory\n\nName: ${name}\nEmail: ${String(data.get('email')).trim()}\nCity: ${city}\n\nQuestions:\n${String(data.get('message')).trim() || 'Please share pricing, availability, dimensions, capacity, filtration specifications, warranty, and maintenance information.'}\n\nPrepared on ${new Date().toLocaleDateString('en-IN')}\nThis enquiry was prepared locally and has not been sent.\n`;
  if (enquiryUrl) URL.revokeObjectURL(enquiryUrl);
  enquiryUrl = URL.createObjectURL(new Blob([content], { type: 'text/plain;charset=utf-8' }));
  document.querySelector('#download-enquiry').href = enquiryUrl;
  formView.hidden = true; successView.hidden = false;
  dialog.setAttribute('aria-label', 'Your product enquiry is ready'); dialog.removeAttribute('aria-labelledby');
  document.querySelector('#download-enquiry').focus();
});
document.querySelector('#edit-enquiry').addEventListener('click', () => {
  formView.hidden = false; successView.hidden = true;
  dialog.removeAttribute('aria-label'); dialog.setAttribute('aria-labelledby', 'enquiry-title'); form.elements.name.focus();
});

function renderChapters(progress) {
  storyTimeline.progress(progress);
  const stage = progress < .19 ? 0 : progress < .47 ? 1 : progress < .77 ? 2 : 3;
  chapters.forEach((chapter, index) => {
    if (stage !== activeChapter || !chapter.dataset.initialized) {
      chapter.classList.toggle('is-active', index === stage);
      chapter.inert = index !== stage;
      chapter.setAttribute('aria-hidden', String(index !== stage));
      chapter.dataset.initialized = 'true';
    }
  });
  if (stage !== activeChapter) {
    stageButtons.forEach((button, index) => { button.classList.toggle('active', index === stage); if (index === stage) button.setAttribute('aria-current', 'step'); else button.removeAttribute('aria-current'); });
    waterLabel.innerHTML = ['A fresh kind<br>of everyday.', 'A little more<br>possibility.', 'Find your<br>natural flow.', 'A glass full<br>of good.'][stage];
    activeChapter = stage;
  }
  progressBar.style.width = `${progress * 100}%`;
  sceneWrap.dataset.stage = String(stage);
}
function frame(time) {
  if (document.hidden) return;
  const dt = Math.min((time - previousTime) / 1000 || .016, .25);
  previousTime = time;
  smoothProgress += (targetProgress - smoothProgress) * (1 - Math.exp(-dt * (document.documentElement.dataset.smoothScroll === 'enabled' ? 16 : 9)));
  if (Math.abs(targetProgress - smoothProgress) < .00005) smoothProgress = targetProgress;
  const inView = scrollY < experienceHeight + 100;
  if (inView) {
    renderChapters(smoothProgress);
    if (!reduceMotion.matches || !staticRendered) {
      scene?.update(smoothProgress, reduceMotion.matches ? 0 : time * .001, dt);
      staticRendered = Boolean(scene);
    }
  }
}
document.addEventListener('visibilitychange', () => {
  if (!document.hidden) previousTime = 0;
});
measure();
gsap.ticker.add(time => frame(time * 1000));
import('./scene.js').then(async ({ createScene }) => {
  scene = await createScene(document.querySelector('#product-canvas'));
  scene.resize(); scene.update(smoothProgress, 0, .016);
  sceneWrap.classList.add('is-rendered');
}).catch(error => {
  console.info('Static product view is active.', error.message);
  sceneWrap.classList.add('is-fallback');
});
