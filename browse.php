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
$websites = searchWebsites($search_query);

// Get settings from database
$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

$pageTitle = "Browse Websites - " . $site_name;
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

    /* PACKAGES SECTION */
    .packages-section {
      padding: 4rem 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    .packages-header {
      margin-bottom: 3rem;
    }

    .section-eyebrow {
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--grey);
      margin-bottom: 1rem;
    }

    .packages-header h2 {
      font-family: 'Fraunces', serif;
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: -0.03em;
      line-height: 1.2;
      color: var(--black);
    }

    .packages-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 2rem;
    }

    .pkg-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 16px;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .pkg-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    }

    .pkg-card h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.75rem;
      font-weight: 700;
      letter-spacing: -0.025em;
      line-height: 1.1;
      color: var(--black);
      margin-bottom: 0.75rem;
    }

    .pkg-card .price {
      font-family: 'Fraunces', serif;
      font-size: 2.5rem;
      font-weight: 700;
      letter-spacing: -0.04em;
      line-height: 1;
      margin-bottom: 1rem;
    }

    .pkg-card .category {
      font-size: 0.7rem;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--grey);
      margin-bottom: 0.75rem;
    }

    .pkg-card .description {
      color: var(--grey);
      font-size: 0.9rem;
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }

    .pkg-card .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 1rem 1.5rem;
      border-radius: 100px;
      font-size: 0.95rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }

    .pkg-card .btn-primary {
      background: var(--black);
      color: var(--white);
    }

    .pkg-card .btn-primary:hover {
      background: var(--accent);
    }

    .pkg-card .btn-ghost {
      background: transparent;
      color: var(--grey);
      border: 1px solid var(--border);
    }

    .pkg-card .btn-ghost:hover {
      border-color: var(--black);
      color: var(--black);
    }

    .pkg-card .actions {
      display: flex;
      gap: 0.75rem;
      margin-top: auto;
    }

    .pkg-card .actions .btn {
      flex: 1;
    }

    /* MOBILE */
    @media (max-width: 768px) {
      .nav-links {
        display: none;
      }

      .nav-links.open {
        display: flex;
        flex-direction: column;
        background: var(--white);
        padding: 2rem;
        gap: 1.5rem;
        transform: translateX(0);
      }

      nav.menu-open {
        background: var(--white);
        border-bottom: none;
      }

      .nav-hamburger {
        display: flex;
      }

      .packages-grid {
        grid-template-columns: 1fr;
      }

      .pkg-card {
        padding: 2rem;
      }
    }

    @media (max-width: 480px) {
      .pkg-card {
        padding: 1.5rem;
      }
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

  <!-- PAGE HEADER -->
  <div class="page-header">
    <?php if (!empty($search_query)): ?>
      <h1>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>
      <p>Found <?php echo count($websites); ?> result(s)</p>
    <?php else: ?>
      <h1>Browse All Websites</h1>
      <p>Explore our complete collection of premium websites ready for your business</p>
    <?php endif; ?>
  </div>

  <!-- WEBSITES GRID -->
  <section class="packages-section">
    <?php if (empty($websites)): ?>
      <?php if (!empty($search_query)): ?>
        <div style="text-align:center;padding:4rem;background:var(--white);border:1px solid var(--border);border-radius:20px;">
          <i class="fas fa-search" style="font-size:3rem;color:var(--grey);margin-bottom:1rem;"></i>
          <p style="color:var(--grey);margin-bottom:1rem;">No websites found matching "<?php echo htmlspecialchars($search_query); ?>"</p>
          <a href="browse.php" style="color:var(--accent);text-decoration:none;font-weight:500;">Clear search and view all websites</a>
        </div>
      <?php else: ?>
        <div style="text-align:center;padding:4rem;background:var(--white);border:1px solid var(--border);border-radius:20px;">
          <p style="color:var(--grey);">No websites available at the moment.</p>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="packages-grid">
        <?php foreach ($websites as $website): ?>
          <div class="pkg-card" style="padding:0;overflow:hidden;">
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
                <h3 style="font-family:'Fraunces',serif;font-size:1.75rem;font-weight:700;letter-spacing:-0.025em;line-height:1.1;color:var(--black);margin-bottom:0.75rem;"><?php echo htmlspecialchars($website['title']); ?></h3>
                <div class="price" style="font-family:'Fraunces',serif;font-size:2.5rem;font-weight:700;letter-spacing:-0.04em;line-height:1;">$<?php echo number_format($website['price'], 2); ?></div>
                <p class="description"><?php echo htmlspecialchars(substr($website['description'], 0, 150)); ?>...</p>
              </div>
              <div class="actions" style="display:flex;gap:1rem;">
                <a href="website.php?id=<?php echo $website['id']; ?>" class="btn btn-ghost" style="flex:1;text-align:center;padding:1rem 1.5rem;border-radius:100px;font-size:0.95rem;font-weight:600;">View Details</a>
                <a href="website.php?id=<?php echo $website['id']; ?>" class="btn btn-primary" style="flex:1;text-align:center;padding:1rem 1.5rem;border-radius:100px;font-size:0.95rem;font-weight:600;">Add to Cart</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
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
          <li><a href="about.php">About</a></li>
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
