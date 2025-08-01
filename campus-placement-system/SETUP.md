# Campus Placement Management System - Setup Guide

## System Requirements

- **Web Server**: Apache/Nginx with PHP 7.4 or higher
- **Database**: MySQL 5.7 or higher / MariaDB 10.3 or higher
- **PHP Extensions**: PDO, PDO_MySQL, JSON, mbstring, fileinfo
- **Storage**: Minimum 500MB for application files and uploads

## Installation Steps

### 1. Download and Extract Files

1. Download the project files
2. Extract to your web server directory (e.g., `/var/www/html/` or `C:\xampp\htdocs\`)
3. Ensure the web server has read/write permissions for the project directory

### 2. Database Setup

#### Using MySQL Workbench (Recommended):

1. Open MySQL Workbench
2. Connect to your MySQL server
3. Open the file `config/database.sql`
4. Execute the script to create the database and tables
5. Verify that the database `Placement_db` was created with all tables

#### Using Command Line:

```bash
mysql -u root -p < config/database.sql
```

#### Using phpMyAdmin:

1. Open phpMyAdmin in your browser
2. Create a new database named `Placement_db`
3. Import the `config/database.sql` file

### 3. Configure Database Connection

1. Open `config/database.php`
2. Update the database credentials:

```php
define('DB_HOST', 'localhost');     // Your MySQL host
define('DB_USERNAME', 'root');      // Your MySQL username  
define('DB_PASSWORD', '');          // Your MySQL password
define('DB_NAME', 'Placement_db');  // Database name (keep as is)
```

### 4. Set Directory Permissions

Ensure the following directories have write permissions:

```bash
chmod 755 assets/uploads/resumes/
chmod 755 assets/uploads/company_logos/
chmod 755 config/
```

### 5. Web Server Configuration

#### For Apache:

Create/update `.htaccess` file in the project root:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
Header always set X-XSS-Protection "1; mode=block"

# File upload security
<FilesMatch "\.(php|php3|php4|php5|phtml)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

<Directory "assets/uploads/">
    <FilesMatch "\.(php|php3|php4|php5|phtml)$">
        Order Allow,Deny
        Deny from all
    </FilesMatch>
</Directory>
```

#### For Nginx:

Add to your nginx configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/campus-placement-system;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location /assets/uploads/ {
        location ~ \.php$ {
            deny all;
        }
    }
}
```

## Default Login Credentials

### Developer Account (Pre-configured):
- **Email**: vishalsharma08555252@gmail.com
- **Password**: Vishal@178

This is the master developer account that can create Super Admins for colleges.

## User Hierarchy

1. **Developer** → Creates Super Admins for colleges
2. **Super Admin** → Manages college admins and students
3. **Admin** → Manages students, companies, and job postings
4. **Student** → Views jobs, applies, manages profile
5. **Company** → Posts jobs, views applications, schedules interviews

## Post-Installation Steps

### 1. Access the System

1. Open your browser and navigate to your installation URL
2. Log in using the developer credentials
3. The system will redirect you to the developer dashboard

### 2. Create Your First College

1. Go to "Manage Colleges" in the developer dashboard
2. Click "Add New College"
3. Fill in the college details
4. Save the college

### 3. Create Super Admin

1. Go to "Super Admins" section
2. Click "Create Super Admin"
3. Fill in the super admin details
4. Assign them to the college you just created
5. The super admin can now log in and manage their college

### 4. Email Configuration (Optional)

To enable email notifications, update the email settings in the system settings or modify the email configuration in your PHP environment.

## File Structure

```
campus-placement-system/
├── api/                    # API endpoints
│   ├── login.php
│   ├── get-colleges.php
│   ├── add-college.php
│   └── add-superadmin.php
├── assets/                 # Upload directories
│   └── uploads/
│       ├── resumes/
│       └── company_logos/
├── config/                 # Configuration files
│   ├── database.php
│   └── database.sql
├── css/                    # Stylesheets
│   └── style.css
├── developer/              # Developer dashboard
│   └── dashboard.php
├── includes/               # PHP includes
│   └── auth.php
├── js/                     # JavaScript files
│   └── main.js
├── admin/                  # Admin dashboard (to be created)
├── student/                # Student dashboard (to be created)
├── company/                # Company dashboard (to be created)
├── superadmin/             # Super admin dashboard (to be created)
├── index.php               # Login page
└── SETUP.md               # This file
```

## Security Considerations

1. **Change Default Password**: Immediately change the developer password after installation
2. **SSL Certificate**: Use HTTPS in production
3. **File Permissions**: Ensure proper file permissions (755 for directories, 644 for files)
4. **Regular Backups**: Set up automated database backups
5. **Update Regularly**: Keep PHP, MySQL, and web server updated

## Troubleshooting

### Database Connection Issues:

1. Verify MySQL service is running
2. Check database credentials in `config/database.php`
3. Ensure the database user has proper permissions
4. Check if the database `Placement_db` exists

### Permission Issues:

1. Check web server error logs
2. Verify directory permissions for uploads folder
3. Ensure PHP has write access to the project directory

### Login Issues:

1. Clear browser cache and cookies
2. Check if sessions are working in PHP
3. Verify the user exists in the database

### File Upload Issues:

1. Check PHP upload limits in `php.ini`:
   - `upload_max_filesize = 10M`
   - `post_max_size = 10M`
   - `max_file_uploads = 20`
2. Verify upload directory permissions

## Support

For technical support or questions:

1. Check the browser console for JavaScript errors
2. Check web server error logs
3. Check PHP error logs
4. Verify database connectivity

## Features Overview

### Developer Features:
- Manage colleges and super admins
- System settings and configuration
- View activity logs and statistics
- Database backup and export

### Super Admin Features:
- Manage college admins and students
- Oversee placement activities
- Generate college-specific reports

### Admin Features:
- Manage student profiles
- Manage company partnerships
- Create and manage job postings
- Schedule interviews
- Generate placement reports

### Student Features:
- Update profile and upload resume
- View available job opportunities
- Apply to jobs
- Track application status
- View interview schedules

### Company Features:
- Create company profile
- Post job opportunities
- View and manage applications
- Schedule interviews
- View candidate profiles

## Database Schema

The system uses 11 main tables:
- `user_roles` - User role definitions
- `users` - All system users
- `colleges` - College information
- `students` - Student profiles
- `companies` - Company profiles
- `job_postings` - Job opportunities
- `applications` - Job applications
- `interview_schedules` - Interview scheduling
- `notifications` - System notifications
- `system_settings` - Configuration settings
- `email_templates` - Email templates

## Next Steps

After successful installation:

1. Create colleges and super admins
2. Test the login system with different user roles
3. Configure email settings for notifications
4. Customize the system according to your needs
5. Set up regular database backups
6. Configure SSL certificate for production use

The system is now ready for use!