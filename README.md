# Supabase Clone - Upload Ready

A complete authentication system built with PHP, MySQL, and vanilla JavaScript that mimics Supabase functionality.

## Features

- User registration with email validation
- Secure password hashing using PHP's `password_hash()`
- Session-based authentication
- Protected dashboard page
- Clean, responsive UI
- JSON API responses
- MySQL database integration

## Installation

1. Upload all files to your web server
2. Ensure your MySQL database is set up with the credentials in `api/auth/db.php`
3. The system will automatically create the `users` table on first run

## Database Configuration

The system is configured for these database credentials:
- Host: localhost
- Database: webhostp_supabase_user
- Username: webhostp_supabase_user
- Password: PowerSplash2025!

## File Structure

```
supabase-clone/
├── register.html         # User registration page
├── login.html            # User login page
├── dashboard.html        # Protected dashboard (PHP)
├── logout.php            # Session logout handler
├── README.md             # This file
└── api/
    └── auth/
        ├── db.php         # Database connection
        ├── register.php   # Registration API endpoint
        └── login.php      # Login API endpoint
```

## Usage

1. Visit `register.html` to create a new account
2. Visit `login.html` to sign in
3. Access `dashboard.html` to view the protected area
4. Use the logout button to end your session

## Security Features

- Password hashing with `password_hash()`
- SQL injection prevention with prepared statements
- Input validation and sanitization
- Session management
- CSRF protection through session validation

## API Endpoints

### POST /api/auth/register.php
Register a new user account.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "securepassword"
}
```

### POST /api/auth/login.php
Authenticate user and create session.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "securepassword"
}
```

## Browser Support

- Modern browsers with ES6+ support
- JavaScript fetch API required
- Cookies enabled for session management

## Ready for Production

This system is ready to upload to any web server with PHP 7.4+ and MySQL support.