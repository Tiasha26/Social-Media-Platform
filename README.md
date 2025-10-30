# Social Media Platform - User Guide

## 📋 Project Overview

This is a fully functional social media platform built with PHP, MySQL, HTML5, CSS3, and JavaScript. The platform allows users to register, login, create posts with images, search for other users, send private messages, and manage their profiles.

## 🚀 Features

- **User Authentication**
  - Secure registration with password hashing
  - Login system with session management
  - Profile picture upload (optional)
  
- **User Dashboard**
  - Create text posts with optional images
  - View all posts in reverse chronological order
  - Real-time image preview before posting
  
- **User Profiles**
  - View user information and posts
  - Edit own profile (name and picture)
  - Public timeline for each user
  
- **Search Functionality**
  - Search users by name or email
  - Live search with animations
  - Click to view user profiles
  
- **Private Messaging**
  - Send messages to any user
  - View sent and received messages
  - Conversation-style message display
  
- **Security Features**
  - SQL injection prevention (PDO prepared statements)
  - XSS protection (output sanitization)
  - Password hashing (bcrypt)
  - Session security
  - File upload validation

## 📁 Project Structure

```
social_media_platform/
├── css/
│   └── style.css
├── js/
│   ├── validation.js
│   ├── dashboard.js
│   ├── search.js
│   └── messages.js
├── includes/
│   ├── config.php
│   └── navbar.php
├── uploads/
│   └── default_avatar.png
├── database.sql
├── index.php
├── register.php
├── login.php
├── logout.php
├── dashboard.php
├── profile.php
├── search.php
├── messages.php
└── README.md
```

## 🔧 Installation Instructions

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Step 1: Setup Web Server

**For Windows (XAMPP/WAMP):**
1. Install XAMPP or WAMP Server
2. Start Apache and MySQL services

**For Mac (MAMP):**
1. Install MAMP
2. Start servers

**For Linux (LAMP):**
```bash
sudo apt update
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql
sudo systemctl start apache2
sudo systemctl start mysql
```

### Step 2: Create Project Directory

1. Navigate to your web server's document root:
   - XAMPP: `C:\xampp\htdocs\`
   - WAMP: `C:\wamp\www\`
   - MAMP: `/Applications/MAMP/htdocs/`
   - Linux: `/var/www/html/`

2. Create a new folder called `social_media_platform`

3. Extract all project files into this folder

### Step 3: Create Database

1. Open phpMyAdmin in your browser:
   - URL: `http://localhost/phpmyadmin`

2. Click on "Import" tab

3. Click "Choose File" and select `database.sql`

4. Click "Go" to execute the SQL file

**OR** manually create the database:

1. Click "New" in phpMyAdmin
2. Database name: `social_media_platform`
3. Collation: `utf8mb4_general_ci`
4. Click "Create"
5. Open the SQL tab and paste the contents of `database.sql`
6. Click "Go"

### Step 4: Configure Database Connection

1. Open `includes/config.php`

2. Update database credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Change if you have a password
define('DB_NAME', 'social_media_platform');
```

### Step 5: Create Uploads Directory

1. In the project root, create a folder called `uploads`

2. Set proper permissions (Linux/Mac):
```bash
chmod 755 uploads
```

3. Add a default avatar image:
   - Name it `default_avatar.png`
   - Place it in the `uploads` folder
   - Use any generic avatar image (150x150px recommended)

### Step 6: Test Installation

1. Open your browser and navigate to:
   ```
   http://localhost/social_media_platform
   ```

2. You should be redirected to the login page

## 👤 Test Accounts

The database comes with two pre-installed test accounts:

**Account 1:**
- Username: `Joe Rogan`
- Email: `joe.rogan@gmail.com`
- Password: `Test@123`

**Account 2:**
- Username: `Cleve`
- Email: `cleve.davey@gmail.com`
- Password: `Test@123`

## 📖 How to Use

### Creating a New Account

1. Navigate to `http://localhost/social_media_platform/register.php`
2. Fill in all required fields:
   - Username (3-50 characters)
   - Email (valid email format)
   - Full Name
   - Password (minimum 6 characters)
   - Confirm Password
   - Profile Picture (optional)
3. Click "Register"
4. You'll be redirected to login page upon success

### Logging In

1. Navigate to `http://localhost/social_media_platform/login.php`
2. Enter your username or email
3. Enter your password
4. Click "Login"
5. You'll be redirected to your dashboard

### Creating a Post

1. On the dashboard, find the post creator at the top
2. Type your message in the text area
3. Optionally, click "📷 Add Photo" to upload an image
4. Preview your image before posting
5. Click "POST" to share

