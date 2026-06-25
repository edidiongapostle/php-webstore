<?php
require_once 'config.php';
require_once 'functions.php';
$site_name = getSetting('site_name', 'WebStore');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Maintenance Mode - <?php echo htmlspecialchars($site_name); ?></title>
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
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .maintenance-container {
      max-width: 500px;
      width: 100%;
      padding: 2rem;
      text-align: center;
    }

    .maintenance-card {
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 3rem;
      background: var(--white);
    }

    .icon {
      font-size: 4rem;
      color: var(--accent);
      margin-bottom: 1.5rem;
    }

    .maintenance-card h1 {
      font-family: 'Fraunces', serif;
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: -0.03em;
      margin-bottom: 1rem;
    }

    .maintenance-card p {
      color: var(--grey);
      margin-bottom: 2rem;
    }

    .info-box {
      background: var(--light);
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 1rem;
      text-align: left;
    }

    .info-box h3 {
      font-family: 'Fraunces', serif;
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
    }

    .info-box ul {
      list-style: none;
      padding: 0;
    }

    .info-box li {
      color: var(--grey);
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
    }

    .info-box i {
      color: #22C55E;
    }

    .time-box {
      background: #FEF3C7;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
    }

    .time-box p {
      margin: 0;
      font-size: 0.9rem;
      color: #92400E;
    }

    .contact {
      font-size: 0.85rem;
      color: var(--grey);
    }

    .footer {
      margin-top: 2rem;
      font-size: 0.8rem;
      color: var(--grey);
    }
  </style>
</head>
<body>
  <div class="maintenance-container">
    <div class="maintenance-card">
      <div class="icon">
        <i class="fas fa-tools"></i>
      </div>
      
      <h1>Under Maintenance</h1>
      
      <p>
        We're currently performing scheduled maintenance to improve our services. 
        We'll be back online shortly!
      </p>
      
      <div class="info-box">
        <h3>What to expect:</h3>
        <ul>
          <li><i class="fas fa-check-circle"></i> Improved performance</li>
          <li><i class="fas fa-check-circle"></i> New features</li>
          <li><i class="fas fa-check-circle"></i> Enhanced security</li>
        </ul>
      </div>
      
      <div class="time-box">
        <p><i class="fas fa-clock mr-2"></i> Estimated downtime: Less than 2 hours</p>
      </div>
      
      <div class="contact">
        For urgent inquiries, please contact our support team.
      </div>
    </div>
    
    <div class="footer">
      © <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.
    </div>
  </div>
</body>
</html>
