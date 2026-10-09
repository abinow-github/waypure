import { readFileSync, writeFileSync } from 'fs';

let html = readFileSync('index.html', 'utf8');

// --- GOOGLE REVIEWS SLIDER ---
const reviewsSection = `
    <!-- Google Reviews Slider -->
    <section class="reviews-section section-space" id="reviews" aria-label="Customer reviews">
      <div class="reviews-inner">
        <div class="reviews-header reveal">
          <span class="eyebrow"><span class="live-dot"></span> CUSTOMER REVIEWS</span>
          <h2>What our customers<br><em>say about us.</em></h2>
          <div class="reviews-rating-summary">
            <div class="reviews-stars-big" aria-label="4.8 out of 5 stars">
              <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
            </div>
            <div class="reviews-rating-text">
              <strong>4.8</strong>
              <span>/ 5 · Google Reviews</span>
            </div>
          </div>
        </div>

        <div class="reviews-slider-wrap" aria-label="Customer review carousel">
          <div class="reviews-track" id="reviews-track">
            <!-- Review 1 -->
            <article class="review-card">
              <div class="review-top">
                <div class="review-avatar" aria-hidden="true">A</div>
                <div class="review-meta">
                  <strong class="review-name">Arun Kumar</strong>
                  <span class="review-date">3 months ago</span>
                </div>
                <div class="review-google-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                </div>
              </div>
              <div class="review-stars" aria-label="5 stars">★★★★★</div>
              <p class="review-text">"Excellent service! Waypure team helped us choose the perfect purifier for our home. The water quality improved drastically. Highly recommended!"</p>
            </article>

            <!-- Review 2 -->
            <article class="review-card">
              <div class="review-top">
                <div class="review-avatar" aria-hidden="true" style="background:linear-gradient(135deg,#6ee8c8,#1994e9);">S</div>
                <div class="review-meta">
                  <strong class="review-name">Sreevidya M.</strong>
                  <span class="review-date">5 months ago</span>
                </div>
                <div class="review-google-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                </div>
              </div>
              <div class="review-stars">★★★★★</div>
              <p class="review-text">"Best water purifier showroom in Malappuram. Got my Q-Tone installed and the difference is amazing. Professional staff and fast installation."</p>
            </article>

            <!-- Review 3 -->
            <article class="review-card">
              <div class="review-top">
                <div class="review-avatar" aria-hidden="true" style="background:linear-gradient(135deg,#ee785e,#c55a3a);">R</div>
                <div class="review-meta">
                  <strong class="review-name">Rajesh Nair</strong>
                  <span class="review-date">1 month ago</span>
                </div>
                <div class="review-google-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                </div>
              </div>
              <div class="review-stars">★★★★★</div>
              <p class="review-text">"Bought the Terron model. Exceptional build quality and the minerals added back to water is something you can taste. Great after-sales support too."</p>
            </article>

            <!-- Review 4 -->
            <article class="review-card">
              <div class="review-top">
                <div class="review-avatar" aria-hidden="true" style="background:linear-gradient(135deg,#a78bfa,#7c3aed);">F</div>
                <div class="review-meta">
                  <strong class="review-name">Fathima Beevi</strong>
                  <span class="review-date">2 months ago</span>
                </div>
                <div class="review-google-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                </div>
              </div>
              <div class="review-stars">★★★★★</div>
              <p class="review-text">"Water testing service is amazing. They identified iron contamination in our water and recommended the right purifier. Very knowledgeable team."</p>
            </article>

            <!-- Review 5 -->
            <article class="review-card">
              <div class="review-top">
                <div class="review-avatar" aria-hidden="true" style="background:linear-gradient(135deg,#34d399,#059669);">M</div>
                <div class="review-meta">
                  <strong class="review-name">Mohammed Ashraf</strong>
                  <span class="review-date">4 months ago</span>
                </div>
                <div class="review-google-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                </div>
              </div>
              <div class="review-stars">★★★★★</div>
              <p class="review-text">"Got the commercial RO installed for our restaurant. Excellent quality water, no complaints. Installation was clean and fast. Very happy with Waypure."</p>
            </article>

            <!-- Review 6 -->
            <article class="review-card">
              <div class="review-top">
                <div class="review-avatar" aria-hidden="true" style="background:linear-gradient(135deg,#fbbf24,#d97706);">P</div>
                <div class="review-meta">
                  <strong class="review-name">Priya Suresh</strong>
                  <span class="review-date">6 months ago</span>
                </div>
                <div class="review-google-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                </div>
              </div>
              <div class="review-stars">★★★★★</div>
              <p class="review-text">"The Waypure Bravo has been excellent for 6 months now. Pure water, great zinc and copper minerals added back. The team is always responsive for any queries."</p>
            </article>
          </div>

          <!-- Controls -->
          <div class="reviews-controls">
            <button class="reviews-btn" id="rev-prev" aria-label="Previous reviews">‹</button>
            <div class="reviews-dots" id="reviews-dots" role="tablist" aria-label="Review pages"></div>
            <button class="reviews-btn" id="rev-next" aria-label="Next reviews">›</button>
          </div>
        </div>

        <div class="reviews-google-link">
          <a href="https://maps.google.com/search/Waypure+Angadipuram" target="_blank" rel="noopener" class="pill-button">
            Read more on Google
            <span>↗</span>
          </a>
        </div>
      </div>
    </section>
`;

