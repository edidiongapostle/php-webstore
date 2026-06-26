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

// Get website ID from URL
$website_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get website details
$website = getWebsiteById($website_id);

if (!$website) {
    header('Location: index.php');
    exit;
}

// Get feature settings
$enable_reviews = getSetting('enable_reviews', '1');
$enable_wishlist = getSetting('enable_wishlist', '1');
$site_name = getSetting('site_name', 'WebStore');
$seo_title = getSetting('seo_title', 'Premium Websites for Sale');
$seo_description = getSetting('seo_description', 'Buy premium websites and templates for your business');
$seo_keywords = getSetting('seo_keywords', 'websites, templates, premium, business');

$pageTitle = htmlspecialchars($website['title']) . " - " . $site_name;
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
      margin: 0 auto 2rem;
    }

    /* CONTENT */
    .content {
      max-width: 1200px;
      margin: 4rem auto;
      padding: 0 2rem;
    }

    .product-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 3rem;
    }

    @media (max-width: 900px) {
      .product-grid {
        grid-template-columns: 1fr;
      }
    }

    .product-image {
      border: 1px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
      background: var(--white);
    }

    .product-image img {
      width: 100%;
      height: auto;
      display: block;
    }

    .screenshots {
      margin-top: 1.5rem;
    }

    .screenshots h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .screenshot-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
    }

    @media (max-width: 600px) {
      .screenshot-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    .screenshot-item {
      position: relative;
      cursor: pointer;
      border-radius: 8px;
      overflow: hidden;
    }

    .screenshot-item img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      transition: opacity 0.2s;
    }

    .screenshot-item:hover img {
      opacity: 0.9;
    }

    .demo-link {
      display: inline-flex;
      align-items: center;
      background: var(--accent);
      color: var(--white);
      padding: 0.75rem 1.5rem;
      border-radius: 100px;
      text-decoration: none;
      font-weight: 500;
      margin-top: 1.5rem;
      transition: background 0.2s;
    }

    .demo-link:hover {
      background: #0d2be0;
    }

    .product-info {
      display: flex;
      flex-direction: column;
      gap: 2rem;
    }

    .product-header {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 1rem;
    }

    .badge {
      background: var(--light);
      color: var(--black);
      padding: 0.35rem 0.75rem;
      border-radius: 100px;
      font-size: 0.85rem;
      font-weight: 500;
    }

    .badge.featured {
      background: #FEF3C7;
      color: #92400E;
    }

    .product-title {
      font-family: 'Fraunces', serif;
      font-size: 2.5rem;
      font-weight: 700;
      letter-spacing: -0.03em;
      line-height: 1.1;
      margin-bottom: 1rem;
    }

    .price {
      font-size: 2rem;
      font-weight: 700;
      color: var(--accent);
      margin-bottom: 0.5rem;
    }

    .price-note {
      color: var(--grey);
      font-size: 0.9rem;
    }

    .description {
      color: var(--grey);
      line-height: 1.8;
    }

    .features-list {
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
    }

    .features-list h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
      color: var(--grey);
    }

    .feature-item i {
      color: #22C55E;
    }

    .tech-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .tech-tag {
      background: var(--light);
      color: var(--grey);
      padding: 0.35rem 0.75rem;
      border-radius: 100px;
      font-size: 0.85rem;
    }

    .actions {
      display: flex;
      gap: 1rem;
      padding-top: 2rem;
      border-top: 1px solid var(--border);
    }

    .btn {
      flex: 1;
      padding: 0.875rem 1.5rem;
      border-radius: 100px;
      font-weight: 500;
      text-align: center;
      text-decoration: none;
      transition: background 0.2s;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .btn-primary {
      background: var(--accent);
      color: var(--white);
    }

    .btn-primary:hover {
      background: #0d2be0;
    }

    .btn-secondary {
      background: #EC4899;
      color: var(--white);
    }

    .btn-secondary:hover {
      background: #db2777;
    }

    .btn-ghost {
      background: var(--light);
      color: var(--black);
    }

    .btn-ghost:hover {
      background: #e8e8e6;
    }

    .info-box {
      background: #EFF6FF;
      border: 1px solid #BFDBFE;
      padding: 1.5rem;
      border-radius: 12px;
    }

    .info-box h4 {
      font-family: 'Fraunces', serif;
      font-size: 1.1rem;
      font-weight: 600;
      color: #1E40AF;
      margin-bottom: 0.75rem;
    }

    .info-box ul {
      list-style: none;
      color: #1E40AF;
      font-size: 0.9rem;
    }

    .info-box li {
      margin-bottom: 0.5rem;
    }

    /* REVIEWS */
    .reviews-section {
      max-width: 1200px;
      margin: 4rem auto;
      padding: 0 2rem;
    }

    .reviews-card {
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
      background: var(--white);
    }

    .reviews-card h2 {
      font-family: 'Fraunces', serif;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 2rem;
    }

    .review-form {
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 2rem;
    }

    .review-form h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: var(--grey);
    }

    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 0.95rem;
    }

    .form-group textarea {
      resize: vertical;
      min-height: 100px;
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

    /* MODAL */
    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.75);
      z-index: 2000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .modal.active {
      display: flex;
    }

    .modal-content {
      background: var(--white);
      border-radius: 16px;
      max-width: 900px;
      max-height: 90vh;
      overflow: auto;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.5rem;
      border-bottom: 1px solid var(--border);
    }

    .modal-header h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.25rem;
      font-weight: 600;
    }

    .modal-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--grey);
    }

    .modal-body {
      padding: 1.5rem;
    }

    .modal-body img {
      width: 100%;
      height: auto;
      border-radius: 8px;
    }

    .modal-footer {
      padding: 1.5rem;
      border-top: 1px solid var(--border);
      background: var(--light);
    }

    .modal-footer button {
      width: 100%;
      padding: 0.875rem;
      background: var(--grey);
      color: var(--white);
      border: none;
      border-radius: 8px;
      cursor: pointer;
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
    <h1><?php echo htmlspecialchars($website['title']); ?></h1>
    <p><?php echo htmlspecialchars($website['description']); ?></p>
  </section>

  <!-- CONTENT -->
  <div class="content">
    <div class="product-grid">
      <!-- Product Image -->
      <div class="product-image">
        <img src="<?php echo htmlspecialchars($website['image_url']); ?>" alt="<?php echo htmlspecialchars($website['title']); ?>">
        
        <!-- Screenshots Gallery -->
        <?php if (!empty($website['screenshots'])): ?>
          <div class="screenshots">
            <h3>Screenshots</h3>
            <div class="screenshot-grid">
              <?php 
              $screenshots = json_decode($website['screenshots'] ?? '[]', true) ?: [];
              foreach ($screenshots as $screenshot): 
                if (!empty($screenshot)): ?>
                  <div class="screenshot-item" onclick="openScreenshotModal('<?php echo htmlspecialchars($screenshot); ?>')">
                    <img src="<?php echo htmlspecialchars($screenshot); ?>" alt="Website Screenshot">
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
        
        <?php if ($website['demo_url']): ?>
          <a href="<?php echo htmlspecialchars($website['demo_url']); ?>" target="_blank" class="demo-link">
            <i class="fas fa-external-link-alt"></i> View Live Demo
          </a>
        <?php endif; ?>
      </div>

      <!-- Product Info -->
      <div class="product-info">
        <div class="product-header">
          <span class="badge"><?php echo htmlspecialchars($website['category']); ?></span>
          <?php if ($website['featured']): ?>
            <span class="badge featured">Featured</span>
          <?php endif; ?>
        </div>
        
        <h1 class="product-title"><?php echo htmlspecialchars($website['title']); ?></h1>
        <div class="price"><?php echo formatPrice($website['price']); ?></div>
        <div class="price-note">One-time payment</div>
        
        <div class="description">
          <?php echo nl2br(htmlspecialchars($website['description'])); ?>
        </div>

        <?php if ($website['features']): ?>
          <div class="features-list">
            <h3>Features</h3>
            <?php 
            $features = explode(',', $website['features']);
            foreach ($features as $feature): ?>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars(trim($feature)); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($website['technologies']): ?>
          <div>
            <h3 style="font-family:'Fraunces',serif;font-size:1.25rem;font-weight:600;margin-bottom:1rem">Technologies Used</h3>
            <div class="tech-tags">
              <?php 
              $technologies = explode(',', $website['technologies']);
              foreach ($technologies as $tech): ?>
                <span class="tech-tag"><?php echo htmlspecialchars(trim($tech)); ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Add to Cart Form -->
        <div class="actions">
          <form action="add_to_cart.php" method="POST" style="display:flex;gap:1rem;flex:1">
            <input type="hidden" name="website_id" value="<?php echo $website['id']; ?>">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-shopping-cart"></i> Add to Cart
            </button>
          </form>
          <?php if ($enable_wishlist === '1'): ?>
            <button type="button" onclick="addToWishlist(<?php echo $website['id']; ?>)" class="btn btn-secondary">
              <i class="fas fa-heart"></i> Add to Wishlist
            </button>
          <?php endif; ?>
          <a href="index.php" class="btn btn-ghost">Continue Shopping</a>
        </div>

        <div class="info-box">
          <h4>What's Included:</h4>
          <ul>
            <li>• Full source code</li>
            <li>• Installation documentation</li>
            <li>• 30 days support</li>
            <li>• Free updates for 6 months</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- REVIEWS -->
  <?php if ($enable_reviews === '1'): ?>
  <div class="reviews-section">
    <div class="reviews-card">
      <h2>Customer Reviews</h2>
      
      <!-- Review Form -->
      <div class="review-form">
        <h3>Leave a Review</h3>
        <form id="reviewForm">
          <input type="hidden" name="website_id" value="<?php echo $website['id']; ?>">
          <div class="form-group">
            <label>Rating</label>
            <select name="rating">
              <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
              <option value="4">⭐⭐⭐⭐ Very Good</option>
              <option value="3">⭐⭐⭐ Good</option>
              <option value="2">⭐⭐ Fair</option>
              <option value="1">⭐ Poor</option>
            </select>
          </div>
          <div class="form-group">
            <label>Your Review</label>
            <textarea name="review" rows="4" placeholder="Share your experience with this product..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-star"></i> Submit Review
          </button>
        </form>
      </div>
      
      <!-- Reviews List -->
      <div style="text-align:center;padding:2rem;color:var(--grey)">
        <i class="fas fa-star" style="font-size:2rem;margin-bottom:1rem"></i>
        <p>Be the first to review this product!</p>
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

  <!-- Screenshot Modal -->
  <div id="screenshotModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Screenshot Preview</h3>
        <button onclick="closeScreenshotModal()" class="modal-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body">
        <img id="modalImage" src="" alt="Screenshot">
      </div>
      <div class="modal-footer">
        <button onclick="closeScreenshotModal()">Close</button>
      </div>
    </div>
  </div>

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

    function addToWishlist(websiteId) {
      let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
      if (!wishlist.includes(websiteId)) {
        wishlist.push(websiteId);
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        alert('Added to wishlist!');
      } else {
        alert('Already in wishlist!');
      }
    }
    
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append('action', 'submit_review');
      fetch('submit_review.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Review submitted successfully!');
          this.reset();
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error submitting review. Please try again.');
      });
    });

    function openScreenshotModal(imageSrc) {
      const modal = document.getElementById('screenshotModal');
      const modalImage = document.getElementById('modalImage');
      modalImage.src = imageSrc;
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
    
    function closeScreenshotModal() {
      const modal = document.getElementById('screenshotModal');
      modal.classList.remove('active');
      document.body.style.overflow = 'auto';
    }
    
    document.getElementById('screenshotModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeScreenshotModal();
      }
    });
    
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeScreenshotModal();
      }
    });
  </script>
</body>
</html>
