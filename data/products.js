// Verified against rendered pages of PRODUCT CATALOUGE 2023 OCT copy.pdf.
// Pages are 1-based. Prices are historical catalogue MRPs, not current offers.
const A = 'RO + UV + Alkaline minerals';
const C = 'RO + UV + Minerals + Zinc + Copper alkaline';
const copper = 'Active copper with zinc booster';
const guard = 'Mineral guard technology';
const led = 'LED indicators';
function product(id, brand, name, page, capacity, technology, mrp, features = [], extra = {}) {
  return { id, brand, name, pages: [page], capacity, technology, mrp, features, category: 'Water purifiers', ...extra };
}
const wp = (id, name, page, capacity, technology, mrp, features = [], extra = {}) => product(id, 'Waypure', name, page, capacity, technology, mrp, features, { warranty: '36 months', ...extra });
export const products = [
  wp('waypure-q-tone','Q-Tone',9,'15 L','RO + UV + Minerals + Zinc + Copper minerals',19990,['10-stage purification'],{pages:[9,12]}),
  wp('waypure-mars','Mars',11,'10 L',A,13499,[led]),
  wp('waypure-bravo','Bravo',8,'10 L',C,16990),
  wp('waypure-onix','Onix',10,'10 L',`${A} + Zinc + UF + MTDS`,18990),
  wp('waypure-glance','Glance',13,'10 L',A,11890),
  wp('waypure-epic','Epic',11,'12 L',`${A} + UF + MTDS`,16899),
  wp('waypure-aqua-pure','Aqua Pure',8,'10 L',A,14499,[led]),
  wp('waypure-klickpro','Klickpro',8,'9 L',A,13999,[led]),
  wp('waypure-kairox','Kairox',8,'10 L',A,17899,[led]),
  wp('waypure-xzora','Xzora',9,'10 L',A,15499,[led]),
  wp('waypure-rider','Rider',9,'10 L',C,19990,[led],{pages:[9,12]}),
  wp('waypure-terron','Terron',9,'15 L',C,23990,['10-stage purification'],{pages:[9,12]}),
  wp('waypure-xpure','Xpure',10,'10 L',A,15499,[led]),
  wp('waypure-water-lily','Water Lily',10,'10 L',A,14499,[led]),
  wp('waypure-5g-pro','5G Pro',10,null,`${A} + Copper minerals + UF + MTDS`,25000,['Cold, hot and normal water','Ozone detoxifier'],{category:'Dispensers & ozone'}),
  product('waypure-commercial-35','Waypure','35 LPH Commercial',11,null,'RO + UV + Minerals plant',25000,['35 LPH commercial system'],{category:'Commercial & treatment'}),
  product('waypure-plus-dispenser','Waypure','Plus Dispenser',11,null,'Cold & hot water',null,['Hot and cold dispensing'],{category:'Dispensers & ozone'}),
  wp('waypure-jade','Jade',12,'11 L',A,14499,[led]),
  wp('waypure-waternet','Waternet',13,'8 L',A,13499),
  wp('waypure-aqua-clan','Aqua Clan',13,'10 L',A,22500),
  wp('waypure-aqua-nine','Aqua Nine — Zinc',13,'10 L',`${A} + Zinc + UF + MTDS`,18990),
  // The second Aqua Nine listing has an additional copper feature (p14).
  // Its printed "MTYDS" abbreviation is not silently expanded or published.
  wp('waypure-aqua-nine-copper','Aqua Nine — Copper & Zinc',14,'10 L',`${A} + Copper + Zinc + UF`,18990),
  wp('waypure-roma','Roma',14,'10 L',`${A} + Zinc + UF + MTDS`,18990),
  wp('waypure-purosis','Purosis',14,'10 L',`${A} + UF + MTDS`,18990),
  product('waypure-water-cooler','Waypure','Water Cooler',14,null,'Water cooling',23500,[],{category:'Dispensers & ozone'}),
  wp('waypure-under-sink','UTC Under Sink',15,'10 L',A,17999,['Under-sink design']),
  product('waypure-four-stage','Waypure','4 Stage',15,null,'UV',3999,['Four-stage system']),
  product('waypure-iron-remover','Waypure','Iron Remover',15,null,'Iron removal + Softener filter',null,['Automatic valve'],{category:'Commercial & treatment'}),
  product('aquaguard-blaze-slim','Aquaguard','Blaze Slim',2,'4 L','RO + UV + TA + Hot + SS',38000,['Hot water dispensing',copper,'Stainless steel tank',guard,'Taste adjuster','Works at zero water pressure'],{dimensions:'350 × 350 × 490',tankWarranty:'Lifetime stainless steel tank warranty'}),
  product('aquaguard-eden','Aquaguard','Eden',2,'5 L','RO + UV + TA + SS',36500,[copper,'Stainless steel tank',guard,'In-built tray','Taste adjuster','DIY-replaceable cartridges'],{dimensions:'350 × 275 × 530',tankWarranty:'Lifetime stainless steel tank warranty'}),
  product('aquaguard-superio','Aquaguard','Superio',2,'5 L','RO + UV + TA + SS',28000,[copper,'Stainless steel tank',guard,'UV e-boiling','Taste adjuster','Works at zero water pressure'],{dimensions:'320 × 290 × 480',tankWarranty:'Lifetime stainless steel tank warranty'}),
  product('aquaguard-premier','Aquaguard','Premier',2,'6 L','RO + UV + TA',22000,[copper,'UV e-boiling','Taste adjuster','Works at zero water pressure'],{dimensions:'320 × 275 × 476'}),
  product('aquaguard-designo','Aquaguard','Designo UTC',3,'8 L','RO + UV + TA',23000,[copper,guard,'UV e-boiling','Taste adjuster'],{dimensions:'275 × 285 × 195'}),
  product('aquaguard-utc-booster','Aquaguard','UTC UV Booster',3,'Inline','UV',16000,[copper,guard,'UV e-boiling','Compact design'],{dimensions:'275 × 285 × 195'}),
  product('aquaguard-astor','Aquaguard','Astor',3,'6 L','RO + UV + TA + Alkaline',22490,['Alkaline Boost technology','RO membrane','UV e-boiling','Taste adjuster','Smart LED indicator'],{dimensions:'320 × 278 × 476'}),
  product('aquaguard-neo','Aquaguard','Neo',3,'6 L','RO + UV + TA + MC',23000,['Mineral charge technology','Taste adjuster','Smart LED indicator'],{dimensions:'320 × 275 × 476'}),
  product('aquaguard-cristal-booster','Aquaguard','Cristal NXT UV+UF Booster',4,'7 L','UV + UF',22000,[copper,guard,'UV e-boiling','In-built booster pump'],{dimensions:'316 × 251 × 462'}),
  product('aquaguard-classic-booster','Aquaguard','Classic Booster',4,'Inline','Booster system',16490,[copper,guard,'In-built booster pump','Compact design'],{dimensions:'313 × 167 × 423'}),
  product('aquaguard-crest-hot','Aquaguard','Crest UV+Hot',4,'Inline','UV + Hot',18490,['Hot, warm and ambient water',copper,'Stainless steel tank',guard,'UV e-boiling'],{dimensions:'315 × 185 × 425',tankWarranty:'Lifetime stainless steel tank warranty'}),
  product('aquaguard-crest','Aquaguard','Crest UV',4,'Inline','UV',15990,[copper,'Smart LED indicator',guard],{dimensions:'315 × 185 × 425'}),
  product('aquaguard-crystal','Aquaguard','Crystal NXT UV',5,'Inline','UV',14000,[copper,guard,'UV e-boiling','Smart LED indicator','Semi-concealable design'],{dimensions:'313 × 167 × 423'}),
  product('aquaguard-mist','Aquaguard','Mist NXT UV',5,'Inline','UV',14000,[guard,'UV e-boiling','Smart LED indicator','Semi-concealable design'],{dimensions:'313 × 167 × 423'}),
  product('merlin-matrix-zinc','Merlin','Matrix Zinc Copper Digital Black',5,null,'RO + Copper alkaline + UV + UF',null,['Taste adjuster']),
  // Repeated UF tokens in the printed Merlin lists are de-duplicated; UV is
  // not inferred where it is absent from the specification text.
  product('merlin-planet-digi','Merlin','Planet Digi Zinc Copper Digital Black',5,null,'RO + Copper alkaline + UF',null,['Taste adjuster']),
  product('merlin-matrix-white','Merlin','Matrix White',6,null,'RO + Alkaline + UF',null,['Taste adjuster']),
  product('merlin-planet-white','Merlin','Planet Copper White',6,null,'RO + Copper alkaline + UF',null,['Taste adjuster']),
  product('merlin-planet-black','Merlin','Planet Copper Black',6,null,'RO + Copper alkaline + UF',null,['Taste adjuster']),
  product('merlin-matrix-black','Merlin','Matrix Black',6,null,'RO + Alkaline + UF',null,['Taste adjuster']),
  product('prolife-fiesta-blue','Prolife','Fiesta Blue',7,null,'Membrane filtration + Copper filter',null,['Anti-bacterial membrane','High-TDS membrane','Self-suction booster pump','Advanced copper filter']),
  product('prolife-fiesta','Prolife','Fiesta',7,null,'Membrane filtration + Copper filter',null,['Anti-bacterial membrane','High-TDS membrane','Self-suction booster pump','Advanced copper filter']),
  product('prolife-fiesta-transparent','Prolife','Fiesta Transparent',7,null,'Membrane filtration + Copper filter',null,['Anti-bacterial membrane','High-TDS membrane','Self-suction booster pump','Advanced copper filter']),
  // User confirmed the Viber image represents a separate product from Fiesta Blue.
  product('prolife-viber','Prolife','Viber',7,null,'UV + Active copper with zinc booster',null,['Smart LED indicator','Mineral guard technology']),
  product('chrome-first-copper','Chrome','1st Choice Copper',16,'7–8 L','RO + UV + UF + TDS + Copper cartridge',23999,['7-stage purification','User manual, installation kit and RO membrane included'],{dimensions:'360 × 330 × 540'}),
  product('chrome-first-alkaline','Chrome','1st Choice Alkaline',16,'7–8 L','RO + UV + UF + TDS + Alkaline cartridge',23999,['7-stage purification','User manual, installation kit and RO membrane included'],{dimensions:'360 × 330 × 540'}),
  product('chrome-first-mineral','Chrome','1st Choice Mineral',16,'7–8 L','RO + UV + UF + TDS + Mineral cartridge',21999,['7-stage purification','User manual, installation kit and RO membrane included'],{dimensions:'360 × 330 × 540'}),
  product('chrome-smart-mineral','Chrome','Smart+ Mineral',16,'7–8 L','RO + UV + UF + TDS + Mineral cartridge',23000,['7-stage purification','User manual, installation kit and RO membrane included'],{dimensions:'330 × 230 × 460'}),
];

export const catalogueSource = 'info/PRODUCT CATALOUGE 2023 OCT copy.pdf';
export const catalogueLabel = 'October 2023 catalogue';
