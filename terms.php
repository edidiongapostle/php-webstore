<?php
$pageTitle = "Terms of Use - WebStore";
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

    <!-- Terms of Use Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-4xl font-bold mb-8 text-gray-900">Terms of Use</h1>
            
            <div class="prose prose-lg max-w-none">
                <p class="text-gray-600 mb-6">Last updated: <?php echo date('F j, Y'); ?></p>
                
                <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 mb-8">
                    <p class="text-indigo-800">
                        <strong>Important:</strong> By using WebStore and purchasing our products, you agree to these terms of use. Please read them carefully before making any purchase.
                    </p>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">1. Acceptance of Terms</h2>
                <div class="space-y-4 text-gray-700">
                    <p>By accessing and using WebStore, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">2. Description of Service</h2>
                <div class="space-y-4 text-gray-700">
                    <p>WebStore is an e-commerce platform that sells pre-designed websites, templates, and digital products. Our service includes:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Sale of website templates and complete websites</li>
                        <li>Digital product delivery and licensing</li>
                        <li>Customer support for purchased products</li>
                        <li>Anonymous purchasing options</li>
                        <li>Multiple payment methods including cryptocurrency</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">3. User Accounts and Registration</h2>
                <div class="space-y-4 text-gray-700">
                    <p>While we offer anonymous checkout options, creating an account provides additional benefits:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Order history tracking</li>
                        <li>Download management</li>
                        <li>Priority customer support</li>
                        <li>Access to updates and patches</li>
                    </ul>
                    <p>You are responsible for maintaining the confidentiality of your account information.</p>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">4. Product Licensing</h2>
                <div class="space-y-4 text-gray-700">
                    <h3 class="text-xl font-semibold mt-6 mb-3">License Types</h3>
                    <ul class="list-disc pl-6 space-y-2">
                        <li><strong>Regular License:</strong> Use the product for one personal or commercial project</li>
                        <li><strong>Extended License:</strong> Use the product in multiple projects or for client work</li>
                        <li><strong>Resale Rights:</strong> Selected products include resale permissions (clearly marked)</li>
                    </ul>
                    
                    <h3 class="text-xl font-semibold mt-6 mb-3">Permitted Uses</h3>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Use in personal or commercial websites</li>
                        <li>Modify and customize for your needs</li>
                        <li>Use in client projects (with appropriate license)</li>
                        <li>Create derivative works</li>
                    </ul>
                    
                    <h3 class="text-xl font-semibold mt-6 mb-3">Prohibited Uses</h3>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Redistribution or resale of original files (unless explicitly permitted)</li>
                        <li>Claiming ownership of the original design</li>
                        <li>Using in illegal or harmful activities</li>
                        <li>Violating intellectual property rights</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">5. Payment Terms</h2>
                <div class="space-y-4 text-gray-700">
                    <h3 class="text-xl font-semibold mt-6 mb-3">Payment Methods</h3>
                    <p>We accept the following payment methods:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Credit and debit cards</li>
                        <li>PayPal</li>
                        <li>Bank transfers</li>
                        <li>Cryptocurrencies (Bitcoin, Ethereum, and others)</li>
                    </ul>
                    
                    <h3 class="text-xl font-semibold mt-6 mb-3">Anonymous Purchases</h3>
                    <p>For anonymous purchases:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>No personal information is required</li>
                        <li>Cryptocurrency payments are recommended</li>
                        <li>Download links are provided via temporary access codes</li>
                        <li>Standard refund policy applies</li>
                    </ul>
                    
                    <h3 class="text-xl font-semibold mt-6 mb-3">Refund Policy</h3>
                    <p>We offer a 30-day money-back guarantee:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Full refund within 30 days of purchase</li>
                        <li>Digital products must be deleted after refund</li>
                        <li>Custom work may have different terms</li>
                        <li>Crypto refunds processed in equivalent USD value</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">6. Delivery and Access</h2>
                <div class="space-y-4 text-gray-700">
                    <h3 class="text-xl font-semibold mt-6 mb-3">Digital Product Delivery</h3>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Instant download after payment confirmation</li>
                        <li>Download links remain active for 30 days</li>
                        <li>Account holders get permanent access to purchases</li>
                        <li>Anonymous buyers receive temporary access codes</li>
                    </ul>
                    
                    <h3 class="text-xl font-semibold mt-6 mb-3">File Formats</h3>
                    <p>Products are delivered in standard web formats:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>HTML, CSS, JavaScript files</li>
                        <li>PHP source code (where applicable)</li>
                        <li>Database schemas and installation scripts</li>
                        <li>Documentation and setup instructions</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">7. Intellectual Property</h2>
                <div class="space-y-4 text-gray-700">
                    <p>All products on WebStore are protected by intellectual property laws:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>We retain ownership of original designs</li>
                        <li>You receive a license to use the products</li>
                        <li>Third-party assets may have separate licenses</li>
                        <li>Copyright notices must be preserved where required</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">8. Privacy and Data Protection</h2>
                <div class="space-y-4 text-gray-700">
                    <p>Your privacy is important to us. Our privacy policy explains:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>What information we collect and why</li>
                        <li>How we protect your data</li>
                        <li>Your rights regarding your information</li>
                        <li>Anonymous purchasing options</li>
                    </ul>
                    <p>By using our service, you consent to our privacy practices as described in our <a href="privacy.php" class="text-indigo-600 hover:text-indigo-800">Privacy Policy</a>.</p>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">9. Prohibited Activities</h2>
                <div class="space-y-4 text-gray-700">
                    <p>You may not use our service to:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Violate any applicable laws or regulations</li>
                        <li>Infringe on intellectual property rights</li>
                        <li>Distribute malware or harmful code</li>
                        <li>Engage in fraudulent activities</li>
                        <li>Attempt to compromise our security systems</li>
                        <li>Use automated tools to access our service</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">10. Service Availability</h2>
                <div class="space-y-4 text-gray-700">
                    <p>We strive to maintain high service availability:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Service uptime is not guaranteed</li>
                        <li>We may perform maintenance with reasonable notice</li>
                        <li>Digital products remain accessible after purchase</li>
                        <li>We are not liable for service interruptions</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">11. Disclaimers and Warranties</h2>
                <div class="space-y-4 text-gray-700">
                    <h3 class="text-xl font-semibold mt-6 mb-3">Product Warranties</h3>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Products are sold "as is" unless explicitly stated</li>
                        <li>We guarantee functionality as described in product listings</li>
                        <li>Technical support is provided as specified</li>
                        <li>Compatibility with specific environments is not guaranteed</li>
                    </ul>
                    
                    <h3 class="text-xl font-semibold mt-6 mb-3">Service Disclaimers</h3>
                    <p>We disclaim liability for:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Indirect or consequential damages</li>
                        <li>Business interruption or data loss</li>
                        <li>Third-party service failures</li>
                        <li>User modifications to products</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">12. Limitation of Liability</h2>
                <div class="space-y-4 text-gray-700">
                    <p>Our total liability for any claims related to our service shall not exceed the amount you paid for the specific product in question. This limitation applies to all damages, losses, and causes of action.</p>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">13. Indemnification</h2>
                <div class="space-y-4 text-gray-700">
                    <p>You agree to indemnify and hold WebStore harmless from any claims, damages, or expenses arising from:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Your use of our products</li>
                        <li>Violation of these terms</li>
                        <li>Infringement of third-party rights</li>
                        <li>Your modifications to products</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">14. Termination</h2>
                <div class="space-y-4 text-gray-700">
                    <p>We may terminate or suspend your access to our service:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>For violation of these terms</li>
                        <li>For fraudulent activities</li>
                        <li>At our discretion with reasonable notice</li>
                    </ul>
                    <p>Upon termination, your license to use products may be revoked unless otherwise specified.</p>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">15. Changes to Terms</h2>
                <div class="space-y-4 text-gray-700">
                    <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Your continued use of our service constitutes acceptance of any modified terms.</p>
                    <p>For significant changes, we will:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Post notices on our website</li>
                        <li>Email registered users when possible</li>
                        <li>Provide reasonable transition periods</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">16. Dispute Resolution</h2>
                <div class="space-y-4 text-gray-700">
                    <p>Any disputes arising from these terms shall be resolved through:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Direct negotiation between parties</li>
                        <li>Mediation services if available</li>
                        <li>Applicable jurisdiction courts as a last resort</li>
                    </ul>
                </div>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-900">17. Contact Information</h2>
                <div class="space-y-4 text-gray-700">
                    <p>For questions about these terms of use, please contact us:</p>
                    <div class="bg-gray-50 p-4 rounded-lg mt-4">
                        <ul class="space-y-2">
                            <li><strong>Email:</strong> legal@webstore.com</li>
                            <li><strong>Contact Form:</strong> <a href="contact.php" class="text-indigo-600 hover:text-indigo-800">Visit our contact page</a></li>
                            <li><strong>Response Time:</strong> Within 5 business days</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 p-6 bg-indigo-50 rounded-lg">
                    <h3 class="text-xl font-semibold mb-3 text-indigo-900">Agreement to Terms</h3>
                    <p class="text-indigo-800">By completing a purchase on WebStore, you acknowledge that you have read, understood, and agree to be bound by these Terms of Use. If you do not agree to these terms, please do not use our service or make any purchases.</p>
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
                        <li><a href="terms.php" class="text-gray-400 hover:text-white">Terms of Use</a></li>
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
