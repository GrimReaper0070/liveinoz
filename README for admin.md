# Live in Oz - Installation and Admin Setup

## Installation/Setup Steps

### Prerequisites
- PHP 7.0 or higher
- MySQL 5.7 or higher
- XAMPP (recommended) or any web server with PHP and MySQL support
- Web browser

### Setup Instructions
1. **Download/Clone the Project**
   - Place the entire project folder in your web server's document root directory
   - For XAMPP: Place in `C:\xampp\htdocs\oznewfinal`
   - For other servers: Place in the appropriate public_html or www directory

2. **Start Services**
   - Start Apache and MySQL in XAMPP control panel
   - Or start your web server and MySQL service

3. **Database Setup**
   - Open your web browser
   - Navigate to: `http://localhost/oznewfinal/database_setup.php`
   - This will automatically create all necessary database tables and insert the admin user

4. **Access the Application**
   - Main site: `http://localhost/oznewfinal/index.html`
   - The application is now ready to use!

## Admin Access

### Admin Login
- **URL**: `http://localhost/oznewfinal/admin/login.php`
- **Email**: `h@h.com`
- **Password**: `123456`

### Changing Admin Access
To grant or remove admin privileges from users:

1. **Login to Admin Panel**
   - Go to `http://localhost/oznewfinal/admin/login.php`
   - Use the admin credentials above

2. **Access User Management**
   - Click on "User Management" in the sidebar
   - This takes you to the user management page (`admin/users.php`)

3. **Change Admin Privileges**
   - Find the user you want to modify in the user list
   - To **grant admin access**: Click the "Make Admin" button next to the user
   - To **remove admin access**: Click the "Remove Admin" button next to the user

**Important Notes:**
- You cannot remove admin privileges from your own account
- Admin users have full access to all platform features
- Be careful when granting admin access to maintain security

### Admin Features
The admin panel provides access to:
- User Management (view, edit, block users)
- Room Approval and Management
- Marketplace Item Reviews
- Chat Room Monitoring
- Blog Post Management
- Reports and Analytics
- Subscription Management
- WhatsApp Group Links
- System Settings

Login to the admin panel using the credentials above to manage the platform.
