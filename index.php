<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Check maintenance mode
$maintenance_mode = getSetting('maintenance_mode', '0');
if ($maintenance_mode === '1' && !isset($_SESSION['admin_logged_in'])) {
    http_response_code(503);
    include 'maintenance.php';
    exit;
}

// Handle search query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search_query)) {
    $websites = searchWebsites($search_query);
} else {
    $websites = getFeaturedWebsites();
}

// Get settings from database
$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

$pageTitle = $seo_title . " - " . $site_name;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($seo_description); ?>">
  <meta name="keywords" content="<?php echo htmlspecialchars($seo_keywords); ?>">
  <meta name="author" content="<?php echo htmlspecialchars($site_name); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,300;1,9..144,600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --white:   #FFFFFF;
      --black:   #0D0D0D;
      --grey:    #6B6B6B;
      --light:   #F5F5F3;
      --border:  #E4E4E0;
      --accent:  #1A3BFF;
      --accent2: #EEF0FF;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--white);
      color: var(--black);
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }

    /* ── NAV ── */
    nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      background: rgba(255,255,255,0.92);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 5vw;
      height: 64px;
    }

    .nav-logo {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--black);
      text-decoration: none;
      letter-spacing: -0.02em;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 2.5rem;
      list-style: none;
    }

    .nav-links a {
      font-size: 0.875rem;
      font-weight: 500;
      color: var(--grey);
      text-decoration: none;
      transition: color 0.2s;
    }

    .nav-links a:hover { color: var(--black); }

    .nav-search {
      position: relative;
    }

    .nav-search input {
      padding: 0.5rem 1rem 0.5rem 2.5rem;
      border: 1px solid var(--border);
      border-radius: 100px;
      font-size: 0.85rem;
      font-family: 'Inter', sans-serif;
      width: 200px;
      outline: none;
      transition: border-color 0.2s, width 0.2s;
    }

    .nav-search input:focus {
      border-color: var(--accent);
      width: 250px;
    }

    .nav-search i {
      position: absolute;
      left: 0.85rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--grey);
      font-size: 0.85rem;
      pointer-events: none;
    }

    .nav-cta {
      background: var(--black);
      color: var(--white) !important;
      padding: 0.55rem 1.25rem;
      border-radius: 100px;
      font-size: 0.875rem !important;
      font-weight: 500 !important;
      transition: background 0.2s, transform 0.15s !important;
    }

    .nav-cta:hover {
      background: var(--accent) !important;
      color: var(--white) !important;
      transform: translateY(-1px);
    }

    .nav-hamburger {
      display: none;
      flex-direction: column;
      gap: 5px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 4px;
    }

    .nav-hamburger span {
      display: block;
      width: 22px;
      height: 2px;
      background: var(--black);
      border-radius: 2px;
      transition: all 0.3s;
    }

    /* ── HERO ── */
    .hero {
      padding-top: 64px;
      min-height: 100svh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      overflow: hidden;
    }

    .hero-inner {
      padding: 8vh 5vw 5vh;
    }

    .hero-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.78rem;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--accent);
      background: var(--accent2);
      padding: 0.4rem 0.9rem;
      border-radius: 100px;
      margin-bottom: 2rem;
    }

    .hero-eyebrow::before {
      content: '';
      width: 6px; height: 6px;
      background: var(--accent);
      border-radius: 50%;
      display: inline-block;
    }

    h1 {
      font-family: 'Fraunces', serif;
      font-size: clamp(3rem, 7vw, 6.5rem);
      font-weight: 700;
      line-height: 1.03;
      letter-spacing: -0.03em;
      max-width: 14ch;
      color: var(--black);
    }

    h1 em {
      font-style: italic;
      color: var(--accent);
      font-weight: 300;
    }

    .hero-sub {
      margin-top: 2rem;
      font-size: clamp(1rem, 1.5vw, 1.2rem);
      color: var(--grey);
      font-weight: 400;
      max-width: 46ch;
      line-height: 1.7;
    }

    .hero-actions {
      margin-top: 2.75rem;
      display: flex;
      align-items: center;
      gap: 1.25rem;
      flex-wrap: wrap;
    }

    .btn-primary {
      background: var(--black);
      color: var(--white);
      padding: 0.85rem 2rem;
      border-radius: 100px;
      font-size: 0.95rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    }

    .btn-primary:hover {
      background: var(--accent);
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(26,59,255,0.25);
    }

    .btn-ghost {
      color: var(--black);
      font-size: 0.95rem;
      font-weight: 500;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      border-bottom: 1.5px solid var(--border);
      padding-bottom: 2px;
      transition: border-color 0.2s, color 0.2s;
    }

    .btn-ghost:hover {
      color: var(--accent);
      border-color: var(--accent);
    }

    /* ── MARQUEE ── */
    .marquee-section {
      margin-top: 5vh;
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
      padding: 1.5rem 0;
      overflow: hidden;
      background: var(--light);
    }

    .marquee-track {
      display: flex;
      gap: 1.5rem;
      width: max-content;
      animation: marquee 28s linear infinite;
    }

    .marquee-track:hover { animation-play-state: paused; }

    @keyframes marquee {
      0%   { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }

    .site-card {
      width: 180px;
      height: 110px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: var(--white);
      overflow: hidden;
      flex-shrink: 0;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06);
      display: flex;
      flex-direction: column;
    }

    .site-card-bar {
      height: 22px;
      background: var(--light);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      padding: 0 8px;
      gap: 4px;
    }

    .dot { width: 6px; height: 6px; border-radius: 50%; }
    .dot-r { background: #FF5F56; }
    .dot-y { background: #FFBD2E; }
    .dot-g { background: #27C93F; }

    .site-card-body {
      flex: 1;
      padding: 8px 10px;
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .s-line { height: 5px; border-radius: 3px; background: var(--border); }
    .s-line.dark { background: #D0D0CC; }
    .s-line.accent { background: var(--accent); opacity: 0.25; }
    .s-block { height: 28px; border-radius: 6px; background: var(--light); margin-top: 4px; }

    /* ── SECTION SHARED ── */
    section { padding: 8rem 5vw; }

    .section-eyebrow {
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--grey);
      margin-bottom: 1.25rem;
    }

    h2 {
      font-family: 'Fraunces', serif;
      font-size: clamp(2rem, 4vw, 3.25rem);
      font-weight: 600;
      line-height: 1.1;
      letter-spacing: -0.025em;
      color: var(--black);
    }

    /* ── STATS ── */
    .stats-section {
      padding: 6rem 5vw;
      background: var(--black);
      color: var(--white);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 3rem;
      max-width: 900px;
      margin: 0 auto;
      text-align: center;
    }

    .stat-num {
      font-family: 'Fraunces', serif;
      font-size: clamp(2.75rem, 6vw, 4.5rem);
      font-weight: 700;
      color: var(--white);
      letter-spacing: -0.04em;
      line-height: 1;
    }

    .stat-num span {
      color: var(--accent);
      font-style: italic;
      font-weight: 300;
    }

    .stat-label {
      font-size: 0.875rem;
      color: rgba(255,255,255,0.45);
      margin-top: 0.6rem;
      font-weight: 400;
      letter-spacing: 0.03em;
    }

    /* ── HOW IT WORKS ── */
    .how-section { background: var(--white); }

    .how-header {
      display: flex;
      flex-direction: column;
      max-width: 480px;
      margin-bottom: 5rem;
    }

    .how-steps {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 0;
      border: 1px solid var(--border);
      border-radius: 20px;
      overflow: hidden;
    }

    .how-step {
      padding: 3rem 2.5rem;
      border-right: 1px solid var(--border);
      position: relative;
      transition: background 0.25s;
    }

    .how-step:last-child { border-right: none; }
    .how-step:hover { background: var(--light); }

    .step-num {
      font-family: 'Fraunces', serif;
      font-size: 3.5rem;
      font-weight: 700;
      color: var(--border);
      line-height: 1;
      margin-bottom: 1.5rem;
      letter-spacing: -0.04em;
    }

    .step-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
      color: var(--black);
    }

    .step-body {
      font-size: 0.9rem;
      color: var(--grey);
      line-height: 1.7;
    }

    /* ── PACKAGES ── */
    .packages-section { background: var(--light); }

    .packages-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 2rem;
      margin-bottom: 4rem;
    }

    .packages-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
    }

    .pkg-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 2.5rem;
      display: flex;
      flex-direction: column;
      gap: 2rem;
      transition: transform 0.2s, box-shadow 0.2s;
      position: relative;
      overflow: hidden;
    }

    .pkg-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 50px rgba(0,0,0,0.08);
    }

    .pkg-card.featured {
      background: var(--black);
      border-color: var(--black);
      color: var(--white);
    }

    .pkg-badge {
      position: absolute;
      top: 1.5rem; right: 1.5rem;
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      background: var(--accent);
      color: var(--white);
      padding: 0.3rem 0.75rem;
      border-radius: 100px;
    }

    .pkg-name {
      font-size: 0.8rem;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--grey);
    }

    .pkg-card.featured .pkg-name { color: rgba(255,255,255,0.5); }

    .pkg-price {
      font-family: 'Fraunces', serif;
      font-size: 3rem;
      font-weight: 700;
      letter-spacing: -0.04em;
      line-height: 1;
    }

    .pkg-price sup {
      font-size: 1.25rem;
      font-weight: 600;
      vertical-align: top;
      margin-top: 0.4rem;
      display: inline-block;
      font-family: 'Inter', sans-serif;
    }

    .pkg-desc {
      font-size: 0.9rem;
      color: var(--grey);
      line-height: 1.6;
    }

    .pkg-card.featured .pkg-desc { color: rgba(255,255,255,0.55); }

    .pkg-features {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      flex: 1;
    }

    .pkg-features li {
      font-size: 0.875rem;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      color: var(--grey);
    }

    .pkg-card.featured .pkg-features li { color: rgba(255,255,255,0.7); }

    .pkg-features li::before {
      content: '';
      width: 16px; height: 16px;
      background: var(--accent2);
      border-radius: 50%;
      flex-shrink: 0;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M2 5l2.5 2.5L8 3' stroke='%231A3BFF' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: center;
    }

    .pkg-card.featured .pkg-features li::before {
      background-color: rgba(255,255,255,0.1);
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M2 5l2.5 2.5L8 3' stroke='white' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    }

    .pkg-btn {
      display: block;
      text-align: center;
      padding: 0.85rem;
      border-radius: 100px;
      font-size: 0.9rem;
      font-weight: 600;
      text-decoration: none;
      background: var(--light);
      color: var(--black);
      border: 1px solid var(--border);
      transition: background 0.2s, color 0.2s, transform 0.15s;
    }

    .pkg-btn:hover {
      background: var(--black);
      color: var(--white);
      border-color: var(--black);
      transform: translateY(-1px);
    }

    .pkg-card.featured .pkg-btn {
      background: var(--accent);
      color: var(--white);
      border-color: var(--accent);
    }

    .pkg-card.featured .pkg-btn:hover {
      background: var(--white);
      color: var(--black);
      border-color: var(--white);
    }

    /* ── TESTIMONIALS ── */
    .testimonials-section { background: var(--white); }

    .testimonials-header {
      margin-bottom: 3.5rem;
    }

    .testimonials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    .testimonial-card {
      background: var(--light);
      border-radius: 20px;
      padding: 2.25rem;
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      border: 1px solid var(--border);
    }

    .testimonial-text {
      font-size: 1rem;
      color: var(--black);
      line-height: 1.75;
      font-style: italic;
      font-family: 'Fraunces', serif;
      font-weight: 300;
    }

    .testimonial-author {
      display: flex;
      align-items: center;
      gap: 0.85rem;
    }

    .author-avatar {
      width: 40px; height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Fraunces', serif;
      font-weight: 600;
      font-size: 0.9rem;
      color: var(--white);
      flex-shrink: 0;
    }

    .author-name {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--black);
    }

    .author-role {
      font-size: 0.8rem;
      color: var(--grey);
    }

    .stars {
      color: #F59E0B;
      font-size: 0.85rem;
      letter-spacing: 1px;
    }

    /* ── FAQ ── */
    .faq-section {
      background: var(--light);
      padding: 8rem 5vw;
    }

    .faq-inner {
      max-width: 700px;
      margin: 0 auto;
    }

    .faq-header { margin-bottom: 3rem; text-align: center; }

    .faq-item {
      border-bottom: 1px solid var(--border);
    }

    .faq-question {
      width: 100%;
      background: none;
      border: none;
      text-align: left;
      padding: 1.5rem 0;
      font-size: 1rem;
      font-weight: 600;
      color: var(--black);
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      font-family: 'Inter', sans-serif;
    }

    .faq-icon {
      width: 24px; height: 24px;
      border-radius: 50%;
      background: var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: background 0.2s, transform 0.3s;
      font-size: 1rem;
      line-height: 1;
      color: var(--grey);
    }

    .faq-item.open .faq-icon {
      background: var(--accent);
      color: var(--white);
      transform: rotate(45deg);
    }

    .faq-answer {
      font-size: 0.9rem;
      color: var(--grey);
      line-height: 1.75;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease, padding 0.3s;
    }

    .faq-item.open .faq-answer {
      max-height: 300px;
      padding-bottom: 1.5rem;
    }

    /* ── CTA ── */
    .cta-section {
      background: var(--black);
      padding: 8rem 5vw;
      text-align: center;
    }

    .cta-section h2 {
      color: var(--white);
      max-width: 16ch;
      margin: 0 auto 1.5rem;
    }

    .cta-section h2 em {
      color: var(--accent);
      font-weight: 300;
    }

    .cta-sub {
      color: rgba(255,255,255,0.5);
      font-size: 1rem;
      max-width: 44ch;
      margin: 0 auto 3rem;
      line-height: 1.7;
    }

    /* ── FOOTER ── */
    footer {
      background: var(--black);
      border-top: 1px solid rgba(255,255,255,0.08);
      padding: 3rem 5vw;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1.5rem;
    }

    .footer-logo {
      font-family: 'Fraunces', serif;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--white);
      text-decoration: none;
    }

    .footer-links {
      display: flex;
      gap: 2rem;
      list-style: none;
      flex-wrap: wrap;
    }

    .footer-links a {
      font-size: 0.82rem;
      color: rgba(255,255,255,0.4);
      text-decoration: none;
      transition: color 0.2s;
    }

    .footer-links a:hover { color: rgba(255,255,255,0.9); }

    .footer-copy {
      font-size: 0.8rem;
      color: rgba(255,255,255,0.3);
    }

    /* ── MOBILE ── */
    @media (max-width: 768px) {
      .nav-links { display: none; }
      .nav-hamburger { display: flex; }

      .nav-links.open {
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 64px; left: 0; right: 0;
        background: var(--white);
        border-bottom: 1px solid var(--border);
        padding: 1.5rem 5vw 2rem;
        gap: 1.25rem;
      }

      .how-steps {
        grid-template-columns: 1fr;
        border-radius: 16px;
      }

      .how-step { border-right: none; border-bottom: 1px solid var(--border); }
      .how-step:last-child { border-bottom: none; }

      .packages-header { flex-direction: column; align-items: flex-start; }

      footer { flex-direction: column; align-items: flex-start; }

      section { padding: 5rem 5vw; }
      .stats-section { padding: 4rem 5vw; }

      /* Product cards mobile adjustments */
      .packages-grid {
        grid-template-columns: 1fr;
      }

      .pkg-card {
        padding: 2rem;
      }
    }

    @media (max-width: 480px) {
      h1 { font-size: 2.6rem; }
      .hero-actions { flex-direction: column; align-items: flex-start; }

      /* Product cards mobile adjustments */
      .pkg-card {
        padding: 1.5rem;
      }
    }

    /* ── SCROLL REVEAL ── */
    .reveal {
      opacity: 0;
      transform: translateY(28px);
      transition: opacity 0.65s ease, transform 0.65s ease;
    }

    .reveal.visible {
      opacity: 1;
      transform: none;
    }

    @media (prefers-reduced-motion: reduce) {
      .reveal { opacity: 1; transform: none; }
      .marquee-track { animation: none; }
    }
  </style>
