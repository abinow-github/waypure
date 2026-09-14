export function createCatalogue({ gsap, reduceMotion, refresh, onEnquire }) {
  const section = document.querySelector('#details');
  if (!section) return;
  section.addEventListener('click',event=>{
    const button = event.target.closest('[data-product]');
    if (button) onEnquire(button.dataset.product);
  });
  if (!section.querySelector('#product-search')) return;
  const cards = [...section.querySelectorAll('.catalogue-card')];
  const filters = [...section.querySelectorAll('[data-brand-filter]')];
  const search = section.querySelector('#product-search');
  const category = section.querySelector('#product-category');
  const more = section.querySelector('#catalogue-more');
  const status = section.querySelector('[data-catalogue-count]');
  const empty = section.querySelector('.catalogue-empty');
  let brand = 'All', limit = Infinity, timer;
  function update(animate = true) {
    const normalize = text => text.toLowerCase().replace(/[^a-z0-9]+/g,' ').trim();
    const query = normalize(search.value);
    const matches = cards.filter(card => (brand === 'All' || card.dataset.brand === brand) && (category.value === 'All' || card.dataset.category === category.value) && normalize(card.dataset.search).includes(query));
    const visible = matches.slice(0,limit);
    const newlyVisible = visible.filter(card=>card.hidden);
    cards.forEach(card => card.hidden = !visible.includes(card));
    status.textContent = `Showing ${visible.length} of ${matches.length} products`;
    empty.hidden = matches.length > 0;
    if (more) more.hidden = matches.length <= limit;
    if (animate && !reduceMotion.matches && newlyVisible.length) gsap.fromTo(newlyVisible,{opacity:0,y:18},{opacity:1,y:0,duration:.55,stagger:.045,ease:'power2.out',clearProps:'opacity,transform'});
    requestAnimationFrame(refresh);
    return newlyVisible;
  }
  filters.forEach(button => button.addEventListener('click',()=>{
    brand = button.dataset.brandFilter;
    filters.forEach(filter => filter.setAttribute('aria-pressed',String(filter === button)));
    update();
  }));
  search.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>update(),120);});
  category.addEventListener('change',()=>update());
  update(false);
}
