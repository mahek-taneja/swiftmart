# SwiftMart - Multi-Sector Delivery Platform

SwiftMart is a comprehensive multi-sector delivery platform built with PHP, MySQL, and Bootstrap. It supports multiple user roles (customers, vendors, admins) and handles various product categories including electronics, fashion, food, hardware, and more.

## 🚀 Features

### Customer Features
- **User Registration & Authentication** - Secure account creation and login
- **Product Browsing** - Browse products by categories with search functionality
- **Shopping Cart** - Add/remove items, quantity management
- **Order Management** - Place orders, track order status
- **Product Reviews** - Rate and review products
- **Address Management** - Multiple shipping addresses
- **Responsive Design** - Mobile-friendly interface

### Vendor Features
- **Vendor Registration** - Business registration with approval system
- **Product Management** - Add, edit, delete products
- **Inventory Management** - Stock tracking and management
- **Order Processing** - View and process customer orders
- **Analytics Dashboard** - Sales reports and statistics
- **Profile Management** - Business information management

### Admin Features
- **User Management** - Manage customers, vendors, and admins
- **Vendor Approval** - Approve/reject vendor applications
- **Product Moderation** - Review and manage all products
- **Order Management** - Monitor and manage all orders
- **Category Management** - Create and manage product categories
- **Analytics Dashboard** - Comprehensive platform statistics
- **System Settings** - Configure platform settings

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Icons**: Bootstrap Icons
- **Architecture**: MVC Pattern with PDO

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- XAMPP/WAMP/LAMP (for local development)

## 🔧 Installation

### 1. Clone/Download the Project

```bash
git clone https://github.com/yourusername/swiftmart.git
# or download and extract the ZIP file
```

### 2. Set Up Web Server

Place the project files in your web server's document root:
- **XAMPP**: `C:\xampp\htdocs\swiftmart`
- **WAMP**: `C:\wamp64\www\swiftmart`
- **LAMP**: `/var/www/html/swiftmart`

### 3. Configure Database

1. Open `includes/config.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'swiftmart');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

### 4. Install Database

1. Start your web server (Apache) and MySQL
2. Open your browser and navigate to: `http://localhost/swiftmart/install.php`
3. Click "Install Database" to create tables and sample data
4. Wait for the installation to complete

### 5. Set Permissions

Ensure the following directories are writable:
```bash
chmod 755 uploads/
chmod 755 uploads/products/
chmod 755 uploads/users/
```

## 🔐 Default Login Credentials

After installation, you can use these default accounts:

### Admin Account
- **Email**: admin@swiftmart.com
- **Password**: admin123
- **Access**: Full admin panel access

### Vendor Account
- **Email**: vendor@swiftmart.com
- **Password**: vendor123
- **Access**: Vendor dashboard and product management

### Customer Account
- **Email**: customer@swiftmart.com
- **Password**: customer123
- **Access**: Customer shopping features

## 📁 Project Structure

```
swiftmart/
├── admin/                 # Admin panel files
│   ├── analytics.php      # Analytics dashboard
│   ├── api.php           # Admin API endpoints
│   ├── login.php         # Admin login
│   ├── logout.php        # Admin logout
│   ├── users.php         # User management
│   └── vendors.php       # Vendor management
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── img/             # Images and videos
├── customer/            # Customer-facing pages
│   ├── cart.php         # Shopping cart
│   ├── checkout.php     # Checkout process
│   ├── listings.php     # Product listings
│   ├── product.php      # Product details
│   └── tracking.php     # Order tracking
├── database/            # Database files
│   └── schema.sql       # Database schema
├── includes/            # Core PHP files
│   ├── auth.php         # Authentication system
│   ├── config.php       # Configuration
│   ├── footer.php       # Footer template
│   ├── head.php         # Head template
│   ├── models.php       # Database models
│   └── navbar.php       # Navigation template
├── uploads/             # File uploads directory
├── vendor/              # Vendor panel files
│   ├── analytics.php    # Vendor analytics
│   ├── dashboard.php    # Vendor dashboard
│   ├── inventory.php    # Product management
│   ├── login.php        # Vendor login
│   ├── orders.php       # Order management
│   └── register.php     # Vendor registration
├── index.php            # Homepage
├── install.php          # Database installer
└── README.md            # This file
```

## 🎨 Customization

### Adding New Categories

1. Access admin panel
2. Go to Categories section
3. Add new category with name, description, and icon
4. Categories will appear automatically on the homepage

### Modifying Color Scheme

