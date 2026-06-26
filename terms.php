<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

$pageTitle = "Terms of Use - " . $site_name;
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

    .content .notice {
      background: #FEF3C7;
      border-left: 4px solid #F59E0B;
      padding: 1.5rem;
      margin: 2rem 0;
      border-radius: 8px;
    }

    .content .notice strong {
      color: #92400E;
    }

    .content .notice p {
      color: #92400E;
      margin: 0;
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

    .content .agreement {
      background: #EFF6FF;
      padding: 2rem;
      border-radius: 12px;
      margin-top: 3rem;
    }

    .content .agreement h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      margin-top: 0;
      color: var(--accent);
    }

    .content .agreement p {
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
    <h1>Terms of Use</h1>
    <p>Last updated: <?php echo date('F j, Y'); ?></p>
  </section>

  <!-- CONTENT -->
  <div class="content">
    <div class="notice">
      <p><strong>Important:</strong> By using <?php echo htmlspecialchars($site_name); ?> and purchasing our products, you agree to these terms of use. Please read them carefully before making any purchase.</p>
    </div>

    <h2>1. Acceptance of Terms</h2>
    <p>By accessing and using <?php echo htmlspecialchars($site_name); ?>, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>

    <h2>2. Description of Service</h2>
    <p><?php echo htmlspecialchars($site_name); ?> is an e-commerce platform that sells pre-designed websites, templates, and digital products. Our service includes:</p>
    <ul>
      <li>Sale of website templates and complete websites</li>
      <li>Digital product delivery and licensing</li>
      <li>Customer support for purchased products</li>
      <li>Anonymous purchasing options</li>
      <li>Multiple payment methods including cryptocurrency</li>
    </ul>

    <h2>3. User Accounts and Registration</h2>
    <p>While we offer anonymous checkout options, creating an account provides additional benefits:</p>
    <ul>
      <li>Order history tracking</li>
      <li>Download management</li>
      <li>Priority customer support</li>
      <li>Access to updates and patches</li>
    </ul>
    <p>You are responsible for maintaining the confidentiality of your account information.</p>

    <h2>4. Product Licensing</h2>
    <h3>License Types</h3>
    <ul>
      <li><strong>Regular License:</strong> Use the product for one personal or commercial project</li>
      <li><strong>Extended License:</strong> Use the product in multiple projects or for client work</li>
      <li><strong>Resale Rights:</strong> Selected products include resale permissions (clearly marked)</li>
    </ul>
    
    <h3>Permitted Uses</h3>
    <ul>
      <li>Use in personal or commercial websites</li>
      <li>Modify and customize for your needs</li>
      <li>Use in client projects (with appropriate license)</li>
      <li>Create derivative works</li>
    </ul>
    
    <h3>Prohibited Uses</h3>
    <ul>
      <li>Redistribution or resale of original files (unless explicitly permitted)</li>
      <li>Claiming ownership of the original design</li>
      <li>Using in illegal or harmful activities</li>
      <li>Violating intellectual property rights</li>
    </ul>

    <h2>5. Payment Terms</h2>
    <h3>Payment Methods</h3>
    <p>We accept the following payment methods:</p>
    <ul>
      <li>Credit and debit cards</li>
      <li>PayPal</li>
      <li>Bank transfers</li>
      <li>Cryptocurrencies (Bitcoin, Ethereum, and others)</li>
    </ul>
    
    <h3>Anonymous Purchases</h3>
    <p>For anonymous purchases:</p>
    <ul>
      <li>No personal information is required</li>
      <li>Cryptocurrency payments are recommended</li>
      <li>Download links are provided via temporary access codes</li>
      <li>Standard refund policy applies</li>
    </ul>
    
    <h3>Refund Policy</h3>
    <p>We offer a 30-day money-back guarantee:</p>
    <ul>
      <li>Full refund within 30 days of purchase</li>
      <li>Digital products must be deleted after refund</li>
      <li>Custom work may have different terms</li>
      <li>Crypto refunds processed in equivalent USD value</li>
    </ul>

    <h2>6. Delivery and Access</h2>
    <h3>Digital Product Delivery</h3>
    <ul>
      <li>Instant download after payment confirmation</li>
      <li>Download links remain active for 30 days</li>
      <li>Account holders get permanent access to purchases</li>
      <li>Anonymous buyers receive temporary access codes</li>
    </ul>
    
    <h3>File Formats</h3>
    <p>Products are delivered in standard web formats:</p>
    <ul>
      <li>HTML, CSS, JavaScript files</li>
      <li>PHP source code (where applicable)</li>
      <li>Database schemas and installation scripts</li>
      <li>Documentation and setup instructions</li>
    </ul>

    <h2>7. Intellectual Property</h2>
    <p>All products on <?php echo htmlspecialchars($site_name); ?> are protected by intellectual property laws:</p>
    <ul>
      <li>We retain ownership of original designs</li>
      <li>You receive a license to use the products</li>
      <li>Third-party assets may have separate licenses</li>
      <li>Copyright notices must be preserved where required</li>
    </ul>

    <h2>8. Privacy and Data Protection</h2>
    <p>Your privacy is important to us. Our privacy policy explains:</p>
    <ul>
      <li>What information we collect and why</li>
      <li>How we protect your data</li>
      <li>Your rights regarding your information</li>
      <li>Anonymous purchasing options</li>
    </ul>
    <p>By using our service, you consent to our privacy practices as described in our <a href="privacy.php">Privacy Policy</a>.</p>

    <h2>9. Prohibited Activities</h2>
    <p>You may not use our service to:</p>
    <ul>
      <li>Violate any applicable laws or regulations</li>
      <li>Infringe on intellectual property rights</li>
      <li>Distribute malware or harmful code</li>
      <li>Engage in fraudulent activities</li>
      <li>Attempt to compromise our security systems</li>
      <li>Use automated tools to access our service</li>
    </ul>

    <h2>10. Service Availability</h2>
    <p>We strive to maintain high service availability:</p>
    <ul>
      <li>Service uptime is not guaranteed</li>
      <li>We may perform maintenance with reasonable notice</li>
      <li>Digital products remain accessible after purchase</li>
      <li>We are not liable for service interruptions</li>
    </ul>

    <h2>11. Disclaimers and Warranties</h2>
    <h3>Product Warranties</h3>
    <ul>
      <li>Products are sold "as is" unless explicitly stated</li>
      <li>We guarantee functionality as described in product listings</li>
      <li>Technical support is provided as specified</li>
      <li>Compatibility with specific environments is not guaranteed</li>
    </ul>
    
    <h3>Service Disclaimers</h3>
    <p>We disclaim liability for:</p>
    <ul>
      <li>Indirect or consequential damages</li>
      <li>Business interruption or data loss</li>
      <li>Third-party service failures</li>
      <li>User modifications to products</li>
    </ul>

    <h2>12. Limitation of Liability</h2>
    <p>Our total liability for any claims related to our service shall not exceed the amount you paid for the specific product in question. This limitation applies to all damages, losses, and causes of action.</p>

    <h2>13. Indemnification</h2>
    <p>You agree to indemnify and hold <?php echo htmlspecialchars($site_name); ?> harmless from any claims, damages, or expenses arising from:</p>
    <ul>
      <li>Your use of our products</li>
      <li>Violation of these terms</li>
      <li>Infringement of third-party rights</li>
      <li>Your modifications to products</li>
    </ul>

    <h2>14. Termination</h2>
    <p>We may terminate or suspend your access to our service:</p>
    <ul>
      <li>For violation of these terms</li>
      <li>For fraudulent activities</li>
      <li>At our discretion with reasonable notice</li>
    </ul>
    <p>Upon termination, your license to use products may be revoked unless otherwise specified.</p>

    <h2>15. Changes to Terms</h2>
    <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Your continued use of our service constitutes acceptance of any modified terms.</p>
    <p>For significant changes, we will:</p>
    <ul>
      <li>Post notices on our website</li>
      <li>Email registered users when possible</li>
      <li>Provide reasonable transition periods</li>
    </ul>

    <h2>16. Dispute Resolution</h2>
    <p>Any disputes arising from these terms shall be resolved through:</p>
    <ul>
      <li>Direct negotiation between parties</li>
      <li>Mediation services if available</li>
      <li>Applicable jurisdiction courts as a last resort</li>
    </ul>

    <h2>17. Contact Information</h2>
    <p>For questions about these terms of use, please contact us:</p>
    <div class="highlight-box">
      <ul>
        <li><strong>Email:</strong> legal@webstore.com</li>
        <li><strong>Contact Form:</strong> <a href="contact.php">Visit our contact page</a></li>
        <li><strong>Response Time:</strong> Within 5 business days</li>
      </ul>
    </div>

    <div class="agreement">
      <h3>Agreement to Terms</h3>
      <p>By completing a purchase on <?php echo htmlspecialchars($site_name); ?>, you acknowledge that you have read, understood, and agree to be bound by these Terms of Use. If you do not agree to these terms, please do not use our service or make any purchases.</p>
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
