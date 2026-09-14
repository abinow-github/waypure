// The preview and font can paint before the 3D module finishes initializing.
// No artificial minimum delay and no dependency on WebGL or the main app.
(() => {
  const finish = () => {
    document.documentElement.classList.remove('booting');
    document.documentElement.classList.add('boot-ready');
  };
  const preview = document.querySelector('.product-fallback');
  Promise.allSettled([document.fonts.ready, preview?.decode()]).then(finish);
  window.addEventListener('pageshow', event => { if (event.persisted) finish(); });
})();
