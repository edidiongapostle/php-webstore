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
$anonymous_checkout_enabled = getSetting('anonymous_checkout', '1');
$crypto_payments_enabled = getSetting('crypto_payments', '1');
$pageTitle = "Checkout - " . $site_name;

// Redirect if cart is empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
    $anonymous_checkout = isset($_POST['anonymous_checkout']) ? $_POST['anonymous_checkout'] : '0';

    $email = sanitizeInput($_POST['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email address is required for delivery';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    if ($anonymous_checkout !== '1') {
        $name = sanitizeInput($_POST['name'] ?? '');
        if (empty($name)) {
            $errors['name'] = 'Full name is required';
        }
    }
    
    if (empty($payment_method)) {
        $errors['payment_method'] = 'Payment method is required';
    }
    
    if (!isset($_POST['accept_terms']) || $_POST['accept_terms'] != '1') {
        $errors['accept_terms'] = 'You must accept the Terms of Use to complete your purchase';
    }
    
    if (empty($errors)) {
        $_SESSION['checkout_data'] = $_POST;
        header('Location: payment_instructions.php');
        exit;
    }
}
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
      margin: 0 auto;
    }

    /* CHECKOUT */
    .checkout-container {
      max-width: 1200px;
      margin: 4rem auto;
      padding: 0 2rem;
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 3rem;
    }

    @media (max-width: 900px) {
      .checkout-container {
        grid-template-columns: 1fr;
      }
    }

    .checkout-form {
      display: flex;
      flex-direction: column;
      gap: 2rem;
    }

    .form-card {
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
    }

    .form-card h2 {
      font-family: 'Fraunces', serif;
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      font-weight: 500;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 0.85rem;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-family: inherit;
      font-size: 0.95rem;
      transition: border-color 0.2s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--accent);
    }

    .form-group .error {
      color: #DC2626;
      font-size: 0.85rem;
      margin-top: 0.5rem;
    }

    .form-group .hint {
      color: var(--grey);
      font-size: 0.8rem;
      margin-top: 0.5rem;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .checkbox-group input[type="checkbox"] {
      width: auto;
    }

    .payment-option {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem;
      border: 1px solid var(--border);
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.2s;
    }

    .payment-option:hover {
      background: var(--light);
    }

    .payment-option input {
      width: auto;
    }

    .terms-box {
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
    }

    .terms-box a {
      color: var(--accent);
      text-decoration: none;
    }

    .submit-btn {
      width: 100%;
      background: var(--black);
      color: white;
      padding: 0.85rem 2rem;
      border: none;
      border-radius: 100px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }

    .submit-btn:hover {
      background: #1a1a1a;
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

    .summary-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.75rem;
      font-size: 0.9rem;
    }

    .summary-item .name {
      color: var(--grey);
    }

    .summary-divider {
      border-top: 1px solid var(--border);
      padding-top: 1rem;
      margin-top: 1rem;
    }

    .summary-total {
      display: flex;
      justify-content: space-between;
      font-weight: 700;
      font-size: 1.1rem;
      color: var(--accent);
    }

    .summary-features {
      margin-top: 2rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .summary-features div {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 0.85rem;
      color: var(--grey);
    }

    .summary-features i {
      color: #22C55E;
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

    /* MOBILE OPTIMIZATIONS */
    @media (max-width: 768px) {
      .hero {
        padding: 6rem 1.5rem 3rem;
      }

      .hero h1 {
        font-size: clamp(1.75rem, 5vw, 2.5rem);
      }

      .hero p {
        font-size: 1rem;
      }

      .checkout-container {
        margin: 2rem auto;
        padding: 0 1.5rem;
        gap: 2rem;
      }

      .form-card {
        padding: 1.5rem;
        border-radius: 12px;
      }

      .form-card h2 {
        font-size: 1.25rem;
        margin-bottom: 1rem;
      }

      .form-group {
        margin-bottom: 1rem;
      }

      .form-group label {
        font-size: 0.85rem;
      }

      .form-group input,
      .form-group textarea {
        padding: 0.75rem;
        font-size: 0.9rem;
      }

      .payment-option {
        padding: 0.75rem;
      }

      .terms-box {
        padding: 1rem;
      }

      .summary-card {
        padding: 1.5rem;
        border-radius: 12px;
      }

      .summary-card h2 {
        font-size: 1.25rem;
        margin-bottom: 1rem;
      }

      .summary-item {
        font-size: 0.85rem;
      }

      .summary-total {
        font-size: 1rem;
      }

      .summary-features div {
        font-size: 0.8rem;
      }

      .submit-btn {
        padding: 0.75rem 1.5rem;
      }

      .footer-links {
        gap: 1rem;
      }

      .footer-links a {
        font-size: 0.85rem;
      }
    }

    @media (max-width: 480px) {
      .form-card h2,
      .summary-card h2 {
        font-size: 1.15rem;
      }
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

  <!-- HERO -->
  <section class="hero">
    <h1>Checkout</h1>
    <p>Complete your purchase securely and quickly</p>
  </section>

  <!-- CHECKOUT -->
  <div class="checkout-container">
    <form method="POST" class="checkout-form">
      <!-- Billing Information -->
      <div class="form-card">
        <h2>Billing Information</h2>
        
        <?php if ($anonymous_checkout_enabled === '1'): ?>
        <div class="form-group checkbox-group">
          <input type="checkbox" name="anonymous_checkout" value="1" id="anonymous_checkout"
                 onchange="toggleAnonymousFields(this.checked)" <?php echo (($_POST['anonymous_checkout'] ?? '') === '1' ? 'checked' : ''); ?>>
          <label for="anonymous_checkout" style="margin:0">Checkout Anonymously</label>
        </div>
        <p class="hint">Hide your name (email still required for delivery)</p>
        <?php endif; ?>

        <div class="form-group" id="name-field">
          <label>Full Name *</label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
          <?php if (isset($errors['name'])): ?>
            <p class="error"><?php echo $errors['name']; ?></p>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
          <?php if (isset($errors['email'])): ?>
            <p class="error"><?php echo $errors['email']; ?></p>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
        </div>
      </div>

      <!-- Payment Method -->
      <div class="form-card">
        <h2>Payment Method</h2>
        
        <div class="form-group">
          <?php 
          $payment_methods = getPaymentMethods();
          foreach ($payment_methods as $payment):
            if ($payment['type'] === 'crypto' && $crypto_payments_enabled !== '1') {
              continue;
            }
            $config = json_decode($payment['config_data'], true) ?: [];
          ?>
            <label class="payment-option">
              <input type="radio" name="payment_method" value="<?php echo $payment['type']; ?>"
                     <?php echo (($_POST['payment_method'] ?? '') === $payment['type'] ? 'checked' : ''); ?>>
              <i class="<?php echo $payment['icon']; ?>"></i>
              <div>
                <span style="font-weight:500"><?php echo htmlspecialchars($payment['name']); ?></span>
                <?php if ($payment['type'] === 'crypto' && !empty($config)): ?>
                  <div style="font-size:0.75rem;color:var(--grey);margin-top:0.25rem">
                    <?php 
                    $enabled_coins = $config['enabled_coins'] ?? [];
                    $coins = [];
                    if (!empty($config['btc_address']) && in_array('BTC', $enabled_coins)) $coins[] = 'BTC';
                    if (!empty($config['eth_address']) && in_array('ETH', $enabled_coins)) $coins[] = 'ETH';
                    if (!empty($config['ltc_address']) && in_array('LTC', $enabled_coins)) $coins[] = 'LTC';
                    echo !empty($coins) ? 'Available: ' . implode(', ', $coins) : 'No coins enabled';
                    ?>
                  </div>
                <?php endif; ?>
              </div>
            </label>
          <?php endforeach; ?>
          <?php if (isset($errors['payment_method'])): ?>
            <p class="error"><?php echo $errors['payment_method']; ?></p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Order Notes -->
      <div class="form-card">
        <h2>Order Notes (Optional)</h2>
        <div class="form-group">
          <textarea name="notes" rows="4" placeholder="Any special instructions or notes..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
        </div>
      </div>

      <!-- Terms -->
      <div class="form-card">
        <h2>Terms of Use</h2>
        <div class="terms-box">
          <p style="margin-bottom:1rem">
            By completing this purchase, you agree to our <a href="terms.php" target="_blank">Terms of Use</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>.
          </p>
          <ul style="font-size:0.8rem;color:var(--grey);list-style:none;padding:0">
            <li>• Products are delivered as digital downloads</li>
            <li>• 30-day money-back guarantee applies</li>
            <li>• License terms vary by product type</li>
            <li>• Anonymous purchases available with crypto</li>
          </ul>
        </div>
        <div class="form-group checkbox-group" style="margin-top:1.5rem">
          <input type="checkbox" name="accept_terms" value="1" id="accept_terms">
          <label for="accept_terms" style="margin:0">I have read and agree to the <a href="terms.php" target="_blank">Terms of Use</a></label>
        </div>
        <?php if (isset($errors['accept_terms'])): ?>
          <p class="error"><?php echo $errors['accept_terms']; ?></p>
        <?php endif; ?>
      </div>

      <button type="submit" class="submit-btn">
        Continue to Payment →
      </button>
    </form>

    <!-- Order Summary -->
    <div class="summary-card">
      <h2>Order Summary</h2>
      
      <?php foreach ($cart_items as $item): ?>
        <div class="summary-item">
          <span class="name"><?php echo htmlspecialchars($item['title']); ?></span>
          <span><?php echo formatPrice($item['price']); ?></span>
        </div>
      <?php endforeach; ?>
      
      <div class="summary-divider">
        <div class="summary-item">
          <span class="name">Subtotal</span>
          <span><?php echo formatPrice($cart_total); ?></span>
        </div>
        <div class="summary-item">
          <span class="name">Tax</span>
          <span>$0.00</span>
        </div>
        <div class="summary-total">
          <span>Total</span>
          <span><?php echo formatPrice($cart_total); ?></span>
        </div>
      </div>

      <div class="summary-features">
        <div><i class="fas fa-check-circle"></i> Instant download after purchase</div>
        <div><i class="fas fa-check-circle"></i> 30 days money-back guarantee</div>
        <div><i class="fas fa-check-circle"></i> 24/7 customer support</div>
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

    function toggleAnonymousFields(isAnonymous) {
      const nameField = document.getElementById('name-field');
      if (nameField) {
        nameField.style.display = isAnonymous ? 'none' : 'block';
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const anonymousCheckbox = document.getElementById('anonymous_checkout');
      if (anonymousCheckbox && anonymousCheckbox.checked) {
        toggleAnonymousFields(true);
      }
    });
  </script>
</body>
</html>
