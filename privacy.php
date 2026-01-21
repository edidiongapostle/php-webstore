<?php
$pageTitle = "Privacy Policy - WebStore";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-indigo-600">WebStore</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                    <a href="cart.php" class="relative text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                <?php echo count($_SESSION['cart']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="contact.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Contact</a>
                    <a href="admin/login.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">Admin</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Privacy Policy Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-4xl font-bold mb-8 text-gray-900">Privacy Policy</h1>
            
            <div class="prose prose-lg max-w-none">
                <p class="text-gray-600 mb-6">Last updated: <?php echo date('F j, Y'); ?></p>
                
                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">1. Information We Collect</h2>
                <div class="space-y-4 text-gray-700">
                    <p>At WebStore, we are committed to protecting your privacy. We collect information to provide better services to all our users.</p>
                    
                    <h3 class="text-xl font-semibold mt-6 mb-3">Personal Information</h3>
                    <p>When you make a purchase, we may collect:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Name and email address (unless using anonymous checkout)</li>
                        <li>Phone number (optional)</li>
                        <li>Payment information (processed securely)</li>
                        <li>Shipping and billing addresses (if applicable)</li>
                    </ul>
                    
                    <h3 class="text-xl font-semibold mt-6 mb-3">Anonymous Purchases</h3>
                    <p>We offer anonymous checkout options that allow you to purchase without providing personal information. In such cases:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>No personal data is stored</li>
                        <li>Orders are marked as "Anonymous"</li>
                        <li>Only transaction data is retained for order fulfillment</li>
                    </ul>
                    
                    <h3 class="text-xl font-semibold mt-6 mb-3">Technical Information</h3>
                    <p>We automatically collect certain technical information:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>IP address and browser type</li>
                        <li>Device information</li>
                        <li>Pages visited and time spent</li>
                        <li>Referral source</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">2. How We Use Your Information</h2>
                <div class="space-y-4 text-gray-700">
                    <p>We use the information we collect to:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Process and fulfill your orders</li>
                        <li>Provide customer support</li>
                        <li>Improve our services and website functionality</li>
                        <li>Send transactional emails (order confirmations, updates)</li>
                        <li>Prevent fraud and ensure security</li>
                        <li>Comply with legal obligations</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">3. Data Security</h2>
                <div class="space-y-4 text-gray-700">
                    <p>We implement appropriate security measures to protect your information:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>SSL encryption for all data transmissions</li>
                        <li>Secure payment processing through trusted providers</li>
                        <li>Regular security audits and updates</li>
                        <li>Limited access to personal data</li>
                        <li>Secure data storage practices</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">4. Cryptocurrency Payments</h2>
                <div class="space-y-4 text-gray-700">
                    <p>For customers choosing cryptocurrency payments:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Only transaction hash and amount are recorded</li>
                        <li>No personal wallet information is stored</li>
                        <li>Transactions are processed through secure crypto payment gateways</li>
                        <li>Blockchain transparency ensures transaction verification</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">5. Data Retention</h2>
                <div class="space-y-4 text-gray-700">
                    <p>We retain information only as long as necessary:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Order records: 7 years for tax and legal compliance</li>
                        <li>Customer accounts: Until account deletion</li>
                        <li>Anonymous orders: Transaction data only</li>
                        <li>Analytics data: Aggregated and anonymized after 90 days</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">6. Your Rights</h2>
                <div class="space-y-4 text-gray-700">
                    <p>You have the right to:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Access your personal information</li>
                        <li>Correct inaccurate information</li>
                        <li>Request deletion of your data (subject to legal requirements)</li>
                        <li>Opt-out of marketing communications</li>
                        <li>Request data portability</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">7. Third-Party Services</h2>
                <div class="space-y-4 text-gray-700">
                    <p>We use trusted third-party services:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Payment processors (PayPal, Stripe, crypto gateways)</li>
                        <li>Web hosting and CDN services</li>
                        <li>Analytics tools (anonymized data only)</li>
                        <li>Email delivery services for transactional emails</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">8. Cookies</h2>
                <div class="space-y-4 text-gray-700">
                    <p>Our website uses cookies to:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Maintain shopping cart contents</li>
                        <li>Remember login preferences</li>
                        <li>Analyze website usage (anonymized)</li>
                        <li>Improve user experience</li>
                    </ul>
                    <p>You can control cookies through your browser settings.</p>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">9. International Data Transfers</h2>
                <div class="space-y-4 text-gray-700">
                    <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your data in accordance with applicable data protection laws.</p>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">10. Changes to This Policy</h2>
                <div class="space-y-4 text-gray-700">
                    <p>We may update this privacy policy from time to time. We will notify you of any changes by:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Posting the new policy on this page</li>
                        <li>Sending email notifications for significant changes</li>
                        <li>Displaying notices on our website</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">11. Contact Us</h2>
                <div class="space-y-4 text-gray-700">
                    <p>If you have any questions about this privacy policy or our data practices, please contact us:</p>
                    <div class="bg-gray-50 p-4 rounded-lg mt-4">
                        <ul class="space-y-2">
                            <li><strong>Email:</strong> privacy@webstore.com</li>
                            <li><strong>Contact Form:</strong> <a href="contact.php" class="text-indigo-600 hover:text-indigo-800">Visit our contact page</a></li>
                            <li><strong>Response Time:</strong> Within 48 hours</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 p-6 bg-indigo-50 rounded-lg">
                    <h3 class="text-xl font-semibold mb-3 text-indigo-900">Privacy Commitment</h3>
                    <p class="text-indigo-800">We are committed to protecting your privacy and ensuring transparency in how we handle your data. Your trust is important to us, and we continuously work to improve our privacy practices.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">WebStore</h3>
                    <p class="text-gray-400">Premium websites for your business needs</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Legal</h3>
                    <ul class="space-y-2">
                        <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-700 text-center">
                <p>&copy; <?php echo date('Y'); ?> WebStore. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
