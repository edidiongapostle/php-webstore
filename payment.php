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

// Get payment method from session or form
$payment_method = $_POST['payment_method'] ?? $_SESSION['checkout_data']['payment_method'] ?? '';
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

// Store payment method in session
$_SESSION['payment_method'] = $payment_method;

// Get payment method details
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
$pageTitle = "Payment - " . $site_name;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // Process the order
    $anonymous_checkout = ($_POST['anonymous_checkout'] ?? '0') === '1' ? 1 : 0;
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');

    // If not anonymous but name/email are empty, treat as anonymous
    if (!$anonymous_checkout && (empty($name) || empty($email))) {
        $anonymous_checkout = 1;
    }

    try {
        $customer_data = [
            'name' => $anonymous_checkout ? 'Anonymous Customer' : $name,
            'email' => $anonymous_checkout ? 'anonymous@webstore.com' : $email,
            'phone' => $phone,
            'total' => $cart_total,
            'anonymous_checkout' => $anonymous_checkout
        ];
        
        $order_id = createOrder($customer_data, $cart_items);
        
        if ($order_id) {
            clearCart();
            $_SESSION['order_success'] = true;
            $_SESSION['order_id'] = $order_id;
            $_SESSION['payment_method'] = $payment_method;
            header('Location: order_success.php');
            exit;
        } else {
            $errors['general'] = 'Failed to create order. Please try again.';
        }
    } catch (Exception $e) {
        $errors['general'] = 'An error occurred. Please try again.';
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
      max-width: 1200px;
      margin: 4rem auto;
      padding: 0 2rem;
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 3rem;
    }

    @media (max-width: 900px) {
      .content {
        grid-template-columns: 1fr;
      }
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

    .payment-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .payment-header i {
      font-size: 2.5rem;
      color: var(--accent);
    }

    .payment-header h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 600;
    }

    .payment-header p {
      color: var(--grey);
      margin: 0;
    }

    .payment-box {
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
    }

    .payment-box h4 {
      font-family: 'Fraunces', serif;
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
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

    .crypto-card h5 {
      font-weight: 600;
      margin-bottom: 0.75rem;
    }

    .crypto-card .address {
      font-size: 0.8rem;
      word-break: break-all;
      margin-bottom: 0.5rem;
      color: var(--grey);
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

    .crypto-card img {
      width: 100px;
      height: 100px;
      margin: 0.5rem auto;
      display: block;
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

    .confirm-box {
      background: #FEF3C7;
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
    }

    .confirm-box h4 {
      font-weight: 600;
      margin-bottom: 0.75rem;
      color: #92400E;
    }

    .confirm-box p {
      color: #92400E;
      margin-bottom: 1rem;
      font-size: 0.9rem;
    }

    .confirm-box label {
      display: flex;
      align-items: start;
      gap: 0.75rem;
      font-size: 0.9rem;
      color: #92400E;
    }

    .confirm-box input {
      margin-top: 0.25rem;
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
    <h1>Payment Details</h1>
    <p>Complete your payment to finalize your order</p>
  </section>

  <!-- CONTENT -->
  <div class="content">
    <!-- Payment Method Details -->
    <div class="card">
      <div class="payment-header">
        <i class="<?php echo $selected_payment['icon']; ?>"></i>
        <div>
          <h3><?php echo htmlspecialchars($selected_payment['name']); ?></h3>
          <p>Complete your payment using this method</p>
        </div>
      </div>

      <?php if (isset($errors['general'])): ?>
        <div style="background:#FEE2E2;padding:1rem;border-radius:8px;border:1px solid #FCA5A5;margin-bottom:1.5rem;color:#991B1B">
          <?php echo $errors['general']; ?>
        </div>
      <?php endif; ?>

      <!-- Payment Method Specific Instructions -->
      <?php if ($selected_payment['type'] === 'credit_card'): ?>
        <div class="payment-box">
          <h4>Credit/Debit Card Payment</h4>
          <p>Your card will be processed securely via <?php echo htmlspecialchars($config['processor'] ?? 'Stripe'); ?>.</p>
          <?php if (!empty($config['sandbox']) && $config['sandbox'] == '1'): ?>
            <p style="color:#EA580C;font-weight:500">This is a test transaction (sandbox mode).</p>
          <?php endif; ?>
          <p style="font-size:0.85rem;color:var(--grey)"><i class="fas fa-lock mr-1"></i> Secure payment processing</p>
        </div>
      <?php endif; ?>

      <?php if ($selected_payment['type'] === 'paypal'): ?>
        <div class="payment-box">
          <h4>PayPal Payment</h4>
          <p>You will be redirected to PayPal to complete your payment.</p>
          <?php if (!empty($config['sandbox']) && $config['sandbox'] == '1'): ?>
            <p style="color:#EA580C;font-weight:500">This is a test transaction (sandbox mode).</p>
          <?php endif; ?>
          <p style="font-size:0.85rem;color:var(--grey)"><i class="fab fa-paypal mr-1"></i> PayPal secure checkout</p>
        </div>
      <?php endif; ?>

      <?php if ($selected_payment['type'] === 'bank_transfer'): ?>
        <div class="payment-box">
          <h4>Bank Transfer</h4>
          <p>Please transfer the total amount to our bank account:</p>
          <div style="background:var(--white);padding:1rem;border-radius:8px;border:1px solid var(--border);margin-top:1rem">
            <p style="font-weight:500;margin-bottom:0.5rem">Bank: <?php echo htmlspecialchars($config['bank_name'] ?? 'Your Bank'); ?></p>
            <p style="font-weight:500;margin-bottom:0.5rem">Account Number: <?php echo htmlspecialchars($config['account_number'] ?? '****1234'); ?></p>
            <?php if (!empty($config['routing_number'])): ?>
              <p style="font-weight:500;margin-bottom:0.5rem">Routing Number: <?php echo htmlspecialchars($config['routing_number']); ?></p>
            <?php endif; ?>
            <p style="font-size:0.9rem;color:var(--grey)">Reference: Your order ID</p>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($selected_payment['type'] === 'crypto'): ?>
        <div class="payment-box">
          <h4>Cryptocurrency Payment</h4>
          <p>Send cryptocurrency to the addresses below or scan the QR codes:</p>
          <div class="crypto-grid">
            <?php 
            $enabled_coins = $config['enabled_coins'] ?? [];
            
            if (!empty($config['btc_address']) && in_array('BTC', $enabled_coins)): ?>
              <div class="crypto-card">
                <h5>Bitcoin (BTC)</h5>
                <p class="address"><?php echo htmlspecialchars($config['btc_address']); ?></p>
                <button onclick="copyAddress('<?php echo htmlspecialchars($config['btc_address']); ?>')" class="copy-btn">Copy Address</button>
                <?php if (!empty($config['btc_qr_code'])): ?>
                  <img src="<?php echo htmlspecialchars($config['btc_qr_code']); ?>" alt="Bitcoin QR Code">
                <?php else: ?>
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($config['btc_address']); ?>" alt="Bitcoin QR Code">
                <?php endif; ?>
              </div>
            <?php endif; ?>
            
            <?php if (!empty($config['eth_address']) && in_array('ETH', $enabled_coins)): ?>
              <div class="crypto-card">
                <h5>Ethereum (ETH)</h5>
                <p class="address"><?php echo htmlspecialchars($config['eth_address']); ?></p>
                <button onclick="copyAddress('<?php echo htmlspecialchars($config['eth_address']); ?>')" class="copy-btn">Copy Address</button>
                <?php if (!empty($config['eth_qr_code'])): ?>
                  <img src="<?php echo htmlspecialchars($config['eth_qr_code']); ?>" alt="Ethereum QR Code">
                <?php else: ?>
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($config['eth_address']); ?>" alt="Ethereum QR Code">
                <?php endif; ?>
              </div>
            <?php endif; ?>
            
            <?php if (!empty($config['ltc_address']) && in_array('LTC', $enabled_coins)): ?>
              <div class="crypto-card">
                <h5>Litecoin (LTC)</h5>
                <p class="address"><?php echo htmlspecialchars($config['ltc_address']); ?></p>
                <button onclick="copyAddress('<?php echo htmlspecialchars($config['ltc_address']); ?>')" class="copy-btn">Copy Address</button>
                <?php if (!empty($config['ltc_qr_code'])): ?>
                  <img src="<?php echo htmlspecialchars($config['ltc_qr_code']); ?>" alt="Litecoin QR Code">
                <?php else: ?>
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($config['ltc_address']); ?>" alt="Litecoin QR Code">
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Order Summary & Confirmation -->
    <div class="card">
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

      <form method="POST">
        <input type="hidden" name="confirm_payment" value="1">
        <input type="hidden" name="payment_method" value="<?php echo $payment_method; ?>">
        <input type="hidden" name="anonymous_checkout" value="<?php echo $_SESSION['checkout_data']['anonymous_checkout'] ?? '0'; ?>">
        <input type="hidden" name="name" value="<?php echo htmlspecialchars($_SESSION['checkout_data']['name'] ?? ''); ?>">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['checkout_data']['email'] ?? ''); ?>">
        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($_SESSION['checkout_data']['phone'] ?? ''); ?>">
        <input type="hidden" name="notes" value="<?php echo htmlspecialchars($_SESSION['checkout_data']['notes'] ?? ''); ?>">
        
        <div class="confirm-box">
          <h4>Confirmation Required</h4>
          <p>Please review your payment details and confirm to complete your order.</p>
          <label>
            <input type="checkbox" name="confirm" required>
            <span>I have reviewed the payment details and confirm my order</span>
          </label>
        </div>
        
        <button type="submit" class="submit-btn">
          <i class="fas fa-check mr-2"></i>Confirm Order & Complete Payment
        </button>
      </form>
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

    function copyAddress(address) {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(address).then(() => showCopied(), () => fallbackCopy(address));
      } else {
        fallbackCopy(address);
      }
    }

    function fallbackCopy(text) {
      const textArea = document.createElement('textarea');
      textArea.value = text;
      textArea.style.position = 'fixed';
      textArea.style.left = '-9999px';
      document.body.appendChild(textArea);
      textArea.select();
      try {
        const successful = document.execCommand('copy');
        if (successful) showCopied();
        else alert('Failed to copy. Please copy manually.');
      } catch (err) {
        alert('Failed to copy. Please copy manually.');
      }
      document.body.removeChild(textArea);
    }

    function showCopied() {
      const button = event.target;
      const originalText = button.innerHTML;
      button.innerHTML = 'Copied!';
      button.style.background = '#22C55E';
      setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = 'var(--accent)';
      }, 2000);
    }

    document.querySelector('form').addEventListener('submit', function(e) {
      const confirmCheckbox = document.querySelector('input[name="confirm"]');
      if (!confirmCheckbox.checked) {
        e.preventDefault();
        alert('Please confirm that you have reviewed the payment details');
        confirmCheckbox.focus();
      }
    });
  </script>
</body>
</html>
