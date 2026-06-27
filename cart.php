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

$cart_items = getCartItems();
$cart_total = getCartTotal();
$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

$pageTitle = "Shopping Cart - " . $site_name;
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

    /* CART */
    .cart-container {
      max-width: 1200px;
      margin: 4rem auto;
      padding: 0 2rem;
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 3rem;
    }

    @media (max-width: 900px) {
      .cart-container {
        grid-template-columns: 1fr;
      }
    }

    .cart-items {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .cart-item {
      display: flex;
      gap: 1.5rem;
      padding: 1.5rem;
      border: 1px solid var(--border);
      border-radius: 16px;
      align-items: center;
    }

    .cart-item img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 12px;
    }

    .cart-item-details {
      flex: 1;
    }

    .cart-item-details h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .cart-item-details .category {
      color: var(--grey);
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
    }

    .cart-item-details .price {
      font-family: 'Fraunces', serif;
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--accent);
    }

    .cart-item-actions {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .cart-item-actions a {
      color: var(--accent);
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
    }

    .cart-item-actions button {
      background: none;
      border: none;
      color: #DC2626;
      cursor: pointer;
      font-size: 0.9rem;
      font-weight: 500;
    }

    /* SUMMARY */
    .summary-card {
      position: sticky;
      top: 100px;
      padding: 2rem;
      border: 1px solid var(--border);
      border-radius: 16px;
      background: var(--white);
    }

    .summary-card h2 {
      font-family: 'Fraunces', serif;
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 1rem;
      color: var(--grey);
    }

    .summary-row.total {
      border-top: 1px solid var(--border);
      padding-top: 1rem;
      margin-top: 1rem;
      font-weight: 700;
      color: var(--black);
    }

    .summary-row.total .price {
      color: var(--accent);
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
    }

    .checkout-btn {
      width: 100%;
      background: var(--black);
      color: white;
      padding: 0.85rem 2rem;
      border: none;
      border-radius: 100px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      text-decoration: none;
      display: block;
      text-align: center;
      margin-top: 1.5rem;
    }

    .checkout-btn:hover {
      background: #1a1a1a;
    }

    .continue-btn {
      width: 100%;
      background: var(--light);
      color: var(--black);
      padding: 0.85rem 2rem;
      border: none;
      border-radius: 100px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      text-decoration: none;
      display: block;
      text-align: center;
      margin-top: 0.75rem;
    }

    .continue-btn:hover {
      background: #E5E5E3;
    }

    .secure-note {
      margin-top: 1.5rem;
      padding: 1rem;
      background: #EFF6FF;
      border-radius: 8px;
      font-size: 0.85rem;
      color: var(--grey);
      text-align: center;
    }

    /* EMPTY CART */
    .empty-cart {
      max-width: 600px;
      margin: 4rem auto;
      padding: 3rem 2rem;
      text-align: center;
      border: 1px solid var(--border);
      border-radius: 16px;
    }

    .empty-cart i {
      font-size: 4rem;
      color: var(--grey);
      margin-bottom: 1.5rem;
    }

    .empty-cart h2 {
      font-family: 'Fraunces', serif;
      font-size: 1.75rem;
      margin-bottom: 0.75rem;
    }

    .empty-cart p {
      color: var(--grey);
      margin-bottom: 2rem;
    }

    .empty-cart a {
      display: inline-block;
      background: var(--black);
      color: white;
      padding: 0.85rem 2rem;
      border-radius: 100px;
      text-decoration: none;
      font-weight: 600;
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
    <h1>Shopping Cart</h1>
    <p><?php echo getCartCount(); ?> item<?php echo getCartCount() !== 1 ? 's' : ''; ?> in your cart</p>
  </section>

  <!-- CART -->
  <?php if (empty($cart_items)): ?>
    <div class="empty-cart">
      <i class="fas fa-shopping-cart"></i>
      <h2>Your cart is empty</h2>
      <p>Start adding some amazing websites to your cart!</p>
      <a href="index.php">Continue Shopping →</a>
    </div>
  <?php else: ?>
    <div class="cart-container">
      <div class="cart-items">
        <?php foreach ($cart_items as $item): ?>
          <div class="cart-item">
            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
            <div class="cart-item-details">
              <h3><?php echo htmlspecialchars($item['title']); ?></h3>
              <div class="category"><?php echo htmlspecialchars($item['category']); ?></div>
              <div class="price"><?php echo formatPrice($item['price']); ?></div>
            </div>
            <div class="cart-item-actions">
              <a href="website.php?id=<?php echo $item['id']; ?>"><i class="fas fa-eye"></i> View</a>
              <form action="remove_from_cart.php" method="POST">
                <input type="hidden" name="website_id" value="<?php echo $item['id']; ?>">
                <button type="submit"><i class="fas fa-trash"></i> Remove</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="summary-card">
        <h2>Order Summary</h2>
        <div class="summary-row">
          <span>Subtotal (<?php echo getCartCount(); ?> items)</span>
          <span><?php echo formatPrice($cart_total); ?></span>
        </div>
        <div class="summary-row">
          <span>Tax</span>
          <span>$0.00</span>
        </div>
        <div class="summary-row total">
          <span>Total</span>
          <span class="price"><?php echo formatPrice($cart_total); ?></span>
        </div>
        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        <a href="index.php" class="continue-btn">Continue Shopping</a>
        <div class="secure-note">
          <i class="fas fa-lock"></i> Secure checkout with encrypted payments
        </div>
      </div>
    </div>
  <?php endif; ?>

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
