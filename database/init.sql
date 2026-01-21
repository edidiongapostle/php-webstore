-- SQLite database initialization script

-- Websites table
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
);

-- Orders table
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
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    website_id INTEGER NOT NULL,
    price REAL NOT NULL,
    title TEXT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    email TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT OR IGNORE INTO websites (id, title, description, price, category, image_url, demo_url, features, technologies, featured) VALUES
(1, 'E-Commerce Platform', 'A fully functional e-commerce website with payment integration, inventory management, and responsive design. Perfect for online stores.', 2999.00, 'E-Commerce', 'https://via.placeholder.com/400x300/4F46E5/FFFFFF?text=E-Commerce', 'https://demo.example.com/ecommerce', 'Payment Integration, Inventory Management, User Accounts, Admin Panel, Responsive Design', 'PHP, MySQL, JavaScript, Bootstrap, Stripe API', 1),
(2, 'Restaurant Website', 'Modern restaurant website with online ordering system, menu management, and reservation booking. Includes mobile app integration.', 1999.00, 'Restaurant', 'https://via.placeholder.com/400x300/10B981/FFFFFF?text=Restaurant', 'https://demo.example.com/restaurant', 'Online Ordering, Menu Management, Reservations, Customer Reviews, SEO Optimized', 'PHP, MySQL, JavaScript, jQuery, Google Maps API', 0),
(3, 'Portfolio Website', 'Professional portfolio website for freelancers and agencies. Includes project gallery, client testimonials, and contact forms.', 999.00, 'Portfolio', 'https://via.placeholder.com/400x300/F59E0B/FFFFFF?text=Portfolio', 'https://demo.example.com/portfolio', 'Project Gallery, Testimonials, Contact Forms, Blog Integration, Analytics', 'HTML5, CSS3, JavaScript, PHP, MySQL', 0),
(4, 'Blog Platform', 'Complete blogging platform with content management, user registration, comment system, and social media integration.', 1499.00, 'Blog', 'https://via.placeholder.com/400x300/EF4444/FFFFFF?text=Blog', 'https://demo.example.com/blog', 'Content Management, User Registration, Comments, Social Sharing, SEO Tools', 'PHP, MySQL, JavaScript, Bootstrap, REST API', 0),
(5, 'Real Estate Website', 'Professional real estate website with property listings, search functionality, agent profiles, and mortgage calculator.', 2499.00, 'Real Estate', 'https://via.placeholder.com/400x300/8B5CF6/FFFFFF?text=Real+Estate', 'https://demo.example.com/realestate', 'Property Listings, Advanced Search, Agent Profiles, Mortgage Calculator, Virtual Tours', 'PHP, MySQL, JavaScript, Leaflet Maps, Chart.js', 1),
(6, 'Learning Management System', 'Complete LMS with course creation, student enrollment, progress tracking, and certificate generation.', 3499.00, 'Education', 'https://via.placeholder.com/400x300/06B6D4/FFFFFF?text=LMS', 'https://demo.example.com/lms', 'Course Management, Student Enrollment, Progress Tracking, Certificates, Video Streaming', 'PHP, MySQL, JavaScript, Bootstrap, Video.js', 0);

-- Insert admin user (password: admin123)
INSERT OR IGNORE INTO admin_users (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@webstore.com');
