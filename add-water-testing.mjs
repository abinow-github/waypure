import { readFileSync, writeFileSync } from 'fs';

let html = readFileSync('index.html', 'utf8');

const waterTestingSection = `
    <!-- Water Testing Plans -->
    <section class="wt-section section-space" id="water-testing" aria-labelledby="wt-title">
      <div class="wt-inner">
        <div class="wt-header reveal">
          <span class="eyebrow"><span class="live-dot"></span> WAYPURE LAB SERVICES</span>
          <h2 id="wt-title">Know your water.<br><em>Before you purify it.</em></h2>
          <p>Our certified lab analyses your water sample and delivers a comprehensive report — helping you choose the exact purifier your home needs.</p>
        </div>
        <div class="wt-plans">
          <!-- Standard Plan -->
          <div class="wt-card reveal" data-plan="standard">
            <div class="wt-card-top">
              <div class="wt-plan-icon" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.3"/>
                  <path d="M24 8C18 16 12 20 12 28a12 12 0 0 0 24 0C36 20 30 16 24 8Z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                  <path d="M18 30c0 4 3 6 6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="wt-plan-label">
                <span class="wt-badge">Standard</span>
                <div class="wt-price">
                  <strong>₹900</strong>
                  <span>per sample</span>
                </div>
              </div>
            </div>
            <p class="wt-desc">Essential water quality check covering all key parameters for safe daily drinking use.</p>
            <ul class="wt-params" aria-label="Standard plan parameters">
              <li><span class="wt-check">✓</span> pH Level</li>
              <li><span class="wt-check">✓</span> TDS (Total Dissolved Solids)</li>
              <li><span class="wt-check">✓</span> Turbidity</li>
              <li><span class="wt-check">✓</span> Total Hardness</li>
              <li><span class="wt-check">✓</span> Calcium</li>
              <li><span class="wt-check">✓</span> Magnesium</li>
              <li><span class="wt-check">✓</span> Total Alkalinity</li>
              <li><span class="wt-check">✓</span> Chloride</li>
              <li><span class="wt-check">✓</span> Sulphate</li>
              <li><span class="wt-check">✓</span> Nitrate</li>
              <li><span class="wt-check">✓</span> Iron</li>
              <li><span class="wt-check">✓</span> Total Coliform</li>
            </ul>
            <div class="wt-param-count">12 Parameters</div>
            <a href="tel:+919061091810" class="wt-cta-btn">Book Standard Test <span>↗</span></a>
          </div>

          <!-- Pro Plan -->
          <div class="wt-card wt-card--pro reveal" data-plan="pro" style="transition-delay:0.1s;">
            <div class="wt-popular-badge" aria-label="Recommended">Recommended</div>
            <div class="wt-card-top">
              <div class="wt-plan-icon" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.3"/>
                  <path d="M24 8C18 16 12 20 12 28a12 12 0 0 0 24 0C36 20 30 16 24 8Z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                  <circle cx="24" cy="18" r="3" stroke="currentColor" stroke-width="1.2" fill="none"/>
                  <path d="M18 30c0 4 3 6 6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  <path d="M30 14l2-3M18 14l-2-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="wt-plan-label">
                <span class="wt-badge wt-badge--pro">Pro</span>
                <div class="wt-price">
                  <strong>₹1,200</strong>
                  <span>per sample</span>
                </div>
              </div>
            </div>
            <p class="wt-desc">Advanced comprehensive analysis including heavy metals, biological contaminants and industrial parameters.</p>
            <ul class="wt-params" aria-label="Pro plan parameters">
              <li><span class="wt-check">✓</span> pH Level</li>
              <li><span class="wt-check">✓</span> TDS (Total Dissolved Solids)</li>
              <li><span class="wt-check">✓</span> Turbidity</li>
              <li><span class="wt-check">✓</span> Total Hardness</li>
              <li><span class="wt-check">✓</span> Calcium</li>
              <li><span class="wt-check">✓</span> Magnesium</li>
              <li><span class="wt-check">✓</span> Total Alkalinity</li>
              <li><span class="wt-check">✓</span> Chloride</li>
              <li><span class="wt-check">✓</span> Sulphate</li>
              <li><span class="wt-check">✓</span> Nitrate</li>
              <li><span class="wt-check">✓</span> Iron</li>
              <li><span class="wt-check">✓</span> Total Coliform</li>
              <li><span class="wt-check wt-check--pro">✓</span> Fluoride</li>
              <li><span class="wt-check wt-check--pro">✓</span> Arsenic</li>
              <li><span class="wt-check wt-check--pro">✓</span> Lead (Pb)</li>
              <li><span class="wt-check wt-check--pro">✓</span> Copper</li>
              <li><span class="wt-check wt-check--pro">✓</span> Zinc</li>
              <li><span class="wt-check wt-check--pro">✓</span> Manganese</li>
              <li><span class="wt-check wt-check--pro">✓</span> E. Coli (Bacteria)</li>
              <li><span class="wt-check wt-check--pro">✓</span> BOD (Biological Oxygen Demand)</li>
              <li><span class="wt-check wt-check--pro">✓</span> COD (Chemical Oxygen Demand)</li>
              <li><span class="wt-check wt-check--pro">✓</span> Dissolved Oxygen</li>
              <li><span class="wt-check wt-check--pro">✓</span> Ammonia</li>
              <li><span class="wt-check wt-check--pro">✓</span> Phosphate</li>
              <li><span class="wt-check wt-check--pro">✓</span> Residual Chlorine</li>
              <li><span class="wt-check wt-check--pro">✓</span> TDS at 180°C</li>
            </ul>
            <div class="wt-param-count wt-param-count--pro">26 Parameters</div>
            <a href="tel:+919061091810" class="wt-cta-btn wt-cta-btn--pro">Book Pro Test <span>↗</span></a>
          </div>
        </div>
        <p class="wt-note">Results delivered within 3–5 working days. Sample collection available at our lab or by arrangement. Call us for details.</p>
      </div>
    </section>
`;

// Insert before the FAQ section
html = html.replace('    <section class="faq-section section-space" id="questions">', waterTestingSection + '    <section class="faq-section section-space" id="questions">');

writeFileSync('index.html', html);
console.log('Water testing section added to index.html');
