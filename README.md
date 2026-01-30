# Travel Agency Management System

A comprehensive PHP-based travel agency management system with MySQL database, featuring user registration/authentication, tour management, booking system, reviews, wishlists, and admin panel.

## Features

### User Management
- User registration with email validation and password hashing
- Secure login system with session management
- User profile management with profile image upload
- User dashboard showing bookings and statistics

### Tour & Destination Management
- Browse all destinations with filtering
- View comprehensive tour listings with advanced search
- Filter tours by destination, price range, duration
- Detailed tour pages with gallery, itinerary, reviews, and availability
- Rating system based on customer reviews

### Booking System
- Easy tour booking with date selection and number of people
- Automatic capacity checking and booking code generation
- Booking confirmation with detailed information
- Booking history with status filtering (pending, confirmed, completed, cancelled)
- Special requests field for custom requirements

### Reviews System
- Submit reviews only for completed bookings
- 5-star rating system
- Automatic tour rating calculation from reviews
- Display reviews with reviewer information

### Wishlist Functionality
- Add/remove tours from wishlist
- View all saved tours
- AJAX-based wishlist management

### Payment System
- Secure payment form with card details
- Multiple payment methods (Credit Card, Debit Card, PayPal)
- Payment confirmation and receipt
- Transaction ID generation

### Admin Panel
- Admin dashboard with statistics
- Manage destinations (CRUD operations)
- Manage tours (CRUD operations)
- Manage bookings and update statuses
- View and manage users
- Activity logging

### Security Features
- Password hashing using bcrypt
- Input validation and sanitization
- Prepared statements for all database queries
- Session-based authentication
- Role-based access control

### Design & UX
- Responsive Bootstrap 5 design
- Mobile-friendly interface
- Smooth animations and transitions
- Professional color scheme
- Intuitive navigation

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web Server (Apache/Nginx)

### Setup Steps

1. **Create Database**
   ```sql
   CREATE DATABASE travel_db;
   ```

2. **Import Database Schema**
   ```bash
   mysql -u root -p travel_db < database/travel_db_schema.sql
   ```

3. **Configure Database Connection**
   Edit `config/database.php` and update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASSWORD', '');
   define('DB_NAME', 'travel_db');
   ```

4. **Create Upload Directories**
   ```bash
   mkdir -p uploads/profiles
   mkdir -p uploads/tours
   chmod 755 uploads/profiles
   chmod 755 uploads/tours
   ```

5. **Configure PHP Settings**
   Update `php.ini` if needed:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

6. **Access the Application**
   - User Site: `http://localhost/travel_agency_1/`
   - Admin Login: `http://localhost/travel_agency_1/admin/admin-login.php`

## Demo Account

### User Account
- Email: user@travel.com
- Password: User@123

### Admin Account
- Email: admin@travel.com
- Password: Admin@123

## Directory Structure

```
travel_agency_1/
├── config/
│   └── database.php              # MySQLi database configuration
├── includes/
│   ├── header.php               # Navigation and page header
│   ├── footer.php               # Page footer
│   ├── functions.php            # Reusable PHP functions
│   ├── auth-check.php           # User authentication middleware
│   └── admin-auth-check.php     # Admin authentication middleware
├── users/
│   ├── user-registration.php    # User registration form
│   ├── user-login.php           # User login page
│   ├── user-profile.php         # User profile management
│   ├── user-dashboard.php       # User dashboard
│   └── logout.php               # Logout handler
├── tours/
│   ├── destinations.php         # Destination listing
│   ├── tour-list.php            # Tour listing with filters
│   ├── tour-detail.php          # Single tour detail page
│   └── tour-search.php          # Advanced tour search
├── bookings/
│   ├── booking-form.php         # Booking form
│   ├── booking-confirmation.php # Booking confirmation
│   ├── my-bookings.php          # User's booking history
│   └── booking-details.php      # Single booking details
├── reviews/
│   └── add-review.php           # Submit/edit review
├── wishlist/
│   ├── add-to-wishlist.php      # AJAX wishlist handler
│   └── my-wishlist.php          # Wishlist display
├── payments/
│   ├── payment-form.php         # Payment information form
│   └── payment-receipt.php      # Payment confirmation receipt
├── admin/
│   ├── admin-login.php          # Admin login
│   ├── admin-dashboard.php      # Admin dashboard
│   ├── manage-destinations.php  # Destination management
│   ├── manage-tours.php         # Tour management
│   ├── manage-bookings.php      # Booking management
│   └── manage-users.php         # User management
├── assets/
│   ├── css/
│   │   └── style.css            # Custom CSS styles
│   ├── js/
│   │   └── script.js            # Custom JavaScript
│   └── images/                  # Static images
├── uploads/
│   ├── profiles/                # User profile images
│   └── tours/                   # Tour and destination images
├── database/
│   └── travel_db_schema.sql     # Database schema
├── index.php                    # Home page
└── README.md                    # This file
```

## Database Schema

### Tables
- **users** - User accounts and profiles
- **destinations** - Travel destinations
- **tours** - Tour packages and details
- **tourGallery** - Tour images gallery
- **bookings** - Customer bookings
- **payments** - Payment records
- **reviews** - Customer reviews and ratings
- **wishlist** - User wishlist items
- **activityLog** - Admin activity logging

