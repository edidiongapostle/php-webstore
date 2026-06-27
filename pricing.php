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

// Get settings from database
$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

$pageTitle = "Pricing - " . $site_name;
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
    }

    body {
      font-family: 'Inter', sans-serif;
      color: var(--black);
      background: var(--white);
      line-height: 1.6;
    }

    /* NAV */
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

      .nav-hamburger {
        display: flex;
      }
    }

    /* PAGE HEADER */
    .page-header {
      padding: 8rem 2rem 4rem;
      text-align: center;
      background: var(--light);
    }

    .page-header h1 {
      font-family: 'Fraunces', serif;
      font-size: 2.5rem;
      font-weight: 700;
      letter-spacing: -0.03em;
      line-height: 1.1;
      color: var(--black);
      margin-bottom: 1rem;
    }

    .page-header p {
      color: var(--grey);
      font-size: 1rem;
      max-width: 600px;
      margin: 0 auto;
    }

    /* PRICING SECTION */
    .pricing-section {
      padding: 4rem 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .pricing-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }

    .pricing-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 3rem 2rem;
      text-align: center;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .pricing-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
    }

    .pricing-card.featured {
      border-color: var(--accent);
      background: linear-gradient(135deg, var(--white) 0%, #F8F9FF 100%);
    }

    .pricing-badge {
      display: inline-block;
      background: var(--accent);
      color: var(--white);
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      padding: 0.4rem 1rem;
      border-radius: 100px;
      margin-bottom: 1.5rem;
    }

    .pricing-card h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .pricing-price {
      font-family: 'Fraunces', serif;
      font-size: 3rem;
      font-weight: 700;
      letter-spacing: -0.04em;
      line-height: 1;
      margin-bottom: 0.5rem;
    }

    .pricing-price span {
      font-size: 1rem;
      font-weight: 500;
      color: var(--grey);
    }

    .pricing-description {
      color: var(--grey);
      font-size: 0.95rem;
      margin-bottom: 2rem;
    }

    .pricing-features {
      list-style: none;
      margin-bottom: 2rem;
      text-align: left;
    }

    .pricing-features li {
      padding: 0.75rem 0;
      border-bottom: 1px solid var(--border);
      color: var(--grey);
      font-size: 0.9rem;
    }

    .pricing-features li:last-child {
      border-bottom: none;
    }

    .pricing-features li i {
      color: var(--accent);
      margin-right: 0.75rem;
    }

    .pricing-btn {
      display: inline-block;
      padding: 1rem 2rem;
      border-radius: 100px;
      font-size: 0.95rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }

    .pricing-btn.primary {
      background: var(--black);
      color: var(--white);
    }

    .pricing-btn.primary:hover {
      background: var(--accent);
    }

    .pricing-btn.secondary {
      background: var(--light);
      color: var(--black);
      border: 1px solid var(--border);
    }

    .pricing-btn.secondary:hover {
      background: var(--border);
    }

    /* FOOTER */
    footer {
      background: var(--black);
      color: var(--white);
      padding: 4rem 2rem;
      margin-top: 4rem;
    }

    .footer-content {
      max-width: 1400px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 3rem;
    }

    .footer-col h4 {
      font-family: 'Fraunces', serif;
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .footer-col ul {
      list-style: none;
    }

    .footer-col ul li {
      margin-bottom: 0.5rem;
    }

    .footer-col ul li a {
      color: var(--grey);
      text-decoration: none;
      transition: color 0.2s;
    }

    .footer-col ul li a:hover {
      color: var(--white);
    }

    .footer-bottom {
      max-width: 1400px;
      margin: 3rem auto 0;
      padding-top: 2rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      text-align: center;
      color: var(--grey);
      font-size: 0.875rem;
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
        <form action="browse.php" method="GET">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search...">
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

  <!-- PAGE HEADER -->
  <div class="page-header">
    <h1>Simple, Transparent Pricing</h1>
    <p>Choose the perfect website for your needs at competitive prices</p>
  </div>

  <!-- PRICING SECTION -->
  <section class="pricing-section">
    <div class="pricing-grid">
      <div class="pricing-card">
        <h3>Standard</h3>
        <div class="pricing-price">$49 <span>per website</span></div>
        <p class="pricing-description">Perfect for small businesses and personal projects</p>
        <ul class="pricing-features">
          <li><i class="fas fa-check"></i> Full source code</li>
          <li><i class="fas fa-check"></i> 6 months support</li>
          <li><i class="fas fa-check"></i> Free updates</li>
          <li><i class="fas fa-check"></i> Documentation included</li>
          <li><i class="fas fa-check"></i> Commercial license</li>
        </ul>
        <a href="browse.php" class="pricing-btn secondary">Browse Standard</a>
      </div>

      <div class="pricing-card featured">
        <div class="pricing-badge">Most Popular</div>
        <h3>Premium</h3>
        <div class="pricing-price">$99 <span>per website</span></div>
        <p class="pricing-description">Best value for growing businesses with advanced features</p>
        <ul class="pricing-features">
          <li><i class="fas fa-check"></i> Full source code</li>
          <li><i class="fas fa-check"></i> 12 months priority support</li>
          <li><i class="fas fa-check"></i> Free updates</li>
          <li><i class="fas fa-check"></i> Documentation included</li>
          <li><i class="fas fa-check"></i> Commercial license</li>
          <li><i class="fas fa-check"></i> Custom installation help</li>
          <li><i class="fas fa-check"></i> 1 hour consultation</li>
        </ul>
        <a href="browse.php" class="pricing-btn primary">Browse Premium</a>
      </div>

      <div class="pricing-card">
        <h3>Enterprise</h3>
        <div class="pricing-price">$199 <span>per website</span></div>
        <p class="pricing-description">Complete solution for large organizations</p>
        <ul class="pricing-features">
          <li><i class="fas fa-check"></i> Full source code</li>
          <li><i class="fas fa-check"></i> 24 months priority support</li>
          <li><i class="fas fa-check"></i> Free updates</li>
          <li><i class="fas fa-check"></i> Documentation included</li>
          <li><i class="fas fa-check"></i> Commercial license</li>
          <li><i class="fas fa-check"></i> Custom installation help</li>
          <li><i class="fas fa-check"></i> 3 hours consultation</li>
          <li><i class="fas fa-check"></i> Priority bug fixes</li>
        </ul>
        <a href="browse.php" class="pricing-btn secondary">Browse Enterprise</a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="footer-content">
      <div class="footer-col">
        <h4><?php echo htmlspecialchars($site_name); ?></h4>
        <p style="color:var(--grey);font-size:0.9rem;">Premium websites for your business.</p>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="browse.php">Browse</a></li>
          <li><a href="categories.php">Categories</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Legal</h4>
        <ul>
          <li><a href="privacy.php">Privacy Policy</a></li>
          <li><a href="terms.php">Terms of Service</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.
    </div>
  </footer>

  <script>
    // Mobile menu toggle
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');

    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
  </script>
</body>
</html>
