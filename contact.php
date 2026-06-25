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

$errors = [];
$success = false;
$site_name = getSetting('site_name', 'WebStore');
$site_email = getSetting('site_email', 'admin@webstore.com');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

$pageTitle = "Contact - " . $site_name;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!validateEmail($email)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($subject)) {
        $errors['subject'] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters long';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO contact_messages (name, email, subject, message, created_at) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([$name, $email, $subject, $message]);
            
            $success = true;
            $name = $email = $subject = $message = '';
        } catch (Exception $e) {
            $errors['general'] = 'Failed to send message. Please try again.';
        }
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

    /* CONTACT */
    .contact-container {
      max-width: 1200px;
      margin: 4rem auto;
      padding: 0 2rem;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4rem;
    }

    @media (max-width: 768px) {
      .contact-container {
        grid-template-columns: 1fr;
        gap: 2rem;
      }
    }

    .contact-info h2 {
      font-family: 'Fraunces', serif;
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .contact-info p {
      color: var(--grey);
      margin-bottom: 2rem;
    }

    .info-item {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .info-icon {
      width: 48px;
      height: 48px;
      background: var(--accent);
      color: white;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .info-text h4 {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .info-text p {
      color: var(--grey);
      font-size: 0.9rem;
      margin: 0;
    }

    .faq-section {
      margin-top: 3rem;
    }

    .faq-section h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
    }

    .faq-item {
      margin-bottom: 1rem;
    }

    .faq-item h4 {
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .faq-item p {
      color: var(--grey);
      font-size: 0.9rem;
    }

    /* FORM */
    .form-card {
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
    }

    .form-card h2 {
      font-family: 'Fraunces', serif;
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
    }

    .success-message {
      background: #DCFCE7;
      color: #166534;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
    }

    .error-message {
      background: #FEE2E2;
      color: #991B1B;
      padding: 1rem;
      border-radius: 8px;
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
    .form-group select,
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
    .form-group select:focus,
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

    .form-footer {
      margin-top: 1.5rem;
      padding: 1rem;
      background: var(--light);
      border-radius: 8px;
      text-align: center;
      font-size: 0.85rem;
      color: var(--grey);
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
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="blog.php">Blog</a></li>
      <li><a href="contact.php">Contact</a></li>
      <li><a href="cart.php" class="relative">
        <i class="fas fa-shopping-cart"></i>
        <?php
        $cart_count = getCartCount();
        if ($cart_count > 0):
        ?>
          <span style="position:absolute;top:-4px;right:-8px;background:#EF4444;color:white;border-radius:50%;width:18px;height:18px;font-size:10px;display:flex;align-items:center;justify-content:center;"><?php echo $cart_count; ?></span>
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
    <h1>Contact Us</h1>
    <p>We're here to help. Send us a message and we'll get back to you within 24 hours.</p>
  </section>

  <!-- CONTACT -->
  <div class="contact-container">
    <div class="contact-info">
      <h2>Get in Touch</h2>
      <p>Whether you have questions about our websites, need support with your purchase, or want to discuss custom solutions, feel free to reach out.</p>
      
      <div class="info-item">
        <div class="info-icon"><i class="fas fa-envelope"></i></div>
        <div class="info-text">
          <h4>Email</h4>
          <p><?php echo htmlspecialchars($site_email); ?></p>
          <p>We respond within 24 hours</p>
        </div>
      </div>
      
      <div class="info-item">
        <div class="info-icon"><i class="fas fa-clock"></i></div>
        <div class="info-text">
          <h4>Business Hours</h4>
          <p>Monday - Friday: 9:00 AM - 6:00 PM EST</p>
          <p>Saturday: 10:00 AM - 4:00 PM EST</p>
        </div>
      </div>
      
      <div class="info-item">
        <div class="info-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="info-text">
          <h4>Privacy & Security</h4>
          <p>Your information is secure with us. <a href="privacy.php" style="color:var(--accent);">Read our Privacy Policy</a></p>
        </div>
      </div>

      <div class="faq-section">
        <h3>Frequently Asked Questions</h3>
        <div class="faq-item">
          <h4>What payment methods do you accept?</h4>
          <p>We accept credit/debit cards, PayPal, bank transfers, and various cryptocurrencies including Bitcoin and Ethereum.</p>
        </div>
        <div class="faq-item">
          <h4>Can I purchase anonymously?</h4>
          <p>Yes! We offer anonymous checkout options that don't require personal information, especially when paying with cryptocurrency.</p>
        </div>
        <div class="faq-item">
          <h4>How do I receive my purchase?</h4>
          <p>After payment, you'll receive instant download links and access credentials via email.</p>
        </div>
      </div>
    </div>
    
    <div class="form-card">
      <h2>Send us a Message</h2>
      
      <?php if ($success): ?>
        <div class="success-message">
          <i class="fas fa-check-circle"></i> Thank you for your message! We'll get back to you within 24 hours.
        </div>
      <?php endif; ?>
      
      <?php if (isset($errors['general'])): ?>
        <div class="error-message"><?php echo $errors['general']; ?></div>
      <?php endif; ?>
      
      <form method="POST">
        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>">
          <?php if (isset($errors['name'])): ?>
            <div class="error"><?php echo $errors['name']; ?></div>
          <?php endif; ?>
        </div>
        
        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
          <?php if (isset($errors['email'])): ?>
            <div class="error"><?php echo $errors['email']; ?></div>
          <?php endif; ?>
        </div>
        
        <div class="form-group">
          <label>Subject *</label>
          <select name="subject">
            <option value="">Select a topic</option>
            <option value="General Inquiry" <?php echo (isset($subject) && $subject === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
            <option value="Technical Support" <?php echo (isset($subject) && $subject === 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
            <option value="Billing Question" <?php echo (isset($subject) && $subject === 'Billing Question') ? 'selected' : ''; ?>>Billing Question</option>
            <option value="Custom Website" <?php echo (isset($subject) && $subject === 'Custom Website') ? 'selected' : ''; ?>>Custom Website Request</option>
            <option value="Partnership" <?php echo (isset($subject) && $subject === 'Partnership') ? 'selected' : ''; ?>>Partnership Opportunity</option>
            <option value="Other" <?php echo (isset($subject) && $subject === 'Other') ? 'selected' : ''; ?>>Other</option>
          </select>
          <?php if (isset($errors['subject'])): ?>
            <div class="error"><?php echo $errors['subject']; ?></div>
          <?php endif; ?>
        </div>
        
        <div class="form-group">
          <label>Message *</label>
          <textarea name="message" rows="6" placeholder="Please describe your question or request in detail..."><?php echo htmlspecialchars($message ?? ''); ?></textarea>
          <?php if (isset($errors['message'])): ?>
            <div class="error"><?php echo $errors['message']; ?></div>
          <?php endif; ?>
          <div class="hint">Minimum 10 characters</div>
        </div>
        
        <button type="submit" class="submit-btn">
          <i class="fas fa-paper-plane"></i> Send Message
        </button>
      </form>
      
      <div class="form-footer">
        <i class="fas fa-lock"></i> Your information is secure and will never be shared with third parties.
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
