import { contactConfig } from './contact-config.js';

export function createInterface({ gsap, ScrollTrigger, reduceMotion, dialog, openEnquiry }) {
  let lenis = null;
  const sync = () => ScrollTrigger.update();
  const tick = time => lenis?.raf(time * 1000);
  function configureScroll() {
    gsap.ticker.remove(tick);
    lenis?.destroy(); lenis = null;
    if (!reduceMotion.matches && window.Lenis) {
      lenis = new window.Lenis({
        lerp: .095, smoothWheel: true, syncTouch: false, anchors: false,
        prevent: node => node.closest?.('dialog, [data-lenis-prevent]'),
      });
      lenis.on('scroll', sync);
      gsap.ticker.add(tick);
      if (dialog.open) lenis.stop();
    }
    document.documentElement.dataset.smoothScroll = lenis ? 'enabled' : 'native';
  }
  configureScroll();
  reduceMotion.addEventListener('change', configureScroll);
  const scrollTo = (target, options = {}) => {
    if (lenis) lenis.scrollTo(target, { duration: 1.25, ...options });
    else if (typeof target === 'number') window.scrollTo({ top: target, behavior: reduceMotion.matches ? 'auto' : 'smooth' });
    else target.scrollIntoView({ behavior: reduceMotion.matches ? 'auto' : 'smooth' });
  };
  document.querySelectorAll('a[href^="#"]:not([data-chapter]):not([data-contact]):not(.skip-link)').forEach(link => {
    link.addEventListener('click', event => {
      const target = document.querySelector(link.getAttribute('href'));
      if (!target || event.ctrlKey || event.metaKey || event.shiftKey) return;
      event.preventDefault();
      // Lenis already applies the document's CSS scroll-padding-top.
      scrollTo(target);
    });
  });
  const number = value => /^\+?[1-9]\d{7,14}$/.test(value.replace(/[\s()-]/g, '')) ? value.replace(/\D/g, '') : '';
  document.querySelectorAll('[data-contact]').forEach(link => {
    const whatsapp = link.dataset.contact === 'whatsapp';
    const digits = number(whatsapp ? contactConfig.whatsapp : contactConfig.phone);
    if (digits) {
      link.href = whatsapp ? `https://wa.me/${digits}?text=${encodeURIComponent(contactConfig.message)}` : `tel:+${digits}`;
      if (whatsapp) { link.target = '_blank'; link.rel = 'noopener noreferrer'; }
    } else {
      link.setAttribute('aria-haspopup', 'dialog');
      link.addEventListener('click', event => { event.preventDefault(); openEnquiry(); });
    }
  });
  dialog.addEventListener('close', () => lenis?.start());
  return { scrollTo, stop: () => lenis?.stop() };
}