1. Open `assets/css/styles.css`
2. Update CSS variables in `:root` section:
   ```css
   :root {
     --brand: #ff6b35;        /* Primary brand color */
     --accent: #ffd23f;       /* Accent color */
     --secondary: #ff4757;    /* Secondary color */
   }
   ```

### Adding New Features

The project follows MVC pattern:
- **Models**: Database operations (`includes/models.php`)
- **Views**: HTML templates (PHP files)
- **Controllers**: Business logic (API files)

## 🔒 Security Features

- **Password Hashing**: Secure password storage using PHP's `password_hash()`
- **CSRF Protection**: CSRF tokens for form submissions
- **SQL Injection Prevention**: Prepared statements with PDO
- **Input Sanitization**: All user inputs are sanitized
- **Session Management**: Secure session handling
- **Role-Based Access**: Different access levels for users

## 📊 Database Schema

### Key Tables
- `users` - User accounts (customers, vendors, admins)
- `vendors` - Vendor business information
- `categories` - Product categories
- `products` - Product information
- `orders` - Order records
- `order_items` - Order line items
- `cart` - Shopping cart items
- `reviews` - Product reviews
- `coupons` - Discount coupons
- `settings` - System settings

## 🚀 Deployment

### Production Deployment

1. **Update Configuration**:
   ```php
   define('DB_HOST', 'your-production-host');
   define('DB_NAME', 'your-production-db');
   define('DB_USER', 'your-production-user');
   define('DB_PASS', 'your-secure-password');
   ```

2. **Security Settings**:
   - Change default passwords
   - Update JWT secret key
   - Enable HTTPS
   - Configure proper file permissions

3. **Performance Optimization**:
   - Enable PHP OPcache
   - Configure MySQL query cache
   - Use CDN for static assets
   - Implement caching strategies

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Failed**:
   - Check MySQL service is running
   - Verify database credentials
   - Ensure database exists

2. **File Upload Issues**:
   - Check `uploads/` directory permissions
   - Verify PHP upload settings
   - Check file size limits

3. **Session Issues**:
   - Check PHP session configuration
   - Verify session directory permissions
   - Clear browser cookies

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📞 Support

For support and questions:
- Create an issue on GitHub
- Email: support@swiftmart.com
- Documentation: [Wiki](https://github.com/yourusername/swiftmart/wiki)

## 🔄 Updates

### Version 1.0.0
- Initial release
- Basic e-commerce functionality
- Multi-role user system
- Admin panel
- Vendor management
- Order processing

---

**SwiftMart** - Your Ultimate Multi-Sector Delivery Platform 🚀


## AI Forecasting + Chatbot (Flask + PHP)

### Overview
- Python Flask service provides `/forecast` and `/chat` endpoints.
- Forecast uses Prophet; responses include KPIs and Plotly figure JSON.
- Chatbot calls local Ollama (`OLLAMA_HOST`) with model (`OLLAMA_MODEL`, e.g., llama3) and uses forecast context for answers.

### Directory Structure
```
ai/
  app.py
  forecast_service.py
  chatbot_service.py
  requirements.txt
php/
  ai/
    forecast.php
    chatbot.php
  pages/
    sales_forecast.php
    chatbot.php
```

### Prerequisites
- Python 3.10+
- Node not required
- MySQL available with SwiftMart schema and order data
- Ollama installed and a model pulled (e.g., `ollama pull llama3`)

### Configure Environment
1. Copy `.env.example` to `.env` in the repo root.
2. Adjust values as needed (ports, DB creds):
```
FLASK_PORT=5055
DB_HOST=127.0.0.1
DB_PORT=3307
DB_USER=root
DB_PASS=
DB_NAME=swiftmart
OLLAMA_HOST=http://127.0.0.1:11434
OLLAMA_MODEL=llama3
```

### Install Python Dependencies
```bash
cd ai
python -m venv .venv
# Windows PowerShell
. .venv/Scripts/Activate.ps1
pip install -r requirements.txt
```

### Run Flask Service
```bash
# from ai/
python app.py
# Service runs on http://127.0.0.1:5055 (configurable via .env)
```

### Test Endpoints
- Forecast: `GET http://127.0.0.1:5055/forecast?horizon=90`
- Chat: `POST http://127.0.0.1:5055/chat` with JSON `{"message":"Why are sales dropping?"}`

### PHP Integration
- Forecast UI: visit `/php/pages/sales_forecast.php`
- Chatbot UI: visit `/php/pages/chatbot.php`

Both PHP pages call the Flask API via cURL and render the results (Bootstrap + Plotly).