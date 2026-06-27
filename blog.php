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

$pageTitle = "Blog - " . $site_name;
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
        background: white;
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
      margin: 0 auto;
    }

    /* BLOG GRID */
    .blog-grid {
      max-width: 1200px;
      margin: 4rem auto;
      padding: 0 2rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 2rem;
    }

    .blog-card {
      border: 1px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .blog-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 40px rgba(0,0,0,0.08);
    }

    .blog-image {
      height: 200px;
      background: linear-gradient(135deg, var(--accent), #4F6FFF);
    }

    .blog-content {
      padding: 1.5rem;
    }

    .blog-category {
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 0.75rem;
    }

    .blog-title {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 700;
      letter-spacing: -0.025em;
      margin-bottom: 0.75rem;
      line-height: 1.3;
    }

    .blog-excerpt {
      font-size: 0.9rem;
      color: var(--grey);
      line-height: 1.6;
      margin-bottom: 1rem;
    }

    .blog-link {
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--accent);
      text-decoration: none;
    }

    .blog-link:hover {
      text-decoration: underline;
    }

    /* CTA */
    .cta-section {
      max-width: 800px;
      margin: 4rem auto;
      padding: 3rem 2rem;
      text-align: center;
      background: var(--light);
      border-radius: 16px;
    }

    .cta-section a {
      display: inline-block;
      background: var(--black);
      color: var(--white);
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
    <h1>Blog</h1>
    <p>Tips, tutorials, and insights to help you make the most of your website.</p>
  </section>

  <!-- BLOG GRID -->
  <div class="blog-grid">
    <div class="blog-card">
      <div class="blog-image" style="background: linear-gradient(135deg, #1A3BFF, #4F6FFF);"></div>
      <div class="blog-content">
        <div class="blog-category">Tips & Tricks</div>
        <h3 class="blog-title">How to Choose the Right Website Template</h3>
        <p class="blog-excerpt">Learn the key factors to consider when selecting a website template for your project.</p>
        <a href="#" class="blog-link">Read More →</a>
      </div>
    </div>

    <div class="blog-card">
      <div class="blog-image" style="background: linear-gradient(135deg, #22C55E, #4ADE80);"></div>
      <div class="blog-content">
        <div class="blog-category">Tutorial</div>
        <h3 class="blog-title">Getting Started with Your New Website</h3>
        <p class="blog-excerpt">A step-by-step guide to setting up and customizing your purchased website.</p>
        <a href="#" class="blog-link">Read More →</a>
      </div>
    </div>

    <div class="blog-card">
      <div class="blog-image" style="background: linear-gradient(135deg, #F59E0B, #FBBF24);"></div>
      <div class="blog-content">
        <div class="blog-category">News</div>
        <h3 class="blog-title">New Payment Methods Available</h3>
        <p class="blog-excerpt">We've added more payment options to make your shopping experience even better.</p>
        <a href="#" class="blog-link">Read More →</a>
      </div>
    </div>

    <div class="blog-card">
      <div class="blog-image" style="background: linear-gradient(135deg, #06B6D4, #22D3EE);"></div>
      <div class="blog-content">
        <div class="blog-category">Security</div>
        <h3 class="blog-title">Protecting Your Digital Downloads</h3>
        <p class="blog-excerpt">Best practices for securing your purchased digital files and assets.</p>
        <a href="#" class="blog-link">Read More →</a>
      </div>
    </div>

    <div class="blog-card">
      <div class="blog-image" style="background: linear-gradient(135deg, #EC4899, #F472B6);"></div>
      <div class="blog-content">
        <div class="blog-category">Features</div>
        <h3 class="blog-title">Top 5 Website Features You Need</h3>
        <p class="blog-excerpt">Essential features every modern website should have to succeed online.</p>
        <a href="#" class="blog-link">Read More →</a>
      </div>
    </div>

    <div class="blog-card">
      <div class="blog-image" style="background: linear-gradient(135deg, #8B5CF6, #A78BFA);"></div>
      <div class="blog-content">
        <div class="blog-category">Updates</div>
        <h3 class="blog-title">Platform Updates Coming Soon</h3>
        <p class="blog-excerpt">Exciting new features and improvements coming to our platform.</p>
        <a href="#" class="blog-link">Read More →</a>
      </div>
    </div>
  </div>

  <!-- CTA -->
  <div class="cta-section">
    <p style="color:var(--grey);">More articles coming soon!</p>
    <a href="index.php">Browse Our Products →</a>
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
