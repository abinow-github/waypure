# Aqua Royal

A self-contained water purifier website with an original 3D product model based on the supplied reference photograph. Built with HTML, CSS, JavaScript, Three.js, GSAP, ScrollTrigger, AOS, and Lenis.

## Open locally

With XAMPP Apache running, visit **http://localhost/water-purifying/**. No build step or npm installation is needed to view the website: the rendering library, font, and product illustrations are included locally.

Alternatively, run `npm start` and visit **http://localhost:3000**.

## Experience

- Scroll through four stages: introduction, tank filling, tap pouring, and a filled glass. Scrolling backwards reverses the sequence.
- A larger product presentation, cool blue and white materials, animated water caustics, and a navy/white pouring stage keep the purifier in focus. GSAP controls the product rotation, background transitions, and section entrances; ScrollTrigger controls section reveals and parallax.
- Headings and supporting copy reveal through a moving wave edge with individually staggered letters. AOS triggers section reveals; the pinned story uses a reversible GSAP letter timeline. Background bubbles rise throughout the page, and two moving water layers form the footer. Bubbles and footer motion pause offscreen and when the tab is hidden; reduced-motion mode keeps text readable and the water surface still.
- Soft mint-green accents and moving water currents complement the navy/blue banner. Buttons have liquid sweep hovers, and fixed water-drop call/WhatsApp controls connect to **+91 99467 31750** with a rising-water hover.
- Lenis smooths wheel input and anchor navigation on the GSAP clock; touch scrolling stays native. The enquiry dialog locks background scrolling, and reduced motion switches to native scrolling. Integration follows the [official Lenis documentation](https://github.com/darkroomengineering/lenis).
- A small drop preloader dismisses as soon as the preview/font are ready, with a 1.6-second fail-safe and no artificial minimum delay. It never blocks input and is hidden with reduced motion or without JavaScript.
- The model has a rounded rectangular ivory cabinet, a thin lid, a transparent blue tank with surface droplets and brand decals, a water surface, bubbles, a turning tap handle, an attached water stream, glass ripples, and splash particles.
- Stage controls and navigation work with the keyboard. Mobile layouts, reduced motion, and a static WebGL fallback are included.
- Product details, native FAQ accordions, and a product enquiry dialog complete the page.
- “Make it yours” prepares a downloadable enquiry locally. It does not send email, store customer details, or claim to submit a request to a retailer. The floating call/WhatsApp buttons use the supplied retailer number; connect a backend if the form should submit enquiries directly.

## Customize

- `index.html`: branding, product content, FAQ, and enquiry fields.
- `styles.css`: colors, typography, layouts, and section animations.
- `water-theme.css`: the navy/white water theme, larger product layout, and responsive presentation.
- `fresh-water.css`: mint accents, banner currents, liquid button hovers, contact droplets, and preloader.
- `interface.js` / `contact-config.js`: smooth scrolling and retailer contact destinations.
- `water-effects.js` / `water-effects.css`: accessible liquid text reveals, rising bubbles, and the animated footer surface.
- `scene.js`: model geometry, lighting, and the scroll-driven water sequence.
- `app.js`: scroll transitions, navigation, accessibility, and enquiry download.
- `assets/purifier-render.webp`: exported from the live model for the product portrait and static fallback. Regenerate with `node tests/render-product.mjs` while the local server is running. The original PNG/SVG illustrations are retained as source assets.

The supplied photo was used as a visual reference; the model is a custom approximation, not a manufacturer CAD model. Pricing, capacity, certifications, filtration performance, and warranty were not supplied, so the site does not invent them. The reference URL `https://www.palmo.co.in/` could not be reached during development.

## Loading performance

The complete Three.js library is minified to 681 KB and served as a 174 KB gzip response on XAMPP. The transparent WebP product preview is 79 KB, down from 308 KB PNG. Fonts are preloaded, the lower product image loads lazily, and the scene module is preloaded. Visible animations retain their geometry and timing; ambient hero motion and rendering pause offscreen.

Run `npm run build:vendor` after updating Three.js or Lenis to regenerate their local files and gzip companions together. `.htaccess` negotiates compressed vendor responses and revalidates code/images so edits appear immediately. The optional Node development server serves the same minified JavaScript without gzip.

## Verification

Install development dependencies using `npm ci`, start the local server, then run `npm test`. To test XAMPP instead, set `TEST_URL` to `http://localhost/water-purifying/`. The Playwright checks use installed Google Chrome and verify actual 3D rendering, scroll state changes, reverse scrolling, mobile navigation, enquiry downloads, FAQ behavior, text reveal completion, moving bubbles, footer waves, offscreen pausing, and reduced motion. The water-effects check uses the static product fallback to test DOM animation independently of the WebGL renderer. Screenshots are written to `test-results/`.

The responsive matrix verifies hero and pouring stages at 12 phone, tablet, desktop, and landscape sizes, from 320px to 1920px wide.

The interface check verifies contact destinations without placing calls or sending messages, actual liquid hover changes, wheel interpolation, modal scroll lock, contact positioning, preloader fail-safe behavior, reduced motion, and asset sizes.

AOS, Lenis, Three.js, and Manrope license notices are included in `vendor/AOS-LICENSE.txt`, `vendor/LENIS-LICENSE.txt`, `vendor/THREE-LICENSE.txt`, and `assets/MANROPE-LICENSE.txt`. The vendored GSAP and ScrollTrigger files retain their copyright and license headers.