</head>
<body>

  <!-- NAV -->
  <nav>
    <a href="index.php" class="nav-logo"><?php echo htmlspecialchars($site_name); ?></a>
    <ul class="nav-links" id="navLinks">
      <li><a href="browse.php">Browse</a></li>
      <li><a href="categories.php">Categories</a></li>
      <li><a href="pricing.php">Pricing</a></li>
      <li><a href="documentation.php">Documentation</a></li>
      <li><a href="blog.php">Blog</a></li>
      <li><a href="contact.php">Contact</a></li>
      <li class="nav-search">
        <form action="index.php" method="GET">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search_query); ?>">
        </form>
      </li>
      <li><a href="cart.php" class="relative" style="position:relative">
        <i class="fas fa-shopping-cart"></i>
        <?php
        $cart_count = getCartCount();
        if ($cart_count > 0):
        ?>
          <span style="position:absolute;top:-6px;right:-8px;background:#EF4444;color:white;border-radius:50%;width:18px;height:18px;font-size:10px;display:flex;align-items:center;justify-content:center;z-index:1"><?php echo $cart_count; ?></span>
        <?php endif; ?>
      </a></li>
      <li><a href="admin/login.php" class="nav-cta">Admin</a></li>
    </ul>
    <button class="nav-hamburger" id="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <?php if (!empty($search_query)): ?>
  <!-- Search Header -->
  <section style="padding:4rem 2rem 2rem;text-align:center;">
    <h1 style="font-family:'Fraunces',serif;font-size:2.5rem;font-weight:700;letter-spacing:-0.03em;line-height:1.1;color:var(--black);margin-bottom:1rem;">
      Search Results for "<?php echo htmlspecialchars($search_query); ?>"
    </h1>
    <p style="color:var(--grey);font-size:1rem;margin-bottom:2rem;">
      Found <?php echo count($websites); ?> result(s)
    </p>
    <a href="index.php" style="display:inline-flex;align-items:center;gap:0.5rem;color:var(--accent);text-decoration:none;font-weight:500;">
      <i class="fas fa-arrow-left"></i> Clear search and view all websites
    </a>
  </section>
  <?php endif; ?>

  <?php if (empty($search_query)): ?>
  <!-- HERO -->
  <section class="hero">
    <div class="hero-inner">
      <div class="hero-eyebrow">Premium websites, fast</div>
      <h1>Your next site,<br><em>already built.</em></h1>
      <p class="hero-sub">Browse, customize, and launch a professionally designed website in days — not months. No agency retainer. No guesswork.</p>
      
      <!-- Search Bar -->
      <form action="index.php" method="GET" class="search-container" style="max-width:600px;margin:2rem auto;">
        <div style="display:flex;gap:0.5rem;">
          <input 
            type="text" 
            name="search" 
            value="<?php echo htmlspecialchars($search_query); ?>" 
            placeholder="Search by title, technology, category, or features..." 
            style="flex:1;padding:1rem 1.5rem;border:1px solid var(--border);border-radius:100px;font-size:1rem;font-family:'Inter',sans-serif;outline:none;transition:border-color 0.2s;"
          >
          <button type="submit" style="padding:1rem 2rem;background:var(--black);color:var(--white);border:none;border-radius:100px;font-weight:500;cursor:pointer;transition:background 0.2s;">
            <i class="fas fa-search"></i>
          </button>
        </div>
        <?php if (!empty($search_query)): ?>
          <div style="margin-top:0.75rem;text-align:center;">
            <a href="index.php" style="color:var(--grey);text-decoration:none;font-size:0.9rem;">Clear search</a>
          </div>
        <?php endif; ?>
      </form>
      
      <div class="hero-actions">
        <a href="browse.php" class="btn-primary">Browse websites →</a>
        <a href="about.php" class="btn-ghost">Learn more</a>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="how-section" id="how">
    <div class="how-header reveal">
      <div class="section-eyebrow">The process</div>
      <h2>Launch in three steps</h2>
    </div>
    <div class="how-steps">
      <div class="how-step reveal">
        <div class="step-num">01</div>
        <div class="step-title">Browse & Select</div>
        <p class="step-body">Explore our curated collection of premium websites. Filter by category, view live demos, and find the perfect match for your business.</p>
      </div>
      <div class="how-step reveal">
        <div class="step-num">02</div>
        <div class="step-title">Purchase & Download</div>
        <p class="step-body">Add to cart and complete secure checkout. Instant access to source files, documentation, and support resources.</p>
      </div>
      <div class="how-step reveal">
        <div class="step-num">03</div>
        <div class="step-title">Customize & Launch</div>
        <p class="step-body">Customize colors, content, and branding. Deploy to your hosting and go live with your new professional website.</p>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- WEBSITES GRID -->
  <section class="packages-section" id="packages">
    <?php if (empty($search_query)): ?>
    <div class="packages-header">
      <div>
        <div class="section-eyebrow">Featured Websites</div>
        <h2>Premium Websites<br>Ready to Launch</h2>
      </div>
      <p style="max-width:36ch; color: var(--grey); font-size:0.9rem; line-height:1.7;">Professionally designed websites ready for your business. Instant delivery, secure payments, and anonymous checkout available.</p>
    </div>
    <?php endif; ?>

    <?php if (empty($websites)): ?>
      <?php if (!empty($search_query)): ?>
        <div class="text-center py-16 bg-white border border-gray-200 rounded-lg" style="background:var(--white);border:1px solid var(--border);border-radius:20px;padding:4rem;text-align:center;">
          <i class="fas fa-search" style="font-size:3rem;color:var(--grey);margin-bottom:1rem;"></i>
          <p style="color:var(--grey);margin-bottom:1rem;">No websites found matching "<?php echo htmlspecialchars($search_query); ?>"</p>
          <a href="index.php" style="color:var(--accent);text-decoration:none;font-weight:500;">Clear search and view all websites</a>
        </div>
      <?php else: ?>
        <div class="text-center py-16 bg-white border border-gray-200 rounded-lg" style="background:var(--white);border:1px solid var(--border);border-radius:20px;padding:4rem;text-align:center;">
          <p style="color:var(--grey);">No websites available at the moment.</p>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="packages-grid">
        <?php foreach ($websites as $website): ?>
          <div class="pkg-card reveal" style="padding:0;overflow:hidden;">
            <div style="position:relative;">
              <img src="<?php echo htmlspecialchars($website['image_url']); ?>" alt="<?php echo htmlspecialchars($website['title']); ?>" style="width:100%;height:200px;object-fit:cover;">
              <?php if ($website['featured']): ?>
                <span style="position:absolute;top:1rem;right:1rem;background:var(--accent);color:white;font-size:0.72rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;padding:0.3rem 0.75rem;border-radius:100px;">Featured</span>
              <?php endif; ?>
            </div>
            <div style="padding:2.5rem;display:flex;flex-direction:column;gap:2rem;">
              <div>
                <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--grey);margin-bottom:0.75rem;">
                  <?php echo htmlspecialchars($website['category']); ?>
                </div>
                <div style="font-family:'Fraunces',serif;font-size:1.75rem;font-weight:700;letter-spacing:-0.025em;line-height:1.1;color:var(--black);margin-bottom:0.75rem;">
                  <?php echo htmlspecialchars($website['title']); ?>
                </div>
                <div style="font-size:0.9rem;color:var(--grey);line-height:1.6;">
                  <?php echo htmlspecialchars($website['description']); ?>
                </div>
              </div>
              <div style="font-family:'Fraunces',serif;font-size:2.5rem;font-weight:700;letter-spacing:-0.04em;line-height:1;">
                $<?php echo number_format($website['price'], 2); ?>
              </div>
              <div style="display:flex;gap:1rem;">
                <a href="website.php?id=<?php echo $website['id']; ?>" style="flex:1;text-align:center;padding:1rem 1.5rem;border-radius:100px;font-size:0.95rem;font-weight:600;text-decoration:none;background:var(--light);color:var(--black);border:1px solid var(--border);transition:background 0.2s,color 0.2s,transform 0.15s;">
                  View
                </a>
                <form action="add_to_cart.php" method="POST" style="flex:1;">
                  <input type="hidden" name="website_id" value="<?php echo $website['id']; ?>">
                  <button type="submit" style="width:100%;background:var(--accent);color:white;padding:1rem 1.5rem;border-radius:100px;font-size:0.95rem;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:background 0.2s,transform 0.15s;">
                    Add to Cart
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- TESTIMONIALS -->
  <section class="testimonials-section" id="testimonials">
    <div class="testimonials-header reveal">
      <div class="section-eyebrow">Reviews</div>
      <h2>Customers who launched<br>and never looked back.</h2>
    </div>
    <div class="testimonials-grid">
      <div class="testimonial-card reveal">
        <div class="stars">★★★★★</div>
        <p class="testimonial-text">"Found the perfect portfolio template for my design business. Downloaded immediately, customized it in a weekend, and launched by Monday. Incredible value."</p>
        <div class="testimonial-author">
          <div class="author-avatar" style="background:#1A3BFF;">AM</div>
          <div>
            <div class="author-name">Amara Mensah</div>
            <div class="author-role">Founder, Bloom Studio</div>
          </div>
        </div>
      </div>
      <div class="testimonial-card reveal">
        <div class="stars">★★★★★</div>
        <p class="testimonial-text">"The e-commerce template I purchased saved me months of development. Clean code, great documentation, and the support team helped me with a few customizations."</p>
        <div class="testimonial-author">
          <div class="author-avatar" style="background:#0D0D0D;">JK</div>
          <div>
            <div class="author-name">Jonas Keller</div>
            <div class="author-role">CEO, Pinepoint Consulting</div>
          </div>
        </div>
      </div>
      <div class="testimonial-card reveal">
        <div class="stars">★★★★★</div>
        <p class="testimonial-text">"I've purchased templates from many marketplaces. The quality here is exceptional — professional design, well-organized files, and actually responsive. Highly recommend."</p>
        <div class="testimonial-author">
          <div class="author-avatar" style="background:#6B6B6B;">SI</div>
          <div>
            <div class="author-name">Sola Ige</div>
            <div class="author-role">Marketing Director, RYVE</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="faq-section" id="faq">
    <div class="faq-inner">
      <div class="faq-header reveal">
        <div class="section-eyebrow">FAQ</div>
        <h2>Questions, answered.</h2>
      </div>

      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          What do I get when I purchase a website?
          <span class="faq-icon">+</span>
        </button>
        <div class="faq-answer">You receive the complete source code, all assets (images, fonts, icons), documentation, and a license to use the website for your business. Everything you need to get started immediately.</div>
      </div>

      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          Can I customize the website after purchase?
          <span class="faq-icon">+</span>
        </button>
        <div class="faq-answer">Absolutely. All websites are fully customizable. You can edit colors, fonts, content, and layout. We provide documentation to help you make changes, or you can work with any developer.</div>
      </div>

      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          What payment methods do you accept?
          <span class="faq-icon">+</span>
        </button>
        <div class="faq-answer">We accept credit cards, PayPal, and cryptocurrency payments. All transactions are secure and processed through our encrypted payment system.</div>
      </div>

      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          Do you offer refunds?
          <span class="faq-icon">+</span>
        </button>
        <div class="faq-answer">Due to the digital nature of our products, we offer refunds within 7 days of purchase if the website files are not as described or have technical issues that cannot be resolved.</div>
      </div>

      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          What hosting do I need?
          <span class="faq-icon">+</span>
        </button>
        <div class="faq-answer">Our websites work with any standard web hosting that supports PHP and MySQL. We can also recommend affordable hosting providers if you need assistance getting set up.</div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-section" id="cta">
    <div class="reveal">
      <div class="section-eyebrow" style="color:rgba(255,255,255,0.4)">Get started today</div>
      <h2>Your best website is<br><em>ready to launch.</em></h2>
      <p class="cta-sub">Browse our collection of premium websites and find the perfect match for your business.</p>
      <a href="/browse.php" class="btn-primary" style="display:inline-flex;">Browse websites →</a>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <a href="index.php" class="footer-logo"><?php echo htmlspecialchars($site_name); ?></a>
    <ul class="footer-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="blog.php">Blog</a></li>
      <li><a href="contact.php">Contact</a></li>
      <li><a href="cart.php">Cart</a></li>
      <li><a href="privacy.php">Privacy Policy</a></li>
      <li><a href="terms.php">Terms of Use</a></li>
    </ul>
    <span class="footer-copy">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</span>
  </footer>

  <script>
    // ── FAQ ──
    document.querySelectorAll('.faq-question').forEach(btn => {
      btn.addEventListener('click', () => {
        const item = btn.parentElement;
        const isOpen = item.classList.contains('open');
        document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
        if (!isOpen) item.classList.add('open');
        btn.setAttribute('aria-expanded', !isOpen);
      });
    });

    // ── SCROLL REVEAL ──
    const reveals = document.querySelectorAll('.reveal');
    const obs = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          obs.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    reveals.forEach(r => obs.observe(r));

    // ── HAMBURGER ──
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
    navLinks.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => navLinks.classList.remove('open'));
    });
  </script>
</body>
</html>