<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

$pageTitle = "Privacy Policy - " . $site_name;
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
      max-width: 800px;
      margin: 4rem auto;
      padding: 0 2rem;
    }

    .content h2 {
      font-family: 'Fraunces', serif;
      font-size: 1.75rem;
      font-weight: 700;
      margin-top: 3rem;
      margin-bottom: 1rem;
    }

    .content h3 {
      font-size: 1.25rem;
      font-weight: 600;
      margin-top: 2rem;
      margin-bottom: 0.75rem;
    }

    .content p {
      color: var(--grey);
      margin-bottom: 1rem;
    }

    .content ul {
      color: var(--grey);
      margin-left: 1.5rem;
      margin-bottom: 1rem;
    }

    .content li {
      margin-bottom: 0.5rem;
    }

    .content a {
      color: var(--accent);
      text-decoration: none;
    }

    .content a:hover {
      text-decoration: underline;
    }

    .content .highlight-box {
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
      margin: 2rem 0;
    }

    .content .highlight-box ul {
      margin-left: 0;
      list-style: none;
    }

    .content .highlight-box li {
      margin-bottom: 0.75rem;
    }

    .content .highlight-box strong {
      color: var(--black);
    }

    .content .commitment {
      background: #EFF6FF;
      padding: 2rem;
      border-radius: 12px;
      margin-top: 3rem;
    }

    .content .commitment h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      margin-top: 0;
      color: var(--accent);
    }

    .content .commitment p {
      color: var(--black);
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
    <h1>Privacy Policy</h1>
    <p>Last updated: <?php echo date('F j, Y'); ?></p>
  </section>

  <!-- CONTENT -->
  <div class="content">
    <p>At <?php echo htmlspecialchars($site_name); ?>, we are committed to protecting your privacy. We collect information to provide better services to all our users.</p>

    <h2>1. Information We Collect</h2>
    
    <h3>Personal Information</h3>
    <p>When you make a purchase, we may collect:</p>
    <ul>
      <li>Name and email address (unless using anonymous checkout)</li>
      <li>Phone number (optional)</li>
      <li>Payment information (processed securely)</li>
      <li>Shipping and billing addresses (if applicable)</li>
    </ul>
    
    <h3>Anonymous Purchases</h3>
    <p>We offer anonymous checkout options that allow you to purchase without providing personal information. In such cases:</p>
    <ul>
      <li>No personal data is stored</li>
      <li>Orders are marked as "Anonymous"</li>
      <li>Only transaction data is retained for order fulfillment</li>
    </ul>
    
    <h3>Technical Information</h3>
    <p>We automatically collect certain technical information:</p>
    <ul>
      <li>IP address and browser type</li>
      <li>Device information</li>
      <li>Pages visited and time spent</li>
      <li>Referral source</li>
    </ul>

    <h2>2. How We Use Your Information</h2>
    <p>We use the information we collect to:</p>
    <ul>
      <li>Process and fulfill your orders</li>
      <li>Provide customer support</li>
      <li>Improve our services and website functionality</li>
      <li>Send transactional emails (order confirmations, updates)</li>
      <li>Prevent fraud and ensure security</li>
      <li>Comply with legal obligations</li>
    </ul>

    <h2>3. Data Security</h2>
    <p>We implement appropriate security measures to protect your information:</p>
    <ul>
      <li>SSL encryption for all data transmissions</li>
      <li>Secure payment processing through trusted providers</li>
      <li>Regular security audits and updates</li>
      <li>Limited access to personal data</li>
      <li>Secure data storage practices</li>
    </ul>

    <h2>4. Cryptocurrency Payments</h2>
    <p>For customers choosing cryptocurrency payments:</p>
    <ul>
      <li>Only transaction hash and amount are recorded</li>
      <li>No personal wallet information is stored</li>
      <li>Transactions are processed through secure crypto payment gateways</li>
      <li>Blockchain transparency ensures transaction verification</li>
    </ul>

    <h2>5. Data Retention</h2>
    <p>We retain information only as long as necessary:</p>
    <ul>
      <li>Order records: 7 years for tax and legal compliance</li>
      <li>Customer accounts: Until account deletion</li>
      <li>Anonymous orders: Transaction data only</li>
      <li>Analytics data: Aggregated and anonymized after 90 days</li>
    </ul>

    <h2>6. Your Rights</h2>
    <p>You have the right to:</p>
    <ul>
      <li>Access your personal information</li>
      <li>Correct inaccurate information</li>
      <li>Request deletion of your data (subject to legal requirements)</li>
      <li>Opt-out of marketing communications</li>
      <li>Request data portability</li>
    </ul>

    <h2>7. Third-Party Services</h2>
    <p>We use trusted third-party services:</p>
    <ul>
      <li>Payment processors (PayPal, Stripe, crypto gateways)</li>
      <li>Web hosting and CDN services</li>
      <li>Analytics tools (anonymized data only)</li>
      <li>Email delivery services for transactional emails</li>
    </ul>

    <h2>8. Cookies</h2>
    <p>Our website uses cookies to:</p>
    <ul>
      <li>Maintain shopping cart contents</li>
      <li>Remember login preferences</li>
      <li>Analyze website usage (anonymized)</li>
      <li>Improve user experience</li>
    </ul>
    <p>You can control cookies through your browser settings.</p>

    <h2>9. International Data Transfers</h2>
    <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your data in accordance with applicable data protection laws.</p>

    <h2>10. Changes to This Policy</h2>
    <p>We may update this privacy policy from time to time. We will notify you of any changes by:</p>
    <ul>
      <li>Posting the new policy on this page</li>
      <li>Sending email notifications for significant changes</li>
      <li>Displaying notices on our website</li>
    </ul>

    <h2>11. Contact Us</h2>
    <p>If you have any questions about this privacy policy or our data practices, please contact us:</p>
    <div class="highlight-box">
      <ul>
        <li><strong>Email:</strong> privacy@webstore.com</li>
        <li><strong>Contact Form:</strong> <a href="contact.php">Visit our contact page</a></li>
        <li><strong>Response Time:</strong> Within 48 hours</li>
      </ul>
    </div>

    <div class="commitment">
      <h3>Privacy Commitment</h3>
      <p>We are committed to protecting your privacy and ensuring transparency in how we handle your data. Your trust is important to us, and we continuously work to improve our privacy practices.</p>
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
  </script>
</body>
</html>
