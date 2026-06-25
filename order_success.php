<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['order_success']) || !$_SESSION['order_success']) {
    header('Location: index.php');
    exit;
}

$order_id = $_SESSION['order_id'] ?? 0;
$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

unset($_SESSION['order_success']);
unset($_SESSION['order_id']);

$pageTitle = "Order Successful - " . $site_name;
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

    /* SUCCESS */
    .success-container {
      max-width: 800px;
      margin: 4rem auto;
      padding: 0 2rem;
    }

    .success-card {
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 3rem;
      text-align: center;
      background: var(--white);
    }

    .success-icon {
      width: 80px;
      height: 80px;
      background: #DCFCE7;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
    }

    .success-icon i {
      font-size: 2.5rem;
      color: #22C55E;
    }

    .success-card h2 {
      font-family: 'Fraunces', serif;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.75rem;
    }

    .success-card p {
      color: var(--grey);
      margin-bottom: 2rem;
    }

    .order-details {
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 2rem;
      text-align: left;
    }

    .order-details h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .order-detail-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.75rem;
    }

    .order-detail-row .label {
      color: var(--grey);
    }

    .order-detail-row .value {
      font-weight: 500;
    }

    .order-detail-row .value.status {
      color: #EAB308;
    }

    .next-steps {
      background: #EFF6FF;
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 2rem;
      text-align: left;
    }

    .next-steps h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--accent);
    }

    .next-steps .step {
      display: flex;
      align-items: start;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
      font-size: 0.9rem;
      color: var(--black);
    }

    .next-steps .step i {
      color: var(--accent);
      margin-top: 0.15rem;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
    }

    .action-buttons a {
      padding: 0.85rem 2rem;
      border-radius: 100px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }

    .action-buttons .primary {
      background: var(--black);
      color: white;
    }

    .action-buttons .primary:hover {
      background: #1a1a1a;
    }

    .action-buttons .secondary {
      background: var(--light);
      color: var(--black);
    }

    .action-buttons .secondary:hover {
      background: #E4E4E0;
    }

    /* FEATURES */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-top: 3rem;
    }

    .feature-card {
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
      text-align: center;
      background: var(--white);
    }

    .feature-card i {
      font-size: 2rem;
      color: var(--accent);
      margin-bottom: 1rem;
    }

    .feature-card h4 {
      font-family: 'Fraunces', serif;
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .feature-card p {
      font-size: 0.9rem;
      color: var(--grey);
      margin: 0;
    }

    /* FOOTER */
    footer {
      background: var(--black);
      color: var(--white);
      padding: 3rem 2rem;
      text-align: center;
      margin-top: 4rem;
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
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="blog.php">Blog</a></li>
      <li><a href="contact.php">Contact</a></li>
      <li><a href="cart.php" class="relative">
        <i class="fas fa-shopping-cart"></i>
      </a></li>
      <li><a href="admin/login.php" class="nav-cta">Admin</a></li>
    </ul>
    <button class="nav-hamburger" id="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <h1>Order Successful!</h1>
    <p>Thank you for your purchase. Your order has been received and is being processed.</p>
  </section>

  <!-- SUCCESS -->
  <div class="success-container">
    <div class="success-card">
      <div class="success-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <h2>Thank You!</h2>
      <p>Your order has been successfully placed.</p>

      <div class="order-details">
        <h3>Order Details</h3>
        <div class="order-detail-row">
          <span class="label">Order Number:</span>
          <span class="value">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
        </div>
        <div class="order-detail-row">
          <span class="label">Date:</span>
          <span class="value"><?php echo date('F j, Y, g:i a'); ?></span>
        </div>
        <div class="order-detail-row">
          <span class="label">Status:</span>
          <span class="value status">Processing</span>
        </div>
      </div>

      <div class="next-steps">
        <h3>What happens next?</h3>
        <div class="step">
          <i class="fas fa-envelope"></i>
          <span>You'll receive an order confirmation email shortly</span>
        </div>
        <div class="step">
          <i class="fas fa-download"></i>
          <span>Download links will be sent once payment is confirmed</span>
        </div>
        <div class="step">
          <i class="fas fa-headset"></i>
          <span>Our support team will contact you for installation assistance</span>
        </div>
      </div>

      <div class="action-buttons">
        <a href="index.php" class="primary">
          <i class="fas fa-home mr-2"></i>Back to Home
        </a>
        <a href="mailto:support@webstore.com" class="secondary">
          <i class="fas fa-envelope mr-2"></i>Contact Support
        </a>
      </div>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <i class="fas fa-shield-alt"></i>
        <h4>Secure Payment</h4>
        <p>Your payment information is encrypted and secure</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-redo"></i>
        <h4>30-Day Guarantee</h4>
        <p>Full refund within 30 days if not satisfied</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-headset"></i>
        <h4>24/7 Support</h4>
        <p>Round-the-clock customer support available</p>
      </div>
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
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
    navLinks.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => navLinks.classList.remove('open'));
    });
  </script>
</body>
</html>
