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

// Get checkout data from session
if (!isset($_SESSION['checkout_data'])) {
    header('Location: checkout.php');
    exit;
}

$checkout_data = $_SESSION['checkout_data'];
$cart_items = getCartItems();
$cart_total = getCartTotal();
$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$errors = [];
$success = false;
$order_id = null;
$order_reference = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_reference = sanitizeInput($_POST['transaction_reference'] ?? '');
    $anonymous_checkout = isset($checkout_data['anonymous_checkout']) ? $checkout_data['anonymous_checkout'] : '0';
    $name = sanitizeInput($checkout_data['name'] ?? '');
    $email = sanitizeInput($checkout_data['email'] ?? '');
    $phone = sanitizeInput($checkout_data['phone'] ?? '');
    $notes = sanitizeInput($checkout_data['notes'] ?? '');
    $payment_method = sanitizeInput($checkout_data['payment_method'] ?? '');

    // Validate transaction reference
    if (empty($transaction_reference)) {
        $errors['transaction_reference'] = 'Transaction reference is required';
    }

    // Handle screenshot upload
    $screenshot_path = '';
    if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] == 0) {
        $file = $_FILES['payment_screenshot'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $errors['payment_screenshot'] = 'Only JPG, PNG, and GIF images are allowed';
        } elseif ($file['size'] > $max_size) {
            $errors['payment_screenshot'] = 'File size must be less than 5MB';
        } else {
            $upload_dir = __DIR__ . '/uploads/payment_proofs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $filename = 'payment_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $screenshot_path = 'uploads/payment_proofs/' . $filename;
            } else {
                $errors['payment_screenshot'] = 'Failed to upload screenshot';
            }
        }
    } else {
        $errors['payment_screenshot'] = 'Payment screenshot is required';
    }

    if (empty($errors)) {
        // If not anonymous but name/email are empty, treat as anonymous
        if ($anonymous_checkout !== '1' && (empty($name) || empty($email))) {
            $anonymous_checkout = '1';
        }

        try {
            $customer_data = [
                'name' => $anonymous_checkout === '1' ? 'Anonymous Customer' : $name,
                'email' => $anonymous_checkout === '1' ? 'anonymous@webstore.com' : $email,
                'phone' => $phone,
                'total' => $cart_total,
                'anonymous_checkout' => $anonymous_checkout === '1' ? 1 : 0
            ];

            // Use the order reference that was already generated and shown to customer
            $order_reference = $_SESSION['order_reference'] ?? generateOrderReference();

            // Check if this reference already exists in database (from a previous failed attempt)
            $stmt = $conn->prepare("SELECT id FROM orders WHERE order_reference = ?");
            $stmt->execute([$order_reference]);
            if ($stmt->fetch()) {
                // Reference already exists, generate a new one
                $order_reference = generateOrderReference();
            }

            // Create order with the pre-generated reference
            $order_id = createOrderWithReference($customer_data, $cart_items, $order_reference);

            if ($order_id) {
                // Update order with payment details
                $stmt = $conn->prepare("UPDATE orders SET status = 'awaiting_verification', payment_method = ?, transaction_reference = ?, payment_screenshot = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$payment_method, $transaction_reference, $screenshot_path, $order_id]);

                // Clear cart and session
                clearCart();
                unset($_SESSION['checkout_data']);
                unset($_SESSION['order_reference']);
                $_SESSION['order_success'] = true;
                $_SESSION['order_id'] = $order_id;
                $_SESSION['order_reference'] = $order_reference;
                $_SESSION['payment_method'] = $payment_method;

                // Send order confirmation email
                $email_data = [
                    'site_name' => $site_name,
                    'order_reference' => $order_reference,
                    'total_amount' => formatPrice($cart_total),
                    'payment_method' => $payment_method,
                    'current_year' => date('Y')
                ];
                sendEmail($customer_data['email'], "Order Confirmation - {$order_reference}", 'order_confirmation', $email_data);

                $success = true;
            } else {
                $errors['general'] = 'Failed to create order. Please try again.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Payment Confirmation - " . $site_name;
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

    .notes-box {
      background: #FEF3C7;
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
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

    .action-buttons button,
    .action-buttons a {
      flex: 1;
      padding: 0.85rem 2rem;
      border-radius: 100px;
      font-weight: 600;
      text-decoration: none;
      text-align: center;
      transition: all 0.2s;
      border: none;
      cursor: pointer;
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

    /* SUCCESS */
    .success-box {
      background: #DCFCE7;
      padding: 2rem;
      border-radius: 16px;
      text-align: center;
      margin-bottom: 2rem;
    }

    .success-icon {
      font-size: 3rem;
      color: #22C55E;
      margin-bottom: 1rem;
    }

    .success-box h2 {
      font-family: 'Fraunces', serif;
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: #166534;
    }

    .success-box p {
      color: #166534;
      margin-bottom: 1.5rem;
    }

    .success-box .reference {
      background: var(--white);
      padding: 1rem;
      border-radius: 8px;
      border: 1px solid #86EFAC;
    }

    .success-box .reference .label {
      font-size: 0.85rem;
      color: var(--grey);
      margin-bottom: 0.25rem;
    }

    .success-box .reference .value {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--accent);
    }

    .steps-box {
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
    }

    .steps-box h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .step {
      display: flex;
      align-items: start;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .step-number {
      width: 32px;
      height: 32px;
      background: var(--accent);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      flex-shrink: 0;
    }

    .step-content h4 {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .step-content p {
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

      .summary-item {
        font-size: 0.9rem;
      }

      .summary-total {
        font-size: 1rem;
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

      .action-buttons button,
      .action-buttons a {
        width: 100%;
      }

      .success-box {
        padding: 1.5rem;
      }

      .success-icon {
        font-size: 2.5rem;
      }

      .success-box h2 {
        font-size: 1.5rem;
      }

      .success-box .reference .value {
        font-size: 1.1rem;
        line-height: 1.4;
      }

      .steps-box {
        padding: 1rem;
      }

      .steps-box h3 {
        font-size: 1.1rem;
      }

      .step-number {
        width: 28px;
        height: 28px;
        font-size: 0.9rem;
      }

      .step-content h4 {
        font-size: 0.95rem;
      }

      .step-content p {
        font-size: 0.85rem;
      }

      .footer-links {
        gap: 1rem;
      }

      .footer-links a {
        font-size: 0.85rem;
      }
    }

    @media (max-width: 480px) {
      .card h2 {
        font-size: 1.15rem;
      }

      .success-box .reference .value {
        font-size: 1rem;
      }

      .steps-box h3 {
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
    <h1>Payment Confirmation</h1>
    <p>Submit your payment details to complete your order</p>
  </section>

  <!-- CONTENT -->
  <div class="content">
    <?php if ($success): ?>
      <!-- Success Message -->
      <div class="success-box">
        <div class="success-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h2>Payment Submitted Successfully!</h2>
        <p>Your order is awaiting verification.</p>
        <div class="reference">
          <div class="label">Order Reference:</div>
          <div class="value"><?php echo htmlspecialchars($order_reference); ?></div>
          <div style="display:flex;gap:0.5rem;margin-top:0.75rem">
            <button onclick="copyToClipboard(this)" data-address="<?php echo htmlspecialchars($order_reference); ?>" class="copy-btn" style="padding:0.5rem 1rem;font-size:0.85rem;border-radius:6px;background:var(--accent);color:white;border:none;cursor:pointer">
              <i class="fas fa-copy mr-1"></i>Copy Reference
            </button>
          </div>
        </div>
      </div>

      <div class="card">
        <h2>What happens next?</h2>
        <div class="steps-box">
          <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">
              <h4>Payment Verification</h4>
              <p>Our team will verify your payment within 24-48 hours.</p>
            </div>
          </div>
          <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">
              <h4>Order Approval</h4>
              <p>Once verified, your order will be approved and you'll receive a download link.</p>
            </div>
          </div>
          <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">
              <h4>Download Your Purchase</h4>
              <p>Use the download link sent to your email to access your purchase.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="action-buttons">
        <a href="index.php" class="primary">
          <i class="fas fa-home mr-2"></i>Return to Home
        </a>
      </div>

    <?php else: ?>
      <!-- Payment Confirmation Form -->
      <?php if (isset($errors['general'])): ?>
        <div style="background:#FEE2E2;padding:1rem;border-radius:8px;border:1px solid #FCA5A5;margin-bottom:2rem;color:#991B1B">
          <?php echo $errors['general']; ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <h2>Order Summary</h2>
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

      <form method="POST" enctype="multipart/form-data" class="card">
        <h2>Submit Payment Details</h2>

        <div class="form-group">
          <label>Transaction Reference *</label>
          <input type="text" name="transaction_reference" value="<?php echo htmlspecialchars($_POST['transaction_reference'] ?? ''); ?>" placeholder="Enter transaction ID or reference number">
          <?php if (isset($errors['transaction_reference'])): ?>
            <p class="error"><?php echo $errors['transaction_reference']; ?></p>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label>Payment Screenshot *</label>
          <input type="file" name="payment_screenshot" accept="image/jpeg,image/png,image/jpg,image/gif">
          <p class="hint">Upload a screenshot of your payment confirmation (JPG, PNG, GIF - Max 5MB)</p>
          <?php if (isset($errors['payment_screenshot'])): ?>
            <p class="error"><?php echo $errors['payment_screenshot']; ?></p>
          <?php endif; ?>
        </div>

        <div class="notes-box">
          <h4>Before submitting:</h4>
          <ul>
            <li>• Ensure you have made the payment</li>
            <li>• Take a clear screenshot of the payment confirmation</li>
            <li>• Copy the transaction reference from your payment</li>
            <li>• Double-check the amount matches your order total</li>
          </ul>
        </div>

        <div class="action-buttons">
          <button type="submit" class="primary">
            <i class="fas fa-paper-plane mr-2"></i>Submit Payment Details
          </button>
          <a href="payment_instructions.php" class="secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Instructions
          </a>
        </div>
      </form>
    <?php endif; ?>
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
