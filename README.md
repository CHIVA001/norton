# 🚗 Tire Stock Management System

A comprehensive inventory and sales management system for tire businesses built with Laravel 12, featuring stock tracking, sales management, category organization, and detailed reporting.

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.0-38B2AC?style=flat&logo=tailwind-css)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Database Setup](#-database-setup)
- [Running the Application](#-running-the-application)
- [Project Structure](#-project-structure)
- [Usage](#-usage)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Features

### 📦 Stock Management
- Add, edit, and delete tire stock items
- Track tire brands, codes, quantities, and prices
- Real-time stock level monitoring
- Bulk stock operations

### 🏷️ Brand/Category Management
- Organize tires by brands and categories
- Image upload support for brands
- Year-based categorization
- Live image preview before upload

### 💰 Sales Tracking
- Record sales transactions
- Automatic total calculation
- Sales history with date tracking
- Link sales to specific stock items

### 📊 Reporting & Analytics
- Stock level reports
- Sales reports with date filtering
- Low stock alerts
- Revenue tracking

### 🎨 Modern UI/UX
- Responsive Bootstrap 5 design
- Font Awesome icons
- Dark mode support
- Mobile-friendly interface
- Live image preview functionality

### 🔒 Security
- CSRF protection
- SQL injection prevention
- XSS protection
- Secure file uploads

## 🔧 Requirements

Before you begin, ensure you have the following installed:

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **NPM** or **PNPM** >= 7.5.1
- **SQLite** (default) or **MySQL/PostgreSQL**
- **Git**

### Optional
- **Docker** (for containerized deployment)
- **Redis** (for caching and queues)

## 📥 Installation

### 1. Clone the Repository

```bash
git clone <your-repository-url>
cd project
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

Using NPM:
```bash
npm install
```

Or using PNPM (recommended):
```bash
pnpm install
```

### 4. Environment Setup

Copy the example environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### 5. Configure Environment Variables

Edit the `.env` file and configure your settings:

```env
APP_NAME="Tire Stock Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite by default)
DB_CONNECTION=sqlite

# For MySQL, uncomment and configure:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=tire_management
# DB_USERNAME=root
# DB_PASSWORD=
```

## 🗄️ Database Setup

### Using SQLite (Default)

Create the database file:

```bash
touch database/database.sqlite
```

### Using MySQL

1. Create a database:
```sql
CREATE DATABASE tire_management;
```

2. Update `.env` with MySQL credentials

### Run Migrations

```bash
php artisan migrate
```

### Seed Database with Test Data

Populate the database with 50 sample records for each model:

```bash
php artisan db:seed
```

This will create:
- 50 Users (1 admin + 49 random users)
- 50 Stock items (various tire brands and types)
- 50 Categories/Brands (with placeholder images)
- 50 Sales records (linked to stocks)

**Admin Login:**
- Email: `admin@example.com`
- Password: `password`

### Fresh Installation (Reset Database)

To start fresh with seeded data:

```bash
php artisan migrate:fresh --seed
```

### Create Storage Link

Create symbolic link for file uploads:

```bash
php artisan storage:link
```

This links `public/storage` to `storage/app/public` for image access.

## 🚀 Running the Application

### Development Mode

#### Option 1: Separate Commands (Recommended for Windows)

**Terminal 1** - Start Laravel development server:
```bash
php artisan serve
```

**Terminal 2** - Start Vite development server:
```bash
npm run dev
```

#### Option 2: Concurrent (Single Command)

```bash
composer run dev
```

This runs both servers concurrently with queue workers and logs.

### Access the Application

Open your browser and navigate to:
```
http://localhost:8000
```

## 📁 Project Structure

```
project/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── CategoryController.php    # Brand/Category management
│   │       ├── StockController.php       # Stock management
│   │       ├── SaleController.php        # Sales tracking
│   │       └── ReportController.php      # Reports & analytics
│   └── Models/
│       ├── User.php                      # User model
│       ├── Stock.php                     # Stock model
│       ├── Category.php                  # Category/Brand model
│       └── Sale.php                      # Sale model
├── database/
│   ├── factories/                        # Model factories for testing
│   │   ├── UserFactory.php
│   │   ├── StockFactory.php
│   │   ├── CategoryFactory.php
│   │   └── SaleFactory.php
│   ├── migrations/                       # Database migrations
│   └── seeders/                          # Database seeders
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── StockSeeder.php
│       ├── CategorySeeder.php
│       └── SaleSeeder.php
├── resources/
│   ├── views/
│   │   ├── categories/                   # Brand views
│   │   ├── stocks/                       # Stock views
│   │   ├── sales/                        # Sales views
│   │   └── reports/                      # Report views
│   ├── css/
│   └── js/
├── public/
│   └── storage/                          # Symlinked storage (after storage:link)
├── storage/
│   └── app/
│       └── public/
│           └── categories/               # Uploaded brand images
├── routes/
│   └── web.php                           # Web routes
├── .env.example                          # Environment template
├── composer.json                         # PHP dependencies
├── package.json                          # Node dependencies
└── README.md                             # This file
```

## 📖 Usage

### Stock Management

1. **Add New Stock**
   - Navigate to "Stock" menu
   - Click "Add Stock"
   - Fill in tire details (name, brand, code, quantity, price)
   - Submit

2. **Update Stock**
   - Click edit icon on any stock item
   - Modify details
   - Save changes

3. **Delete Stock**
   - Click delete icon
   - Confirm deletion

### Brand/Category Management

1. **Add Brand**
   - Navigate to "Categories" menu
   - Click "Add Brand"
   - Enter brand name, year, count
   - Upload brand image (optional)
   - See live preview of image before upload
   - Submit

2. **Edit Brand**
   - Click edit icon
   - Modify details or upload new image
   - Check "Remove image" to delete current image
   - Save changes

### Sales Management

1. **Record Sale**
   - Navigate to "Sales" menu
   - Click "Add Sale"
   - Select stock item
   - Enter quantity and price
   - Total is calculated automatically
   - Submit

2. **View Sales History**
   - Browse all sales records
   - Filter by date or stock item
   - View total revenue

### Reports

1. **Stock Report**
   - View current stock levels
   - Identify low stock items
   - Export data

2. **Sales Report**
   - Filter by date range
   - View sales trends
   - Analyze revenue

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

Or using Composer:

```bash
composer test
```

### Code Quality

Run Laravel Pint for code formatting:

```bash
./vendor/bin/pint
```

## 🌐 Deployment

### Production Build

1. **Build Assets**
```bash
npm run build
```

2. **Optimize Application**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Set Environment**
```env
APP_ENV=production
APP_DEBUG=false
```

### Using Docker

Build and run with Docker:

```bash
docker build -t tire-management .
docker run -p 8000:8000 tire-management
```

### Deployment Platforms

- **Vercel/Netlify**: Use `render.yaml` configuration
- **Heroku**: Push to Heroku Git
- **DigitalOcean**: Use App Platform
- **AWS**: Use Elastic Beanstalk or EC2

## 🔍 Troubleshooting

### Images Not Displaying

```bash
# Create storage link
php artisan storage:link

# Check permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Database Connection Issues

```bash
# Clear config cache
php artisan config:clear

# Verify database file exists (SQLite)
ls -la database/database.sqlite
```

### Vite/Asset Issues

```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install

# Rebuild assets
npm run build
```

### Permission Errors

```bash
# Linux/Mac
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache

# Windows (Run as Administrator)
icacls storage /grant Users:F /t
icacls bootstrap/cache /grant Users:F /t
```

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards
- Write descriptive commit messages
- Add tests for new features
- Update documentation

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👥 Authors

- **Your Name** - *Initial work*

## 🙏 Acknowledgments

- Built with [Laravel 12](https://laravel.com)
- UI components from [Bootstrap 5](https://getbootstrap.com)
- Icons by [Font Awesome](https://fontawesome.com)
- Styling with [TailwindCSS 4](https://tailwindcss.com)

## 📞 Support

For support, email your-email@example.com or open an issue in the repository.

---

**Happy Coding! 🚀**
