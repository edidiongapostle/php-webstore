# WebStore - PHP E-commerce Platform

A modern PHP e-commerce webapp for selling premium websites and digital products with anonymous checkout and cryptocurrency payment support.

## Features

### E-commerce Features
- **Product Catalog** - Showcase websites and digital products
- **Shopping Cart** - Full cart management with session storage
- **Secure Checkout** - Multi-step checkout with validation
- **Order Management** - Complete order tracking and status updates
- **Anonymous Checkout** - Privacy-focused purchasing option
- **Cryptocurrency Payments** - Bitcoin, Ethereum, and more

### Payment Methods
- **Credit/Debit Cards** - Stripe integration ready
- **PayPal** - Full PayPal support
- **Bank Transfer** - Direct bank payment option
- **Cryptocurrency** - BTC, ETH, LTC support

### Privacy & Security
- **Anonymous Purchases** - No personal data required
- **Privacy Policy** - Comprehensive privacy protection
- **Terms of Use** - Legal agreement system
- **Secure Payments** - Encrypted transactions

### Admin Panel
- **Dashboard** - Real-time statistics and analytics
- **Product Management** - Full CRUD for websites
- **Order Management** - Complete order processing
- **Settings Panel** - Configure payment methods and features
- **Customer Support** - Contact form and message handling

### Design & UX
- **Responsive Design** - Mobile-first approach
- **Modern UI** - Tailwind CSS styling
- **FontAwesome Icons** - Professional iconography
- **SEO Optimized** - Meta tags and structured data

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: SQLite with PDO
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Tailwind CSS
- **Icons**: FontAwesome 6.4
- **Security**: Password hashing, input sanitization

## Requirements

- PHP 8.0 or higher
- SQLite 3.x
- Web server (Apache, Nginx, etc.)
- PHP extensions: PDO, SQLite, JSON, Sessions

## Quick Start

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/webstore.git
cd webstore
```

### 2. Setup Database
```bash
# Initialize the database with sample data
php database/init.php
```

### 3. Configure Application
```bash
# Copy and edit configuration
cp config.php.example config.php
# Edit config.php with your settings
```

### 4. Set Permissions
```bash
# Make database directory writable
chmod 755 database/
chmod 666 database/webstore.db
```

### 5. Start Development Server
```bash
# Using PHP built-in server
php -S localhost:8000

# Or use your preferred web server
```

## Configuration

### Database Setup
The application uses SQLite for simplicity. The database file will be created automatically at `database/webstore.db`.

### Admin Access
- **URL**: `/admin/login.php`
- **Username**: `admin`
- **Password**: `admin123`

**Change the default admin password immediately after first login!**

### Environment Variables
Create a `.env` file for sensitive data:
```env
DB_PATH=/path/to/database/webstore.db
SITE_NAME=Your Store Name
SITE_EMAIL=your-email@example.com
```

## Project Structure

```
webstore/
├── admin/                  # Admin panel files
│   ├── login.php         # Admin login
│   ├── dashboard.php      # Admin dashboard
│   ├── websites.php       # Product management
│   ├── orders.php         # Order management
│   ├── settings.php       # Settings panel
│   └── ...
├── database/               # Database files
│   ├── init.php          # Database initialization
│   └── webstore.db       # SQLite database
├── assets/                # Static assets (CSS, JS, images)
├── config.php             # Application configuration
├── functions.php          # Helper functions
├── index.php             # Homepage
├── cart.php              # Shopping cart
├── checkout.php           # Checkout process
├── contact.php            # Contact form
├── privacy.php            # Privacy policy
├── terms.php              # Terms of use
└── README.md              # This file
```

## Core Features Explained

### Anonymous Checkout
Customers can purchase without providing personal information:
- Toggle anonymous option during checkout
- No name/email required
- Crypto payments recommended for privacy
- Orders marked as "Anonymous" in admin

### Cryptocurrency Payments
Support for multiple cryptocurrencies:
- Bitcoin (BTC)
- Ethereum (ETH)
- Litecoin (LTC)
- Wallet address configuration in admin
- Real-time payment processing

### Admin Settings Panel
Comprehensive configuration options:
- Payment method management
- Feature toggles (reviews, wishlist, etc.)
- SEO settings
- Tax and pricing configuration
- Maintenance mode

## Security Features

- **Input Sanitization** - All user inputs sanitized
- **Password Hashing** - Secure password storage
- **SQL Injection Protection** - PDO prepared statements
- **XSS Prevention** - Output escaping
- **CSRF Protection** - Token validation
- **Session Security** - Secure session configuration

## Database Schema

### Main Tables
- `websites` - Product catalog
- `orders` - Customer orders
- `order_items` - Order line items
- `contact_messages` - Customer inquiries
- `settings` - Application configuration
- `payment_methods` - Payment gateway settings

## Deployment

### Production Setup
1. **Configure Web Server**
   - Point document root to project directory
   - Enable PHP and SQLite extensions
   - Configure HTTPS certificate

2. **Set File Permissions**
   ```bash
   chmod 755 ./
   chmod 755 database/
   chmod 666 database/webstore.db
   ```

3. **Environment Configuration**
   - Update `config.php` with production settings
   - Set appropriate error reporting levels
   - Configure timezone and locale

4. **Security Hardening**
   - Change default admin password
   - Set up HTTPS
   - Configure firewall rules
   - Regular backups

### Docker Deployment
```bash
# Build image
docker build -t webstore .

# Run container
docker run -p 8080:80 webstore
```

## Testing

### Running Tests
```bash
# Run PHPUnit tests
php vendor/bin/phpunit

# Run specific test
php vendor/bin/phpunit tests/CheckoutTest.php
```

### Test Coverage
- Unit tests for core functions
- Integration tests for payment processing
- Security tests for input validation
- Performance tests for database queries

## Performance Optimization

### Database Optimization
- **SQLite** - Lightweight and fast for small-medium stores
- **Indexed Queries** - Optimized database queries
- **Connection Pooling** - Efficient database connections

### Caching Strategy
- **Session Cache** - User session data
- **Product Cache** - Frequently accessed products
- **Static Asset Caching** - CSS/JS optimization

## Customization

### Adding New Payment Methods
1. Add to `payment_methods` table
2. Update checkout form HTML
3. Implement payment processing logic
4. Configure in admin settings

### Custom Themes
- Modify CSS classes in Tailwind configuration
- Update layout templates
- Add custom JavaScript functionality

## Troubleshooting

### Common Issues

#### **Database Connection Error**
```bash
# Check SQLite extension
php -m | grep sqlite

# Check file permissions
ls -la database/webstore.db
```

#### **Session Issues**
```bash
# Check session save path
php -i | grep session.save_path

# Verify directory permissions
chmod 755 /tmp/sessions
```

#### **Payment Processing Errors**
- Verify API keys in admin settings
- Check webhook URLs
- Test with sandbox mode first

### Debug Mode
Enable debugging in `config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