// --- WAYPURE PRODUCTS SHOWCASE SECTION ---
const waypureProductsSection = `
    <!-- Waypure Brand Showcase -->
    <section class="wp-showcase section-space" id="waypure-products" aria-labelledby="wp-showcase-title">
      <div class="wp-showcase-inner">
        <div class="wp-showcase-header reveal">
          <span class="eyebrow"><span class="live-dot"></span> OUR OWN BRAND</span>
          <h2 id="wp-showcase-title">Made by Waypure.<br><em>Built for India.</em></h2>
          <p>Designed and engineered exclusively by us — every Waypure purifier is built for Indian water conditions, Indian families and lasting purity.</p>
        </div>

        <div class="wp-showcase-tabs" role="tablist" aria-label="Product categories">
          <button class="wp-tab wp-tab--active" role="tab" aria-selected="true" data-wptab="home" id="tab-home">Home Purifiers</button>
          <button class="wp-tab" role="tab" aria-selected="false" data-wptab="premium" id="tab-premium">Premium Range</button>
          <button class="wp-tab" role="tab" aria-selected="false" data-wptab="commercial" id="tab-commercial">Commercial</button>
          <button class="wp-tab" role="tab" aria-selected="false" data-wptab="special" id="tab-special">Specialty</button>
        </div>

        <!-- Home Purifiers -->
        <div class="wp-panel wp-panel--active" role="tabpanel" aria-labelledby="tab-home" data-wppanel="home">
          <div class="wp-product-grid">
            <div class="wp-product-card reveal">
              <div class="wp-product-img"><img src="assets/img/products/mars.png" alt="Waypure Mars" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Mars</h3>
                <p>RO + UV + Alkaline minerals</p>
                <strong>₹13,499</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Mars">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.05s">
              <div class="wp-product-img"><img src="assets/img/products/glance.png" alt="Waypure Glance" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Glance</h3>
                <p>RO + UV + Alkaline minerals</p>
                <strong>₹11,890</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Glance">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.1s">
              <div class="wp-product-img"><img src="assets/img/products/klickpro.png" alt="Waypure Klickpro" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Klickpro</h3>
                <p>RO + UV + Alkaline minerals</p>
                <strong>₹13,999</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Klickpro">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.15s">
              <div class="wp-product-img"><img src="assets/img/products/aqua-pure.png" alt="Waypure Aqua Pure" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Aqua Pure</h3>
                <p>RO + UV + Alkaline minerals</p>
                <strong>₹14,499</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Aqua Pure">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.2s">
              <div class="wp-product-img"><img src="assets/img/products/xzora.png" alt="Waypure Xzora" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Xzora</h3>
                <p>RO + UV + Alkaline minerals</p>
                <strong>₹15,499</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Xzora">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.25s">
              <div class="wp-product-img"><img src="assets/img/products/epic.png" alt="Waypure Epic" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Epic</h3>
                <p>RO + UV + Alkaline + UF + MTDS</p>
                <strong>₹16,899</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Epic">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.3s">
              <div class="wp-product-img"><img src="assets/img/products/xpure.png" alt="Waypure Xpure" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Xpure</h3>
                <p>RO + UV + Alkaline minerals</p>
                <strong>₹15,499</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Xpure">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.35s">
              <div class="wp-product-img"><img src="assets/img/products/water-lily.png" alt="Waypure Water Lily" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Water Lily</h3>
                <p>RO + UV + Alkaline minerals</p>
                <strong>₹14,499</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Water Lily">Enquire</button>
            </div>
          </div>
        </div>

        <!-- Premium Range -->
        <div class="wp-panel" role="tabpanel" aria-labelledby="tab-premium" data-wppanel="premium" hidden>
          <div class="wp-product-grid">
            <div class="wp-product-card reveal">
              <div class="wp-product-img"><img src="assets/img/products/q-tone.png" alt="Waypure Q-Tone" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Q-Tone</h3>
                <p>RO + UV + Minerals + Zinc + Copper</p>
                <strong>₹19,990</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Q-Tone">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.05s">
              <div class="wp-product-img"><img src="assets/img/products/bravo.png" alt="Waypure Bravo" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Bravo</h3>
                <p>RO + UV + Minerals + Zinc + Copper</p>
                <strong>₹16,990</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Bravo">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.1s">
              <div class="wp-product-img"><img src="assets/img/products/onix.png" alt="Waypure Onix" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Onix</h3>
                <p>RO + UV + Alkaline + Zinc + UF + MTDS</p>
                <strong>₹18,990</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Onix">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.15s">
              <div class="wp-product-img"><img src="assets/img/products/kairox.png" alt="Waypure Kairox" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Kairox</h3>
                <p>RO + UV + Alkaline minerals</p>
                <strong>₹17,899</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Kairox">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.2s">
              <div class="wp-product-img"><img src="assets/img/products/rider.png" alt="Waypure Rider" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Rider</h3>
                <p>RO + UV + Minerals + Zinc + Copper</p>
                <strong>₹19,990</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Rider">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.25s">
              <div class="wp-product-img"><img src="assets/img/products/terron.png" alt="Waypure Terron" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Terron</h3>
                <p>RO + UV + Minerals + Zinc + Copper</p>
                <strong>₹23,990</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Terron">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.3s">
              <div class="wp-product-img"><img src="assets/img/products/5g-pro.png" alt="Waypure 5G Pro" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>5G Pro</h3>
                <p>RO + UV + Alkaline + Copper + UF + MTDS</p>
                <strong>₹25,000</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure 5G Pro">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.35s">
              <div class="wp-product-img"><img src="assets/img/products/utc-under-sink.png" alt="Waypure UTC Under Sink" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>UTC Under Sink</h3>
                <p>RO + UV + Alkaline minerals</p>
                <strong>₹17,999</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure UTC Under Sink">Enquire</button>
            </div>
          </div>
        </div>

        <!-- Commercial -->
        <div class="wp-panel" role="tabpanel" aria-labelledby="tab-commercial" data-wppanel="commercial" hidden>
          <div class="wp-product-grid">
            <div class="wp-product-card reveal">
              <div class="wp-product-img"><img src="assets/img/products/35-lph-commercial.png" alt="Waypure 35 LPH" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>35 LPH Commercial</h3>
                <p>RO + UV + Minerals plant · 35 LPH</p>
                <strong>₹25,000</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure 35 LPH Commercial">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.05s">
              <div class="wp-product-img"><img src="assets/img/products/aqua-clan.png" alt="Waypure Aqua Clan" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Aqua Clan</h3>
                <p>RO + UV + Alkaline minerals</p>
                <strong>₹22,500</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Aqua Clan">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.1s">
              <div class="wp-product-img"><img src="assets/img/products/water-cooler.png" alt="Waypure Water Cooler" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Water Cooler</h3>
                <p>Water cooling system</p>
                <strong>₹23,500</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Water Cooler">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.15s">
              <div class="wp-product-img"><img src="assets/img/products/plus-dispenser.png" alt="Waypure Plus Dispenser" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Plus Dispenser</h3>
                <p>Cold &amp; hot water dispensing</p>
                <strong>Price on enquiry</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Plus Dispenser">Enquire</button>
            </div>
          </div>
        </div>

        <!-- Specialty -->
        <div class="wp-panel" role="tabpanel" aria-labelledby="tab-special" data-wppanel="special" hidden>
          <div class="wp-product-grid">
            <div class="wp-product-card reveal">
              <div class="wp-product-img"><img src="assets/img/products/aqua-nine.png" alt="Waypure Aqua Nine Zinc" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Aqua Nine — Zinc</h3>
                <p>RO + UV + Alkaline + Zinc + UF + MTDS</p>
                <strong>₹18,990</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Aqua Nine — Zinc">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.05s">
              <div class="wp-product-img"><img src="assets/img/products/aqua-nine-copper.png" alt="Waypure Aqua Nine Copper" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Aqua Nine — Copper &amp; Zinc</h3>
                <p>RO + UV + Alkaline + Copper + Zinc + UF</p>
                <strong>₹18,990</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Aqua Nine — Copper & Zinc">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.1s">
              <div class="wp-product-img"><img src="assets/img/products/iron-remover.png" alt="Waypure Iron Remover" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>Iron Remover</h3>
                <p>Iron removal + Softener filter</p>
                <strong>Price on enquiry</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure Iron Remover">Enquire</button>
            </div>
            <div class="wp-product-card reveal" style="transition-delay:.15s">
              <div class="wp-product-img"><img src="assets/img/products/4-stage.png" alt="Waypure 4 Stage" loading="lazy"></div>
              <div class="wp-product-info">
                <h3>4 Stage UV</h3>
                <p>Four-stage UV purification system</p>
                <strong>₹3,999</strong>
              </div>
              <button class="wp-enquire enquire-button" data-product="Waypure 4 Stage">Enquire</button>
            </div>
          </div>
        </div>

        <div class="wp-showcase-cta">
          <a href="products.html" class="pill-button dark">Explore all 56 models <span>↗</span></a>
        </div>
      </div>

      <!-- Tab switching JS (inline, no deps) -->
      <script>
      (function() {
        var tabs = document.querySelectorAll('.wp-tab');
        var panels = document.querySelectorAll('.wp-panel');
        tabs.forEach(function(tab) {
          tab.addEventListener('click', function() {
            var key = tab.dataset.wptab;
            tabs.forEach(function(t) { t.classList.remove('wp-tab--active'); t.setAttribute('aria-selected','false'); });
            panels.forEach(function(p) { p.hidden = true; p.classList.remove('wp-panel--active'); });
            tab.classList.add('wp-tab--active');
            tab.setAttribute('aria-selected','true');
            var panel = document.querySelector('[data-wppanel="' + key + '"]');
            if (panel) { panel.hidden = false; panel.classList.add('wp-panel--active'); }
          });
        });
      })();
      </script>
    </section>
`;

// Only inject if not already present
if (!html.includes('id="reviews"')) {
  // Insert before `<section class="ritual"`
  html = html.replace('    <section class="ritual"', reviewsSection + '\n    <section class="ritual"');
}

if (!html.includes('id="waypure-products"')) {
  // Insert before `<section class="product-section catalogue-section`
  html = html.replace('    <section class="product-section catalogue-section', waypureProductsSection + '\n    <section class="product-section catalogue-section');
}

writeFileSync('index.html', html);
console.log('Successfully injected reviews and waypure showcase into index.html');
