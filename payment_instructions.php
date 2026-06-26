<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$maintenance_mode = getSetting('maintenance_mode', '0');
if ($maintenance_mode === '1' && !isset($_SESSION['admin_logged_in'])) {
    http_response_code(503);
    include 'maintenance.php';
    exit;
}

if (!isset($_SESSION['checkout_data'])) {
    header('Location: checkout.php');
    exit;
}

$checkout_data = $_SESSION['checkout_data'];
$payment_method = $checkout_data['payment_method'] ?? '';
$cart_items = getCartItems();
$cart_total = getCartTotal();
$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

if (empty($payment_method) || empty($cart_items)) {
    header('Location: checkout.php');
    exit;
}

$payment_methods = getPaymentMethods();
$selected_payment = null;
foreach ($payment_methods as $payment) {
    if ($payment['type'] === $payment_method) {
        $selected_payment = $payment;
        break;
    }
}

if (!$selected_payment) {
    header('Location: checkout.php');
    exit;
}

$config = json_decode($selected_payment['config_data'], true) ?: [];

if (!isset($_SESSION['order_reference'])) {
    $_SESSION['order_reference'] = generateOrderReference();
}
$order_reference = $_SESSION['order_reference'];

$pageTitle = "Payment Instructions - " . $site_name;
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

    /* CONTENT */
    .content {
      max-width: 800px;
      margin: 4rem auto;
      padding: 0 2rem;
    }

    .card {
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
      margin-bottom: 2rem;
    }

    .card h2 {
      font-family: 'Fraunces', serif;
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
    }

    .reference-box {
      background: #EFF6FF;
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
    }

    .reference-box .label {
      font-weight: 500;
      margin-bottom: 0.5rem;
    }

    .reference-box .value {
      font-family: 'Fraunces', serif;
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--accent);
      word-break: break-all;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.75rem;
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

    .payment-box {
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
    }

    .payment-box h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .payment-box p {
      color: var(--grey);
      margin-bottom: 1rem;
    }

    .crypto-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
    }

    .crypto-card {
      background: var(--white);
      padding: 1rem;
      border-radius: 8px;
      border: 1px solid var(--border);
    }

    .crypto-card .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
    }

    .crypto-card .header h4 {
      font-weight: 600;
      font-size: 0.95rem;
    }

    .crypto-card .copy-btn {
      background: var(--accent);
      color: white;
      border: none;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.75rem;
      cursor: pointer;
    }

    .crypto-card .address {
      font-size: 0.8rem;
      word-break: break-all;
      margin-bottom: 0.5rem;
      color: var(--grey);
    }

    .crypto-card img {
      width: 100px;
      height: 100px;
      margin: 0 auto;
      display: block;
    }

    .notes-box {
      background: #FEF3C7;
      padding: 1.5rem;
      border-radius: 12px;
      margin-top: 1.5rem;
    }

    .notes-box h4 {
      font-weight: 600;
      margin-bottom: 0.75rem;
      color: #92400E;
    }

    .notes-box ul {
      list-style: none;
      padding: 0;
      color: #92400E;
      font-size: 0.9rem;
    }

    .notes-box li {
      margin-bottom: 0.5rem;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .action-buttons a {
      flex: 1;
      padding: 0.85rem 2rem;
      border-radius: 100px;
      font-weight: 600;
      text-decoration: none;
      text-align: center;
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

      .content {
        margin: 2rem auto;
        padding: 0 1.5rem;
      }

      .card {
        padding: 1.5rem;
        border-radius: 12px;
      }

      .card h2 {
        font-size: 1.25rem;
        margin-bottom: 1rem;
      }

      .reference-box {
        padding: 1rem;
        border-radius: 8px;
      }

      .reference-box .label {
        font-size: 0.9rem;
      }

      .reference-box .value {
        font-size: 1.1rem;
        line-height: 1.4;
      }

      .reference-box p {
        font-size: 0.8rem;
      }

      .summary-item {
        font-size: 0.9rem;
      }

      .summary-total {
        font-size: 1rem;
      }

      .payment-box {
        padding: 1rem;
        border-radius: 8px;
      }

      .payment-box h3 {
        font-size: 1.1rem;
      }

      .payment-box p {
        font-size: 0.9rem;
      }

      .crypto-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
      }

      .crypto-card {
        padding: 0.75rem;
      }

      .crypto-card .header h4 {
        font-size: 0.9rem;
      }

      .crypto-card .address {
        font-size: 0.75rem;
      }

      .crypto-card img {
        width: 80px;
        height: 80px;
      }

      .notes-box {
        padding: 1rem;
        border-radius: 8px;
      }

      .notes-box h4 {
        font-size: 0.95rem;
      }

      .notes-box ul {
        font-size: 0.85rem;
      }

      .action-buttons {
        flex-direction: column;
      }

      .action-buttons a {
        width: 100%;
      }

      .footer-links {
        gap: 1rem;
      }

      .footer-links a {
        font-size: 0.85rem;
      }
    }

    @media (max-width: 480px) {
      .reference-box .value {
        font-size: 1rem;
      }

      .card h2 {
        font-size: 1.15rem;
      }

      .payment-box h3 {
        font-size: 1rem;
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
    <h1>Payment Instructions</h1>
    <p>Follow the instructions below to complete your payment</p>
  </section>

  <!-- CONTENT -->
  <div class="content">
    <!-- Order Summary -->
    <div class="card">
      <h2>Order Summary</h2>
      <div class="reference-box">
        <div class="label">Your Order Reference:</div>
        <div class="value"><?php echo htmlspecialchars($order_reference); ?></div>
        <div style="display:flex;gap:0.5rem;margin-top:0.75rem">
          <button onclick="copyToClipboard(this)" data-address="<?php echo htmlspecialchars($order_reference); ?>" class="copy-btn" style="padding:0.5rem 1rem;font-size:0.85rem;border-radius:6px">
            <i class="fas fa-copy mr-1"></i>Copy Reference
          </button>
        </div>
        <p style="font-size:0.85rem;color:var(--grey);margin-top:0.5rem">Use this reference when making your payment</p>
      </div>
      
      <?php foreach ($cart_items as $item): ?>
        <div class="summary-item">
          <span class="name"><?php echo htmlspecialchars($item['title']); ?></span>
          <span><?php echo formatPrice($item['price']); ?></span>
        </div>
      <?php endforeach; ?>
      
      <div class="summary-divider">
        <div class="summary-total">
          <span>Total</span>
          <span><?php echo formatPrice($cart_total); ?></span>
        </div>
      </div>
    </div>

    <!-- Payment Instructions -->
    <div class="card">
      <h2>
        <i class="<?php echo $selected_payment['icon']; ?> mr-2"></i>
        <?php echo htmlspecialchars($selected_payment['name']); ?> Payment Instructions
      </h2>

      <?php if ($selected_payment['type'] === 'bank_transfer'): ?>
        <div class="payment-box">
          <h3>Bank Transfer Details</h3>
          <p>Please transfer the total amount to our bank account:</p>
          <div style="background:var(--white);padding:1rem;border-radius:8px;border:1px solid var(--border);margin-top:1rem">
            <p style="font-weight:500;margin-bottom:0.5rem">Bank: <?php echo htmlspecialchars($config['bank_name'] ?? 'Your Bank'); ?></p>
            <p style="font-weight:500;margin-bottom:0.5rem">Account Number: <?php echo htmlspecialchars($config['account_number'] ?? '****1234'); ?></p>
            <?php if (!empty($config['routing_number'])): ?>
              <p style="font-weight:500;margin-bottom:0.5rem">Routing Number: <?php echo htmlspecialchars($config['routing_number']); ?></p>
            <?php endif; ?>
            <p style="font-size:0.9rem;color:var(--grey);margin-bottom:0.5rem">Amount: <?php echo formatPrice($cart_total); ?></p>
            <p style="font-size:0.9rem;color:var(--grey);font-weight:600">Reference: <span style="color:var(--accent);word-break:break-all"><?php echo htmlspecialchars($order_reference); ?></span></p>
          </div>
        </div>

      <?php elseif ($selected_payment['type'] === 'crypto'): ?>
        <div class="payment-box">
          <h3>Cryptocurrency Payment</h3>
          <p>Send cryptocurrency to the addresses below or scan the QR codes:</p>
          <div class="crypto-grid">
            <?php
            $enabled_coins = $config['enabled_coins'] ?? [];

            if (!empty($config['btc_address']) && in_array('BTC', $enabled_coins)): ?>
              <div class="crypto-card">
                <div class="header">
                  <h4>Bitcoin (BTC)</h4>
                  <button onclick="copyToClipboard(this)" data-address="<?php echo htmlspecialchars($config['btc_address']); ?>" class="copy-btn">Copy</button>
                </div>
                <p class="address"><?php echo htmlspecialchars($config['btc_address']); ?></p>
                <?php if (!empty($config['btc_qr_code'])): ?>
                  <img src="<?php echo htmlspecialchars($config['btc_qr_code']); ?>" alt="BTC QR Code">
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($config['eth_address']) && in_array('ETH', $enabled_coins)): ?>
              <div class="crypto-card">
                <div class="header">
                  <h4>Ethereum (ETH)</h4>
                  <button onclick="copyToClipboard(this)" data-address="<?php echo htmlspecialchars($config['eth_address']); ?>" class="copy-btn">Copy</button>
                </div>
                <p class="address"><?php echo htmlspecialchars($config['eth_address']); ?></p>
                <?php if (!empty($config['eth_qr_code'])): ?>
                  <img src="<?php echo htmlspecialchars($config['eth_qr_code']); ?>" alt="ETH QR Code">
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($config['ltc_address']) && in_array('LTC', $enabled_coins)): ?>
              <div class="crypto-card">
                <div class="header">
                  <h4>Litecoin (LTC)</h4>
                  <button onclick="copyToClipboard(this)" data-address="<?php echo htmlspecialchars($config['ltc_address']); ?>" class="copy-btn">Copy</button>
                </div>
                <p class="address"><?php echo htmlspecialchars($config['ltc_address']); ?></p>
                <?php if (!empty($config['ltc_qr_code'])): ?>
                  <img src="<?php echo htmlspecialchars($config['ltc_qr_code']); ?>" alt="LTC QR Code">
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
          <p style="font-size:0.9rem;color:var(--grey);margin-top:1rem">Amount: <?php echo formatPrice($cart_total); ?></p>
          <p style="font-size:0.9rem;color:var(--grey);font-weight:600">Reference for memo/note: <span style="color:var(--accent);word-break:break-all"><?php echo htmlspecialchars($order_reference); ?></span></p>
        </div>

      <?php elseif ($selected_payment['type'] === 'paypal'): ?>
        <div class="payment-box">
          <h3>PayPal Payment</h3>
          <p>Send payment to our PayPal account:</p>
          <div style="background:var(--white);padding:1rem;border-radius:8px;border:1px solid var(--border);margin-top:1rem">
            <p style="font-weight:500;margin-bottom:0.5rem">PayPal Email: <?php echo htmlspecialchars($config['processor'] ?? 'paypal@example.com'); ?></p>
            <p style="font-size:0.9rem;color:var(--grey);margin-bottom:0.5rem">Amount: <?php echo formatPrice($cart_total); ?></p>
            <p style="font-size:0.9rem;color:var(--grey);font-weight:600">Reference/Note: <span style="color:var(--accent);word-break:break-all"><?php echo htmlspecialchars($order_reference); ?></span></p>
            <?php if (!empty($config['sandbox']) && $config['sandbox'] == '1'): ?>
              <p style="font-size:0.8rem;color:#EA580C;margin-top:0.5rem">(Sandbox Mode)</p>
            <?php endif; ?>
          </div>
        </div>

      <?php else: ?>
        <div class="payment-box">
          <p>Payment instructions will be provided after order creation.</p>
        </div>
      <?php endif; ?>

      <div class="notes-box">
        <h4>Important Notes</h4>
        <ul>
          <li>• Make sure to send the exact amount shown above</li>
          <li>• Include your order reference in the payment description</li>
          <li>• Save your transaction reference/screenshot for verification</li>
          <li>• Your order will be processed after payment verification</li>
        </ul>
      </div>
    </div>

    <div class="action-buttons">
      <a href="payment_confirmation.php" class="primary">
        <i class="fas fa-check mr-2"></i>I've Made Payment
      </a>
      <a href="checkout.php" class="secondary">
        <i class="fas fa-arrow-left mr-2"></i>Back to Checkout
      </a>
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

    function copyToClipboard(button) {
      const address = button.getAttribute('data-address');
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(address).then(() => showCopied(button), () => fallbackCopy(address, button));
      } else {
        fallbackCopy(address, button);
      }
    }

    function fallbackCopy(text, button) {
      const textArea = document.createElement('textarea');
      textArea.value = text;
      textArea.style.position = 'fixed';
      textArea.style.left = '-9999px';
      document.body.appendChild(textArea);
      textArea.select();
      try {
        const successful = document.execCommand('copy');
        if (successful) showCopied(button);
        else alert('Failed to copy. Please copy manually.');
      } catch (err) {
        alert('Failed to copy. Please copy manually.');
      }
      document.body.removeChild(textArea);
    }

    function showCopied(button) {
      const originalText = button.innerHTML;
      button.innerHTML = 'Copied!';
      button.style.background = '#22C55E';
      setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = 'var(--accent)';
      }, 2000);
    }
  </script>
</body>
</html>
