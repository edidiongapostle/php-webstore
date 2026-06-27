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

$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

$pageTitle = "About - " . $site_name;
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
      background: var(--white);
      color: var(--black);
      line-height: 1.6;
    }

    /* NAV */
    nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      background: rgba(255,255,255,0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--border);
      padding: 1.25rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .nav-logo {
      font-family: 'Fraunces', serif;
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: -0.03em;
      color: var(--black);
      text-decoration: none;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 2rem;
      list-style: none;
    }

    .nav-links a {
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--grey);
      text-decoration: none;
      transition: color 0.2s;
      position: relative;
    }

    .nav-links a:hover {
      color: var(--black);
    }

    .nav-cta {
      background: var(--black);
      color: var(--white) !important;
      padding: 0.6rem 1.2rem;
      border-radius: 100px;
      transition: background 0.2s;
    }

    .nav-cta:hover {
      background: #1a1a1a;
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
      .nav-links {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        bottom: 0;
        flex-direction: column;
        background: var(--white);
        padding: 2rem;
        gap: 1.5rem;
        transform: translateX(100%);
        transition: transform 0.3s ease;
      }

      .nav-links.open {
        transform: translateX(0);
      }

      nav.menu-open {
        background: transparent;
        border-bottom: none;
        backdrop-filter: none;
      }

      .nav-hamburger {
        display: flex;
      }
    }

    /* HERO */
    .hero {
      padding: 8rem 2rem 4rem;
      background: var(--light);
      text-align: center;
    }

    .hero h1 {
      font-family: 'Fraunces', serif;
      font-size: clamp(2.5rem, 6vw, 4rem);
      font-weight: 700;
      letter-spacing: -0.03em;
      line-height: 1.1;
      margin-bottom: 1.5rem;
    }

    .hero p {
      font-size: 1.1rem;
      color: var(--grey);
      max-width: 600px;
      margin: 0 auto 2rem;
    }

    /* CONTENT */
    .content {
      max-width: 800px;
      margin: 0 auto;
      padding: 4rem 2rem;
    }

    .section {
      margin-bottom: 4rem;
    }

    .section h2 {
      font-family: 'Fraunces', serif;
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: -0.025em;
      margin-bottom: 1.5rem;
    }

    .section p {
      color: var(--grey);
      line-height: 1.8;
      margin-bottom: 1rem;
    }

    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }

    .feature {
      padding: 1.5rem;
      background: var(--light);
      border-radius: 12px;
    }

    .feature h3 {
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .feature p {
      font-size: 0.9rem;
      color: var(--grey);
    }

    .cta-box {
      background: var(--black);
      color: var(--white);
      padding: 3rem;
      border-radius: 16px;
      text-align: center;
      margin-top: 3rem;
    }

    .cta-box h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.75rem;
      margin-bottom: 1rem;
    }

    .cta-box a {
      display: inline-block;
      background: var(--white);
      color: var(--black);
      padding: 0.85rem 2rem;
      border-radius: 100px;
      text-decoration: none;
      font-weight: 600;
      margin-top: 1.5rem;
    }

    /* FOOTER */
    footer {
      background: var(--black);
      color: var(--white);
      padding: 3rem 2rem;
      text-align: center;
    }

    .footer-logo {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      color: var(--white);
      text-decoration: none;
      display: block;
      margin-bottom: 1.5rem;
    }

    .footer-links {
      display: flex;
      justify-content: center;
      gap: 2rem;
      list-style: none;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }

    .footer-links a {
      color: var(--grey);
      text-decoration: none;
      font-size: 0.9rem;
      transition: color 0.2s;
    }

    .footer-links a:hover {
      color: var(--white);
    }

    .footer-copy {
      color: var(--grey);
      font-size: 0.85rem;
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

  <!-- HERO -->
  <section class="hero">
    <h1>About Us</h1>
    <p><?php echo htmlspecialchars($site_name); ?> is dedicated to providing high-quality digital products and services to our customers.</p>
  </section>

  <!-- CONTENT -->
  <div class="content">
    <div class="section">
      <h2>Our Mission</h2>
      <p>We believe in making premium digital assets accessible to everyone, with a focus on quality, security, and customer satisfaction. Every website in our collection is carefully crafted to help businesses establish a strong online presence quickly and affordably.</p>
    </div>

    <div class="section">
      <h2>What We Offer</h2>
      <div class="features">
        <div class="feature">
          <h3>Premium Websites</h3>
          <p>High-quality, professionally designed websites ready for immediate use.</p>
        </div>
        <div class="feature">
          <h3>Secure Payments</h3>
          <p>Multiple payment options including cryptocurrency with bank-level security.</p>
        </div>
        <div class="feature">
          <h3>Instant Delivery</h3>
          <p>Get your purchases immediately after payment verification.</p>
        </div>
        <div class="feature">
          <h3>24/7 Support</h3>
          <p>Our team is always ready to help you with any questions.</p>
        </div>
      </div>
    </div>

    <div class="section">
      <h2>Our Story</h2>
      <p>Founded with a passion for digital excellence, <?php echo htmlspecialchars($site_name); ?> has grown from a small idea to a trusted platform for digital products. We understand the importance of having the right tools and resources to succeed in the digital world, and we're committed to providing exactly that.</p>
    </div>

    <div class="section">
      <h2>Why Choose Us?</h2>
      <ul style="color:var(--grey);line-height:1.8;">
        <li>Curated selection of premium digital products</li>
        <li>Competitive pricing without compromising quality</li>
        <li>Secure and anonymous checkout options</li>
        <li>Regular updates and improvements to our products</li>
        <li>Dedicated customer support team</li>
      </ul>
    </div>

    <div class="cta-box">
      <h3>Get in Touch</h3>
      <p>Have questions or want to learn more? We'd love to hear from you!</p>
      <a href="contact.php">Contact Us →</a>
    </div>
  </div>

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
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');
    const nav = document.querySelector('nav');
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      nav.classList.toggle('menu-open');
    });
    navLinks.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        navLinks.classList.remove('open');
        nav.classList.remove('menu-open');
      });
    });
  </script>
</body>
</html>