### Searching for Users

1. Click "Search Users" in the navigation menu
2. Enter a name or email in the search box
3. Click "Search"
4. Click on any user result to view their profile

### Sending Messages

1. Click "Messages" in the navigation menu
2. Enter the recipient's username
3. Type your message
4. Click "Send"
5. View your sent and received messages below

### Viewing and Editing Profile

1. Click your avatar in the navigation menu
2. View your profile information and posts
3. Click "Edit Profile" to update:
   - Full Name
   - Profile Picture
4. Click "Update Profile" to save changes

### Logging Out

1. Click "Logout" in the navigation menu
2. You'll be redirected to the login page

## 🔒 Security Features

### Implemented Security Measures

1. **SQL Injection Prevention**
   - All database queries use PDO prepared statements
   - User input is never directly inserted into queries

2. **XSS Protection**
   - All output is sanitized using `htmlspecialchars()`
   - User-generated content is escaped before display

3. **Password Security**
   - Passwords are hashed using `password_hash()` with bcrypt
   - Passwords are never stored in plain text

4. **Session Security**
   - Session ID regeneration after login
   - HTTP-only session cookies
   - Proper session destruction on logout

5. **File Upload Security**
   - File type validation (images only)
   - File size restrictions (5MB max)
   - Unique filename generation
   - Proper file permissions

6. **Form Validation**
   - Client-side JavaScript validation
   - Server-side PHP validation
   - Input sanitization and filtering

## 🐛 Troubleshooting

### Database Connection Errors

**Problem:** "Database connection failed"

**Solutions:**
- Check if MySQL service is running
- Verify database credentials in `config.php`
- Ensure database `social_media_platform` exists
- Check if PDO MySQL extension is enabled in PHP

### Upload Directory Errors

**Problem:** "Failed to upload image"

**Solutions:**
- Ensure `uploads/` directory exists
- Check directory permissions (755 or 777)
- Verify `upload_max_filesize` in php.ini
- Check `post_max_size` in php.ini

### Session Errors

**Problem:** "Headers already sent" error

**Solutions:**
- Ensure no output before `session_start()`
- Check for whitespace before `<?php` tags
- Verify BOM is not present in files
- Save files with UTF-8 without BOM encoding

### Page Not Found (404)

**Problem:** Cannot access pages

**Solutions:**
- Check if Apache is running
- Verify document root path
- Ensure all files are in correct locations
- Check file permissions

### Image Upload Not Working

**Problem:** Images not uploading

**Solutions:**
- Check `uploads/` folder exists and is writable
- Verify `file_uploads = On` in php.ini
- Increase `upload_max_filesize` in php.ini
- Increase `post_max_size` in php.ini
- Check disk space

## 📝 Additional Notes

### PHP Configuration

Recommended php.ini settings:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

### Browser Compatibility

Tested and working on:
- Google Chrome 90+
- Mozilla Firefox 88+
- Microsoft Edge 90+
- Safari 14+

### Mobile Responsiveness

The platform is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

## 👨‍💻 Development Information

### Technologies Used

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Security:** PDO, Password Hashing, Input Sanitization
- **No Frameworks:** Built entirely from scratch

### Code Organization

- **Modular design** with reusable functions
- **Separation of concerns** (MVC-inspired)
- **Commented code** for easy understanding
- **Consistent naming conventions**
- **Security-first approach**

## 📞 Support

If you encounter any issues:

1. Check this README file
2. Review error messages carefully
3. Check server logs (Apache/PHP error logs)
4. Verify all installation steps were followed
5. Ensure all prerequisites are met

## 🎓 Learning Outcomes

This project demonstrates:

- PHP fundamentals and advanced concepts
- MySQL database design and queries
- Secure coding practices
- User authentication systems
- File upload handling
- Session management
- Form validation (client and server-side)
- Responsive web design
- JavaScript DOM manipulation
- Security best practices

## ✅ Assignment Completion Checklist

- ✅ User registration with validation
- ✅ Secure login system
- ✅ Password hashing
- ✅ Session management
- ✅ User dashboard/timeline
- ✅ Post creation with text and images
- ✅ User profile pages
- ✅ Profile editing functionality
- ✅ User search functionality
- ✅ Private messaging system
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ File upload security
- ✅ No frameworks used
- ✅ Clean, commented code
- ✅ Responsive design
- ✅ Form validation (client & server)
- ✅ Proper file organization
- ✅ Database with proper relationships
- ✅ README documentation

---

**© 2024 Social Media Platform - Internet Programming 622 Assignment**