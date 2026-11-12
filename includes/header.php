<?php
declare(strict_types=1);

/**
 * Waypure Frontend Shared Header
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/functions.php';

$pdo = getDb();
$sessionId = getVisitorSessionId();
$cartSummary = getCartSummary($pdo, $sessionId);
$wishlistSummary = getWishlistSummary($pdo, $sessionId);

$currentPage = $currentPage ?? 'home';
$pageTitle = $pageTitle ?? 'Waypure | Water Purifiers & Pure Living Solutions';
$pageDesc = $pageDesc ?? 'Waypure — drink pure, eat pure, breathe pure. Explore water purifiers, air purifiers, ozone detoxifiers and water treatment solutions in Angadipuram and Pulamanthole.';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#edf9f5">
  <meta name="description" content="<?= e($pageDesc) ?>">
  <title><?= e($pageTitle) ?></title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>favicon/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon/favicon.svg" />
  <link rel="shortcut icon" href="<?= BASE_URL ?>favicon/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>favicon/apple-touch-icon.png" />
  <meta name="apple-mobile-web-app-title" content="Waypure" />
  <link rel="manifest" href="<?= BASE_URL ?>favicon/site.webmanifest" />

  <!-- Fonts & Preloads -->
  <link rel="preload" href="<?= BASE_URL ?>assets/manrope-variable.ttf" as="font" type="font/ttf" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <script>document.documentElement.classList.add('booting');setTimeout(function(){document.documentElement.classList.remove('booting')},1600);</script>

  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?= BASE_URL ?>styles.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>water-theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>water-effects.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>fresh-water.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>waypure.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>hero-awareness.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>wq-story.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>pages.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>ecommerce.css">

  <!-- Core Scripts -->
  <script type="importmap">{"imports":{"three":"<?= BASE_URL ?>vendor/three.module.js"}}</script>
  <script defer src="<?= BASE_URL ?>vendor/gsap.min.js"></script>
  <script defer src="<?= BASE_URL ?>vendor/ScrollTrigger.min.js"></script>
  <script defer src="<?= BASE_URL ?>vendor/aos.js"></script>
  <script defer src="<?= BASE_URL ?>vendor/lenis.min.js"></script>
  <script defer src="<?= BASE_URL ?>preloader.js"></script>
  <script defer src="<?= BASE_URL ?>ecommerce.js"></script>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
  <div class="water-preloader" aria-hidden="true"><div class="preloader-mark"><svg viewBox="0 0 64 80"><defs><clipPath id="loader-drop"><path d="M32 3C26 18 8 34 8 49a24 24 0 0 0 48 0C56 34 38 18 32 3Z"/></clipPath></defs><path class="loader-outline" d="M32 3C26 18 8 34 8 49a24 24 0 0 0 48 0C56 34 38 18 32 3Z"/><g clip-path="url(#loader-drop)"><path class="loader-fill" d="M-64 42Q-48 30-32 42T0 42T32 42T64 42T96 42V90H-64Z"/></g><path class="loader-shine" d="M19 49c0 9 5 14 13 14"/></svg><span class="preloader-ripple"></span></div></span></div>
  <a class="skip-link" href="#details">Skip to product details</a>
  
  <header class="header">
    <a class="brand" href="<?= BASE_URL ?>index.php" aria-label="Waypure home">
      <img class="brand-logo" src="<?= BASE_URL ?>assets/img/logo/logo.png" alt="Waypure" width="1746" height="1724" fetchpriority="high">
      <span class="brand-name">way<span>pure</span><small>DRINK · EAT · BREATHE PURE</small></span>
    </a>
    
    <nav class="desktop-nav" aria-label="Main navigation">
      <a href="<?= BASE_URL ?>index.php" <?= $currentPage === 'home' ? 'aria-current="page"' : '' ?>>Home</a>
      <a href="<?= BASE_URL ?>about.html" <?= $currentPage === 'about' ? 'aria-current="page"' : '' ?>>About</a>
      <a href="<?= BASE_URL ?>products.html" <?= $currentPage === 'products' ? 'aria-current="page"' : '' ?>>Products</a>
      <a href="<?= BASE_URL ?>contact.html" <?= $currentPage === 'contact' ? 'aria-current="page"' : '' ?>>Contact</a>
    </nav>

    <div class="header-actions">
      <a href="<?= BASE_URL ?>wishlist.php" class="ecom-nav-btn wishlist-nav-btn" aria-label="Wishlist" title="Wishlist">
        <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        <span class="ecom-badge wishlist-badge" style="<?= $wishlistSummary['count'] > 0 ? 'display:flex;' : 'display:none;' ?>"><?= $wishlistSummary['count'] ?></span>
      </a>
      
      <button type="button" class="ecom-nav-btn cart-nav-btn" id="open-cart-drawer" aria-label="Shopping Cart" title="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <span class="ecom-badge cart-badge" style="<?= $cartSummary['count'] > 0 ? 'display:flex;' : 'display:none;' ?>"><?= $cartSummary['count'] ?></span>
      </button>
    </div>

    <a href="<?= BASE_URL ?>products.html" class="nav-cta">Explore products <span>↗</span></a>
    <button class="menu-toggle" aria-label="Open navigation" aria-expanded="false" aria-controls="mobile-nav"><span></span><span></span></button>
  </header>

  <nav id="mobile-nav" class="mobile-nav" aria-label="Mobile navigation" hidden>
    <a href="<?= BASE_URL ?>index.php" <?= $currentPage === 'home' ? 'aria-current="page"' : '' ?>>Home</a>
    <a href="<?= BASE_URL ?>about.html" <?= $currentPage === 'about' ? 'aria-current="page"' : '' ?>>About</a>
    <a href="<?= BASE_URL ?>products.html" <?= $currentPage === 'products' ? 'aria-current="page"' : '' ?>>Products</a>
    <a href="<?= BASE_URL ?>contact.html" <?= $currentPage === 'contact' ? 'aria-current="page"' : '' ?>>Contact</a>
    <a href="<?= BASE_URL ?>cart.php">Shopping Cart (<?= $cartSummary['count'] ?>)</a>
    <a href="<?= BASE_URL ?>wishlist.php">Saved Wishlist (<?= $wishlistSummary['count'] ?>)</a>
  </nav>

