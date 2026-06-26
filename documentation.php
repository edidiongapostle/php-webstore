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

$pageTitle = "Documentation - " . $site_name;
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
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--border);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .nav-logo {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 600;
      color: var(--black);
      text-decoration: none;
    }

    .nav-links {
      display: flex;
      gap: 2rem;
      align-items: center;
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
      gap: 0.35rem;
      background: none;
      border: none;
      cursor: pointer;
    }

    .nav-hamburger span {
      width: 24px;
      height: 2px;
      background: var(--black);
      transition: 0.2s;
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
        background: var(--white);
        border-bottom: none;
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

    /* DOCUMENTATION SECTION */
    .documentation-section {
      padding: 4rem 2rem;
      max-width: 900px;
      margin: 0 auto;
    }

    .doc-section {
      margin-bottom: 4rem;
    }

    .doc-section h2 {
      font-family: 'Fraunces', serif;
      font-size: 1.75rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: var(--black);
    }

    .doc-section p {
      color: var(--grey);
      font-size: 1rem;
      line-height: 1.7;
      margin-bottom: 1rem;
    }

    .doc-section ul {
      list-style: none;
      margin: 1.5rem 0;
    }

    .doc-section ul li {
      padding: 0.75rem 0;
      border-bottom: 1px solid var(--border);
      color: var(--grey);
    }

    .doc-section ul li:last-child {
      border-bottom: none;
    }

    .doc-section ul li i {
      color: var(--accent);
      margin-right: 0.75rem;
    }

    .doc-section code {
      background: var(--light);
      padding: 0.2rem 0.5rem;
      border-radius: 4px;
      font-family: monospace;
      font-size: 0.9rem;
    }

    .doc-section .alert {
      background: #FFF3E0;
      border-left: 4px solid #FF9800;
      padding: 1rem 1.5rem;
      margin: 1.5rem 0;
      border-radius: 4px;
    }

    .doc-section .alert strong {
      color: #E65100;
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
    <h1>Documentation</h1>
    <p>Everything you need to know about using our websites</p>
  </div>

  <!-- DOCUMENTATION SECTION -->
  <section class="documentation-section">
    <div class="doc-section">
      <h2>Getting Started</h2>
      <p>After purchasing a website from our store, you'll receive instant access to download the source files. Here's how to get started:</p>
      <ul>
        <li><i class="fas fa-check"></i> Download the ZIP file from your order confirmation page</li>
        <li><i class="fas fa-check"></i> Extract the files to your local development environment</li>
        <li><i class="fas fa-check"></i> Review the README.md file for specific setup instructions</li>
        <li><i class="fas fa-check"></i> Configure your database and environment variables</li>
        <li><i class="fas fa-check"></i> Deploy to your hosting provider</li>
      </ul>
    </div>

    <div class="doc-section">
      <h2>Installation Guide</h2>
      <p>Most of our websites are built with modern web technologies. The installation process typically involves:</p>
      <ul>
        <li><i class="fas fa-check"></i> Setting up a web server (Apache, Nginx, or similar)</li>
        <li><i class="fas fa-check"></i> Configuring PHP and database connections</li>
        <li><i class="fas fa-check"></i> Running any required migration scripts</li>
        <li><i class="fas fa-check"></i> Setting up environment variables for API keys and configuration</li>
      </ul>
      <div class="alert">
        <strong>Note:</strong> Some websites may have specific requirements. Always check the included documentation for your specific purchase.
      </div>
    </div>

    <div class="doc-section">
      <h2>Customization</h2>
      <p>Our websites are designed to be easily customizable. You can modify:</p>
      <ul>
        <li><i class="fas fa-check"></i> Colors, fonts, and styling through CSS variables</li>
        <li><i class="fas fa-check"></i> Content and text through configuration files or database</li>
        <li><i class="fas fa-check"></i> Features and functionality through modular code structure</li>
        <li><i class="fas fa-check"></i> Images and media assets</li>
      </ul>
    </div>

    <div class="doc-section">
      <h2>Support</h2>
      <p>If you need help with your website, we offer multiple support channels:</p>
      <ul>
        <li><i class="fas fa-check"></i> Email support for all customers</li>
        <li><i class="fas fa-check"></i> Priority support for Premium and Enterprise plans</li>
        <li><i class="fas fa-check"></i> Documentation and knowledge base</li>
        <li><i class="fas fa-check"></i> Video tutorials for common tasks</li>
      </ul>
      <p>For technical issues, please include your order number and a detailed description of the problem when contacting support.</p>
    </div>

    <div class="doc-section">
      <h2>Licensing</h2>
      <p>All websites come with a commercial license that allows you to:</p>
      <ul>
        <li><i class="fas fa-check"></i> Use the website for commercial purposes</li>
        <li><i class="fas fa-check"></i> Modify the code to fit your needs</li>
        <li><i class="fas fa-check"></i> Deploy to unlimited domains</li>
      </ul>
      <p>The license does not allow redistribution or resale of the source code as-is. Contact us for enterprise licensing options.</p>
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
    const nav = document.querySelector('nav');

    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      nav.classList.toggle('menu-open');
    });
  </script>
</body>
</html>
