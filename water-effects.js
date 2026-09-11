// Liquid type and decorative bubbles share the site's GSAP motion preferences.
export function createWaterEffects({ gsap, ScrollTrigger, storyTimeline }) {
  const textTargets = [...document.querySelectorAll('.chapter h1, .chapter h2, .chapter > p, .difference h2, .difference-heading > p, .benefit h3, .benefit p, .ritual h2, .ritual-content > p, .product-copy h2, .product-description, .faq-intro h2, .faq-intro > p, .closing h2, .footer-heading, .footer-callout > p')];
  const visualWords = new Map();

  // Preserve the original semantic heading/paragraph once for assistive technology.
  // Visual spans keep existing emphasis and <br> behavior, including mobile breaks.
  for (const element of textTargets) {
    const accessible = document.createElement('span');
    accessible.className = 'sr-only flow-accessible';
    accessible.textContent = [...element.childNodes].map(node => node.nodeName === 'BR' ? ' ' : node.textContent).join('').replace(/\s+/g, ' ').trim();
    const visual = document.createElement('span');
    visual.className = 'flow-visual';
    visual.setAttribute('aria-hidden', 'true');
    while (element.firstChild) visual.append(element.firstChild);
    const walker = document.createTreeWalker(visual, NodeFilter.SHOW_TEXT);
    const nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    for (const node of nodes) {
      const fragment = document.createDocumentFragment();
      node.textContent.split(/(\s+)/).forEach(part => {
        if (!part) return;
        if (/^\s+$/.test(part)) { fragment.append(document.createTextNode(part)); return; }
        const word = document.createElement('span');
        word.className = 'flow-word';
        const ink = document.createElement('span');
        ink.className = 'flow-ink';
        ink.textContent = part;
        word.append(ink); fragment.append(word);
      });
      node.replaceWith(fragment);
    }
    element.append(accessible, visual);
    element.classList.add('liquid-text');
    visualWords.set(element, [...element.querySelectorAll('.flow-ink')]);
  }

  // Attach chapter reveals to the existing reversible scroll timeline.
  // Reading remains stable once the moving water edge has passed each word.
  const chapterStarts = [.0, .157, .447, .757];
  document.querySelectorAll('.chapter').forEach((chapter, index) => {
    if (index === 0) return;
    const heading = chapter.querySelector('h2');
    const paragraph = chapter.querySelector(':scope > p');
    for (const [target, offset] of [[heading, 0], [paragraph, .025]]) {
      const words = visualWords.get(target);
      if (!words?.length) continue;
      storyTimeline.fromTo(words, { '--waterline': '112%', yPercent: 48, x: -5, skewY: 3, backgroundPosition: '180% 50%' }, {
        '--waterline': '-18%', yPercent: 0, x: 0, skewY: 0, backgroundPosition: '-70% 50%',
        duration: .092, stagger: { amount: .035 }, ease: 'power2.out',
      }, chapterStarts[index] + offset);
    }
  });

  const fields = [];
  const parents = document.querySelectorAll('.experience-sticky, .difference, .ritual, .product-section, .faq-section, .closing, .water-footer');
  parents.forEach((parent, fieldIndex) => {
    const field = document.createElement('div');
    field.className = 'bubble-field';
    field.setAttribute('aria-hidden', 'true');
    const positions = [4, 94, 11, 86, 48, 97, 34, 72, 7, 58, 91, 22];
    const count = parent.matches('.experience-sticky, .water-footer') ? 12 : 8;
    for (let i = 0; i < count; i++) {
      const bubble = document.createElement('span');
      bubble.className = 'rising-bubble';
      const size = 7 + ((i * 11 + fieldIndex * 7) % 29);
      bubble.style.setProperty('--bubble-size', `${size}px`);
      bubble.style.setProperty('--bubble-x', `${positions[i]}%`);
      bubble.style.setProperty('--bubble-duration', `${15 + (i * 3 + fieldIndex) % 13}s`);
      bubble.style.setProperty('--bubble-delay', `${-((i * 4.7 + fieldIndex * 2) % 25)}s`);
      bubble.style.setProperty('--bubble-drift', `${i % 2 ? -22 : 26}px`);
      field.append(bubble);
    }
    parent.prepend(field); fields.push(field);
  });
  const footer = document.querySelector('.water-footer');
  let footerInView = false;
  let footerTweens = [];
  const updateFooterMotion = () => footerTweens.forEach(tween => tween.paused(!footerInView || document.hidden));
  const fieldVisibility = new IntersectionObserver(entries => {
    entries.forEach(({ target, isIntersecting }) => {
      target.classList.toggle('is-water-visible', isIntersecting);
      if (target === footer) { footerInView = isIntersecting; updateFooterMotion(); }
    });
  }, { threshold: 0 });
  fields.forEach(field => fieldVisibility.observe(field));
  fieldVisibility.observe(footer);
  const fieldSize = new ResizeObserver(entries => {
    entries.forEach(({ target, contentRect }) => target.style.setProperty('--bubble-travel', `${-(contentRect.height + 100)}px`));
  });
  fields.forEach(field => fieldSize.observe(field));
  const visibility = () => { document.body.classList.toggle('motion-sleeping', document.hidden); updateFooterMotion(); };
  document.addEventListener('visibilitychange', visibility);
  visibility();

  const media = gsap.matchMedia();
  media.add('(prefers-reduced-motion: no-preference)', () => {
    // Drive the SVG surface from the same clock as text to avoid delayed
    // compositor startup when the footer first enters the viewport.
    footerTweens = [...footer.querySelectorAll('.footer-wave')].map((wave, index) => gsap.fromTo(wave,
      { xPercent: index === 1 ? -50 : 0 },
      { xPercent: index === 1 ? 0 : -50, duration: [23, 19, 15][index], repeat: -1, ease: 'none', paused: true }
    ));
    updateFooterMotion();
    const timelines = [];
    const playReveal = (element, immediate = false) => {
      const words = visualWords.get(element);
      const isHeading = /^H[1-3]$/.test(element.tagName);
      const timeline = gsap.timeline({ paused: !immediate,
        onStart: () => element.dataset.flowState = 'revealing',
        onComplete: () => { element.dataset.flowState = 'complete'; gsap.set(words, { clearProps: 'clipPath,transform,backgroundPosition' }); },
      });
      timeline.fromTo(words, { '--waterline': '112%', yPercent: isHeading ? 52 : 32, x: -6, skewY: 3, backgroundPosition: '180% 50%' }, {
        '--waterline': '-18%', yPercent: 0, x: 0, skewY: 0, backgroundPosition: '-70% 50%',
        duration: isHeading ? 1.05 : .85, stagger: { amount: isHeading ? .32 : .22 }, ease: 'power3.out',
      });
      if (!immediate) ScrollTrigger.create({ trigger: element, start: 'top 90%', once: true, onEnter: () => timeline.play() });
      timelines.push(timeline);
    };
    for (const element of textTargets) {
      if (element.closest('.chapter')) {
        if (element.closest('.chapter-0')) playReveal(element, true);
      } else playReveal(element);
    }
    return () => {
      footerTweens.forEach(tween => tween.kill()); footerTweens = [];
      timelines.forEach(timeline => timeline.kill());
      textTargets.forEach(element => { element.dataset.flowState = 'complete'; });
    };
  });

  return {
    textCount: textTargets.length,
    refresh() { ScrollTrigger.refresh(); },
    destroy() { media.revert(); fieldVisibility.disconnect(); fieldSize.disconnect(); document.removeEventListener('visibilitychange', visibility); },
  };
}
