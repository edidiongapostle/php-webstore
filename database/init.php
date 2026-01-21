<?php
// SQLite database initialization script
try {
    // Create database directory if it doesn't exist
    $dbDir = __DIR__;
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    
    // Connect to SQLite database (will create if it doesn't exist)
    $db = new PDO('sqlite:' . __DIR__ . '/webstore.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Enable foreign key support
    $db->exec('PRAGMA foreign_keys = ON');
    
    // Websites table
    $db->exec("
        CREATE TABLE IF NOT EXISTS websites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            price REAL NOT NULL,
            category TEXT NOT NULL,
            image_url TEXT NOT NULL,
            demo_url TEXT,
            features TEXT,
            technologies TEXT,
            featured INTEGER DEFAULT 0,
            status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive', 'sold')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Orders table
    $db->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name TEXT NOT NULL,
            customer_email TEXT NOT NULL,
            customer_phone TEXT,
            total_amount REAL NOT NULL,
            status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'completed', 'cancelled')),
            payment_method TEXT,
            transaction_id TEXT,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Order items table
    $db->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            website_id INTEGER NOT NULL,
            price REAL NOT NULL,
            title TEXT NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
        )
    ");
    
    // Admin users table
    $db->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            email TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Insert sample data
    $websites = [
        [
            'title' => 'E-Commerce Platform',
            'description' => 'A fully functional e-commerce website with payment integration, inventory management, and responsive design. Perfect for online stores.',
            'price' => 2999.00,
            'category' => 'E-Commerce',
            'image_url' => 'https://via.placeholder.com/400x300/4F46E5/FFFFFF?text=E-Commerce',
            'demo_url' => 'https://demo.example.com/ecommerce',
            'features' => 'Payment Integration, Inventory Management, User Accounts, Admin Panel, Responsive Design',
            'technologies' => 'PHP, MySQL, JavaScript, Bootstrap, Stripe API',
            'featured' => 1
        ],
        [
            'title' => 'Restaurant Website',
            'description' => 'Modern restaurant website with online ordering system, menu management, and reservation booking. Includes mobile app integration.',
            'price' => 1999.00,
            'category' => 'Restaurant',
            'image_url' => 'https://via.placeholder.com/400x300/10B981/FFFFFF?text=Restaurant',
            'demo_url' => 'https://demo.example.com/restaurant',
            'features' => 'Online Ordering, Menu Management, Reservations, Customer Reviews, SEO Optimized',
            'technologies' => 'PHP, MySQL, JavaScript, jQuery, Google Maps API',
            'featured' => 0
        ],
        [
            'title' => 'Portfolio Website',
            'description' => 'Professional portfolio website for freelancers and agencies. Includes project gallery, client testimonials, and contact forms.',
            'price' => 999.00,
            'category' => 'Portfolio',
            'image_url' => 'https://via.placeholder.com/400x300/F59E0B/FFFFFF?text=Portfolio',
            'demo_url' => 'https://demo.example.com/portfolio',
            'features' => 'Project Gallery, Testimonials, Contact Forms, Blog Integration, Analytics',
            'technologies' => 'HTML5, CSS3, JavaScript, PHP, MySQL',
            'featured' => 0
        ],
        [
            'title' => 'Blog Platform',
            'description' => 'Complete blogging platform with content management, user registration, comment system, and social media integration.',
            'price' => 1499.00,
            'category' => 'Blog',
            'image_url' => 'https://via.placeholder.com/400x300/EF4444/FFFFFF?text=Blog',
            'demo_url' => 'https://demo.example.com/blog',
            'features' => 'Content Management, User Registration, Comments, Social Sharing, SEO Tools',
            'technologies' => 'PHP, MySQL, JavaScript, Bootstrap, REST API',
            'featured' => 0
        ],
        [
            'title' => 'Real Estate Website',
            'description' => 'Professional real estate website with property listings, search functionality, agent profiles, and mortgage calculator.',
            'price' => 2499.00,
            'category' => 'Real Estate',
            'image_url' => 'https://via.placeholder.com/400x300/8B5CF6/FFFFFF?text=Real+Estate',
            'demo_url' => 'https://demo.example.com/realestate',
            'features' => 'Property Listings, Advanced Search, Agent Profiles, Mortgage Calculator, Virtual Tours',
            'technologies' => 'PHP, MySQL, JavaScript, Leaflet Maps, Chart.js',
            'featured' => 1
        ],
        [
            'title' => 'Learning Management System',
            'description' => 'Complete LMS with course creation, student enrollment, progress tracking, and certificate generation.',
            'price' => 3499.00,
            'category' => 'Education',
            'image_url' => 'https://via.placeholder.com/400x300/06B6D4/FFFFFF?text=LMS',
            'demo_url' => 'https://demo.example.com/lms',
            'features' => 'Course Management, Student Enrollment, Progress Tracking, Certificates, Video Streaming',
            'technologies' => 'PHP, MySQL, JavaScript, Bootstrap, Video.js',
            'featured' => 0
        ]
    ];
    
    // Insert websites
    $stmt = $db->prepare("INSERT OR IGNORE INTO websites (title, description, price, category, image_url, demo_url, features, technologies, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($websites as $website) {
        $stmt->execute([
            $website['title'],
            $website['description'],
            $website['price'],
            $website['category'],
            $website['image_url'],
            $website['demo_url'],
            $website['features'],
            $website['technologies'],
            $website['featured']
        ]);
    }
    
    // Insert admin user (password: admin123)
    $db->exec("INSERT OR IGNORE INTO admin_users (username, password, email) VALUES ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@webstore.com')");
    
    echo "Database initialized successfully!\n";
    echo "Database location: " . __DIR__ . "/webstore.db\n";
    echo "Admin login: admin / admin123\n";
    
} catch(PDOException $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?>