## Key Functions

### Authentication Functions
- `hashPassword()` - Hash password using bcrypt
- `verifyPassword()` - Verify password against hash
- `isSessionValid()` - Check if user session is valid
- `getCurrentUserId()` - Get current logged-in user ID
- `getCurrentUserRole()` - Get current user role

### Database Functions
- `generateBookingCode()` - Generate unique booking code
- `generateTransactionId()` - Generate unique transaction ID
- `logAdminActivity()` - Log admin actions

### Validation Functions
- `validateEmail()` - Validate email format
- `validatePassword()` - Validate password strength
- `validatePhone()` - Validate phone number
- `sanitize()` - Sanitize user input

### Display Functions
- `formatCurrency()` - Format currency for display
- `formatDate()` - Format date for display
- `getRatingStars()` - Display rating as stars
- `getBookingStatusBadge()` - Display booking status badge
- `getPaymentStatusBadge()` - Display payment status badge

### Pagination
- `getPaginationData()` - Calculate pagination information
- `getPaginationHTML()` - Generate pagination HTML

## API Endpoints

### User Pages
- GET `/users/user-registration.php` - Registration form
- POST `/users/user-registration.php` - Submit registration
- GET `/users/user-login.php` - Login form
- POST `/users/user-login.php` - Submit login
- GET `/users/user-profile.php` - User profile page
- POST `/users/user-profile.php` - Update profile
- GET `/users/logout.php` - Logout

### Tour Pages
- GET `/tours/destinations.php` - Browse destinations
- GET `/tours/tour-list.php` - Browse tours with filters
- GET `/tours/tour-detail.php?id=X` - View tour details
- GET `/tours/tour-search.php` - Advanced search

### Booking Pages
- GET `/bookings/booking-form.php?tour_id=X` - Booking form
- POST `/bookings/booking-form.php` - Create booking
- GET `/bookings/booking-confirmation.php?code=X` - Confirmation
- GET `/bookings/my-bookings.php` - Booking history
- GET `/bookings/booking-details.php?id=X` - Booking details

### Review Pages
- GET `/reviews/add-review.php?tour_id=X` - Review form
- POST `/reviews/add-review.php` - Submit review

### Wishlist Pages
- POST `/wishlist/add-to-wishlist.php` - Add/remove from wishlist (AJAX)
- GET `/wishlist/my-wishlist.php` - View wishlist

### Payment Pages
- GET `/payments/payment-form.php?booking_id=X` - Payment form
- POST `/payments/payment-form.php` - Process payment
- GET `/payments/payment-receipt.php?booking_id=X` - Payment receipt

### Admin Pages
- GET `/admin/admin-login.php` - Admin login
- POST `/admin/admin-login.php` - Submit login
- GET `/admin/admin-dashboard.php` - Admin dashboard
- GET `/admin/manage-destinations.php` - Manage destinations
- GET `/admin/manage-tours.php` - Manage tours
- GET `/admin/manage-bookings.php` - Manage bookings
- GET `/admin/manage-users.php` - Manage users

## Security Notes

1. **Password Security**
   - All passwords are hashed using PHP's `password_hash()` with bcrypt
   - Minimum 8 characters with uppercase, lowercase, and numbers required

2. **SQL Injection Prevention**
   - All database queries use prepared statements
   - User input is properly bound to prevent SQL injection

3. **XSS Prevention**
   - All user input is sanitized using `htmlspecialchars()`
   - Output is properly escaped before display

4. **Session Security**
   - Sessions are used for authentication
   - Admin-only pages require role verification
   - Activity logging tracks admin actions

5. **File Upload Security**
   - File type validation (only images allowed)
   - File size limits enforced
   - Files stored outside web root recommended

## Customization

### Changing Colors
Edit `assets/css/style.css` and update CSS variables:
```css
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;
}
```

### Adding New Amenities
Edit tour form to add amenities as JSON array:
```php
$amenities = json_encode(['WiFi', 'Swimming Pool', 'Breakfast Included']);
```

### Configuring Payment Gateway
Modify `payments/payment-form.php` to integrate actual payment gateway (Stripe, PayPal, etc.)

## Troubleshooting

### Database Connection Issues
- Verify MySQL is running
- Check database credentials in `config/database.php`
- Ensure database and tables exist

### Upload Directory Issues
- Verify `uploads/` directories exist and are writable
- Check file permissions: `chmod 755 uploads/profiles`

### Session Issues
- Ensure sessions directory is writable
- Check `session.save_path` in `php.ini`

### Email Not Sending
- Currently emails are not implemented (would need PHPMailer)
- You can integrate email functionality using PHPMailer or SendGrid

## Performance Optimization

1. **Database Indexes** - All foreign keys and frequently searched columns have indexes
2. **Query Optimization** - Prepared statements and efficient joins used
3. **Caching** - Implement Redis for session caching (optional)
4. **Image Optimization** - Compress images before upload

## Future Enhancements

- Email notifications for bookings and reviews
- Multi-language support
- Advanced analytics and reporting
- Mobile app API
- Payment gateway integration (Stripe, PayPal)
- Itinerary planning and PDF generation
- Travel insurance options
- Group booking discounts
- Email marketing integration
- SMS notifications

## Support

For issues or questions, please contact: support@travelagency.com

## License

This project is created for educational purposes.
