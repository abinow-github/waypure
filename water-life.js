// Decorative body-water illustration: scroll-linked fill and independent surface flow.
export function createWaterLife({ gsap, ScrollTrigger }) {
  const hero = document.querySelector('.page-hero');
  if (hero) {
    const heroObserver = new IntersectionObserver(([entry]) => hero.classList.toggle('page-water-visible', entry.isIntersecting));
    heroObserver.observe(hero);
  }
  const section = document.querySelector('.water-life');
  if (!section) return;
  const media = gsap.matchMedia();
  media.add('(prefers-reduced-motion: no-preference)', () => {
    const fill = section.querySelector('.body-water-level');
    gsap.fromTo(fill, { y: 300 }, { y: 0, ease: 'none', scrollTrigger: {
      trigger: section, start: 'top 85%', end: 'center 40%', scrub: 1.2,
    } });
    const waves = [...section.querySelectorAll('.body-surface')].map((wave, index) => gsap.fromTo(wave,
      { x: index ? -80 : 0 }, { x: index ? 0 : -80, duration: index ? 3.5 : 4.5, ease: 'sine.inOut', repeat: -1, yoyo: true, paused: true }));
    waves.push(gsap.to(section.querySelector('.life-orbit'), { rotation: 360, svgOrigin: '320 300', duration: 65, ease: 'none', repeat: -1, paused: true }));
    let visible = false;
    const pause = () => { const active = visible && !document.hidden; section.classList.toggle('life-in-view', active); waves.forEach(tween => tween.paused(!active)); };
    const observer = new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; pause(); });
    observer.observe(section);
    document.addEventListener('visibilitychange', pause);
    return () => { observer.disconnect(); document.removeEventListener('visibilitychange', pause); section.classList.remove('life-in-view'); };
  });
}
