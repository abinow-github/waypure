# Waypure

A responsive Waypure website with a Three.js water-filling/pouring story, GSAP and AOS text reveals, Lenis smooth scrolling, animated bubbles, liquid button hovers and a two-layer wave footer. Company and product copy comes from the four supplied PDFs in `info/`. The supplied logo is used throughout.

## Preview

With XAMPP Apache running, open **http://localhost/water-purifying/**. No build or npm installation is needed to view the site; runtime libraries and assets are local. Alternatively, run `npm start` and open **http://localhost:3000**.

## Content and contact

- 56 distinct catalogue entries across Waypure, Aquaguard, Merlin, Prolife and Chrome. Product cards intentionally have no images. The animated hero remains an illustrative water journey, rather than a model specification diagram.
- Product prices are labelled **2023 catalogue MRP**. Current availability, selling price and warranty terms are confirmed on enquiry. Unlisted prices are shown as “On enquiry”.
- Call: **+91 90610 91810**. WhatsApp: **+91 98953 01810**, confirmed by the user.
- Branch details, company introduction, mission, vision and services come from the supplied brochures. See [CONTENT-SOURCES.md](CONTENT-SOURCES.md) for the source audit.
- Enquiry buttons select the relevant product. The form prepares a downloadable text enquiry and a WhatsApp link for the visitor to review and send. It does not send messages automatically or store customer details on a server.

## Editing

- `index.html`: 3D home experience, animated human water illustration, company summary and six featured products. “Show more products” opens `products.html`.
- `about.html`: company introduction, mission, vision and services.
- `products.html`: all 56 models, brand/category filters, search and product enquiries.
- `contact.html`: call/WhatsApp links, enquiry entry point and both branch addresses/map searches.
- `pages.css`, `water-life.js`: responsive inner pages, green prices and scroll-linked silhouette fill with flowing water surfaces. The illustration is conceptual, without medical percentages.
- `data/products.js`: verified models, specifications, historical MRPs and source pages. Run `npm run build:catalogue` after editing to regenerate cards and form options.
- `catalogue.js`: search, brand/category filtering and product enquiries. All matching products remain visible on the Products page.
- `contact-config.js`: confirmed contact destinations. Keep native links across the four HTML pages in sync if these change. Shared navigation, footer and enquiry markup are present in each static page.
- `assets/img/logo/logo.png`: original supplied logo; responsive dimensions are in `waypure.css`.
- `waypure.css`: logo layout, brochure colours, catalogue, services and branch information.
- `styles.css`, `water-theme.css`, `fresh-water.css`: base layout, water theme, responsive story, liquid hovers and preloader.
- `app.js`, `interface.js`, `water-effects.js`, `water-effects.css`: scroll/story synchronization, contact interface, accessible letter reveals, bubbles and footer motion.
- `scene.js`: procedural 3D purifier and water sequence with the supplied logo texture. Regenerate its static hero preview with `node tests/render-product.mjs` while the local server runs (`TEST_URL` can select XAMPP).

## Performance and accessibility

Three.js is loaded only on the home page, minified to 681 KB and served as a 174 KB gzip response on XAMPP. The hero fallback uses WebP. Inner pages use lightweight SVG water art. The logo is reused from one URL. Source PDFs load only when a visitor follows the catalogue link; PDF extraction dependencies are development tools, not website downloads.

Run `npm run build:vendor` after updating Three.js or Lenis to regenerate local files and gzip companions together. `.htaccess` negotiates compressed vendor files and revalidates code/images. The optional Node server serves the minified JavaScript without gzip.

The preloader has no minimum delay and a 1.6-second fail-safe. Reduced motion keeps text readable and uses native scrolling; touch scrolling stays native. Bubbles, footer waves and hero ambient motion pause offscreen. Screen readers receive one semantic copy of animated headings. The enquiry dialog locks background scrolling, and the catalogue remains readable without JavaScript.

Floating call/WhatsApp droplets have no tooltips. Their water surfaces move continuously, fill on hover or keyboard focus, and return to a partly filled state afterward. Touch devices avoid sticky hover and use 16px form controls to prevent focus zoom.

The 3D scene batches condensation, bubbles and splashes into instanced draws while preserving their materials, geometry and animation. Shaders compile before the glass and stream enter view. The scroll indicator uses a transform; hero geometry is cached, resize/refresh requests are coalesced, and unchanged progress attributes are left alone. The 3D story follows Lenis directly instead of adding a second smoothing delay. Offscreen ticker/seal motion pauses, and only visible bubble fields retain compositor hints.

## Verification

Install development dependencies with `npm ci`, start the server, and run `npm test`. For XAMPP, set `TEST_URL` to `http://localhost/water-purifying/`. Tests use installed Google Chrome through Playwright.

Checks cover actual 3D filling/pouring/reverse scrolling, AOS letter reveals, bubbles/waves, wheel interpolation, contact links, enquiries/downloads, the 56-entry catalogue and source-verified prices, filters/search, six home cards and navigation to the collection, all inner pages, animated body-water fill, no eager PDFs or inner-page Three.js, reduced motion, static/no-JavaScript fallbacks and 12 viewport sizes from 320 to 1920px. Screenshots go to `test-results/`.

`scripts/read-brochures.mjs` extracts text and renders PDF pages into `test-results/brochures/` for visual verification. It does not change the PDFs.

`node tests/scroll-profile.mjs <label>` records a six-second browser scroll profile in `test-results/scroll-<label>.json`, including draw calls, frame timing and layout/style work. Frame timing depends on the browser, GPU and system load; compare profiles on the same device.

Runtime license notices: `vendor/THREE-LICENSE.txt`, `vendor/AOS-LICENSE.txt`, `vendor/LENIS-LICENSE.txt`, `assets/MANROPE-LICENSE.txt`. GSAP/ScrollTrigger retain their license headers. Lenis integration follows its [official documentation](https://github.com/darkroomengineering/lenis).
