# Waypure content audit

The four supplied PDFs were extracted and visually checked across all 24 pages. Original files are unchanged. Product names are frequently vector outlines rather than extractable text; feature boxes and prices were matched visually to the correct column.

| Source | Website content |
| --- | --- |
| `info/brochure WAYPURE.pdf`, pp. 1–4 | Drink pure / Eat pure / Breathe pure; southern-India company introduction; water, air and ozone categories; mission and vision; full Angadipuram address and PIN; both phone numbers. |
| `info/Brochure 2023 oct prnt.pdf`, pp. 1–2 | Colour palette; company values; Angadipuram and Pulamanthole branch wording; enquiry numbers. |
| `info/PRODUCT CATALOUGE 2023 OCT copy.pdf`, pp. 1–17 | Model names, capacity, technologies, features, dimensions, historical MRP and listed warranties; services on p. 17. |
| `info/ROLLUP DESIGN 0CT 23.pdf`, p. 1 | Branding, water-purification emphasis, branch names and both numbers. |
| `assets/img/logo/logo.png` | Exact supplied logo in header, preloader, footer, favicon and 3D product texture. |

Brand colours use logo green `#67a63f`, blue `#3985bf`, and the catalogue's green-to-teal accents with lighter background tints.

## Confirmed decisions

- Company name: **Waypure**. Existing animations preserved.
- Call: **+91 90610 91810**; WhatsApp: **+91 98953 01810**, explicitly confirmed by the user.
- Prices labelled **2023 catalogue MRP**, as requested. Product cards have no photographs.
- The hero is illustrative and does not claim to be an exact geometry model of a particular catalogue product.
- The user confirmed **Prolife Fiesta Blue** and **Prolife Viber** are separate products. The final page-7 image says Viber although its heading repeats Fiesta Blue; both are included separately.

## Editorial handling

- Rider, Terron and Q-Tone repeat on pp. 9 and 12 with matching capacities/prices. Each appears once, with both pages recorded.
- Aqua Nine listings on pp. 13–14 have different features. Both remain as Zinc and Copper & Zinc variants, without invented manufacturer model numbers.
- Page 8 text-extraction order differs from the visible columns: **Aqua Pure ₹14,499**, **Kairox ₹17,899**. The website uses the visible columns.
- Repeated UF tokens in Merlin lists were de-duplicated. UV was not inferred where absent from the feature text.
- The ambiguous “MTYDS” abbreviation on p. 14 was omitted instead of silently changed to another specification.
- Chrome feature text states 7–8 L, whereas its capacity summary says 8 L; cards preserve the explicit **7–8 L** range.
- Dimensions retain W × D × H order. No unit is printed in the catalogue, so units are left for confirmation rather than assumed.
- A 36-month warranty is attached only to relevant Waypure models. Selected Aquaguard lifetime warranties are explicitly limited to the stainless steel tank. These dated descriptions are not blanket company warranties.
- Empty prices or unspecified capacity/details are not filled with invented numbers.
- Obvious prose spelling/grammar was corrected. The old “last 5 years” wording was not presented as a current company age.
- Broad medical claims, disease percentages and health-cure wording were not carried into the website. Generic certification graphics were not converted into verified company-wide credentials.
- No opening hours, email address, current discounts, installation promises or warranty conditions were invented.

Per-model source pages and data are recorded in `data/products.js`. `npm run build:catalogue` generates the image-free cards and corresponding form options from those records.

The home page features six models; `products.html` contains the complete 56-model collection. `about.html` expands the sourced company narrative and `contact.html` uses the confirmed contact destinations and brochure addresses. The reference-inspired human-water illustration is conceptual; its decorative fill level does not represent body-water percentages. Its quality message describes the company's stated focus on suitable solutions, service and continuous improvement.
