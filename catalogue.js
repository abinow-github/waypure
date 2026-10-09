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
  const status = section.querySelector('[data-catalogue-count]');
  const empty = section.querySelector('.catalogue-empty');
  const normalize = text => text.toLowerCase().replace(/[^a-z0-9]+/g,' ').trim();
  const searchable = new Map(cards.map(card=>[card,normalize(card.dataset.search)]));
  let brand = 'All', timer;
  function update(animate = true) {
    const query = normalize(search.value);
    const visible = cards.filter(card => (brand === 'All' || card.dataset.brand === brand) && (category.value === 'All' || card.dataset.category === category.value) && searchable.get(card).includes(query));
    const visibleSet = new Set(visible);
    const newlyVisible = visible.filter(card=>card.hidden);
    cards.forEach(card => { const hidden=!visibleSet.has(card); if(card.hidden!==hidden)card.hidden=hidden; });
    status.textContent = `Showing ${visible.length} of ${visible.length} products`;
    empty.hidden = visible.length > 0;
    gsap.killTweensOf(cards);gsap.set(cards,{clearProps:'opacity,transform'});
    if (animate && !reduceMotion.matches && newlyVisible.length) gsap.fromTo(newlyVisible,{opacity:0,y:18},{opacity:1,y:0,duration:.55,stagger:{amount:.3},ease:'power2.out',clearProps:'opacity,transform'});
    
    // Refresh ScrollTriggers after layout changes
    requestAnimationFrame(() => {
      refresh();
      window.ScrollTrigger.refresh();
    });
    return newlyVisible;
  }
  
  // Create glaze triggers for cards
  cards.forEach(card => {
    window.ScrollTrigger.create({
      trigger: card,
      start: 'top 85%',
      onEnter: () => card.classList.add('glaze-active'),
      onLeaveBack: () => card.classList.remove('glaze-active')
    });
  });

  filters.forEach(button => button.addEventListener('click',()=>{
    brand = button.dataset.brandFilter;
    filters.forEach(filter => filter.setAttribute('aria-pressed',String(filter === button)));
    update();
  }));
  search.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>update(),120);});
  category.addEventListener('change',()=>update());
  section.addEventListener('toggle',event=>{if(event.target.matches('.card-details'))refresh();},true);
  update(false);
}
