# LocalSEO Installation Guide

This document provides instructions on how to install and configure the LocalSEO system.

## Requirements

- PHP 8.1+
- MySQL 5.7+ or MariaDB 10.2+
- Composer
- Node.js (for asset compilation)
- Web server (Apache or Nginx)

## Installation Options

You have two ways to install the system:

### Option 1: Automatic Installation (Recommended)

This method will handle everything automatically:

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/localseo.git
   cd localseo
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```

4. Edit the `.env` file to configure your database connection and other settings.

5. Generate an application key:
   ```bash
   php artisan key:generate
   ```

6. Run the installation command:
   ```bash
   php artisan system:install
   ```
   
   This will guide you through the installation process. Follow the on-screen instructions to set up your admin user.

7. Optionally, you can pre-specify installation options:
   ```bash
   php artisan system:install --admin-email=admin@example.com --admin-name="Admin User" --admin-password=yourpassword --seed
   ```

### Option 2: Manual Installation

If you prefer to install manually:

1. Follow steps 1-5 from Option 1 above.

2. Run the database migrations:
   ```bash
   php artisan migrate
   ```

3. Create an admin user:
   ```bash
   php artisan admin:create
   ```

4. Synchronize user roles:
   ```bash
   php artisan users:sync-roles
   ```

5. Optimize the application:
   ```bash
   php artisan optimize
   php artisan storage:link
   ```

## User Roles and Permissions

The system has three default roles:

1. **Admin**:
   - Full access to all features
   - Can manage users and roles
   - Can add, edit, and delete sites
   - Can approve new user registrations

2. **Editor**:
   - Can add, edit, and delete sites
   - Can approve new user registrations
   - Cannot manage roles

3. **User**:
   - Can only view and filter sites
   - Cannot add, edit, or delete sites

## User Registration Process

1. New users register with a default status of 'inactive'
2. Admin or Editor must approve new user registrations
3. After approval, users can log in to the system
4. Only Admin can change user roles

## Troubleshooting

### Role Issues

If users have incorrect permissions, run:
```bash
php artisan users:sync-roles --force
```

### Create a New Admin

If you need to create another admin user:
```bash
php artisan admin:create --force
```

### Clear Application Cache

If changes don't appear to take effect:
```bash
php artisan optimize:clear
```

## Support

For support, please create an issue on the GitHub repository or contact the system administrator. 