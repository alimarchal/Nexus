# Nexus - Laravel Business Management System

A comprehensive Laravel-based business management system designed for organizational operations, audit management, complaint handling, and branch administration.

## 📋 Project Overview

Nexus is a full-featured web application built with Laravel 12 that provides:

- **Audit Management System** - Complete audit lifecycle management with findings, risks, and compliance tracking
- **Complaint Management** - Handle customer complaints with categorization, escalation, and resolution tracking
- **Branch & Regional Management** - Manage organizational structure with regions, districts, and branches
- **Stationery Management** - Track printed materials, transactions, and dispatch registers
- **Employee Resources** - Manage employee resources and organizational divisions
- **User Management** - Role-based access control with comprehensive permissions
- **Reporting & Analytics** - Generate various business reports and insights

## 🔧 System Requirements

Before installing Nexus, ensure your system meets the following requirements:

### Required Software
- **PHP 8.2 or higher** with the following extensions:
  - BCMath PHP Extension
  - Ctype PHP Extension
  - cURL PHP Extension
  - DOM PHP Extension
  - Fileinfo PHP Extension
  - JSON PHP Extension
  - Mbstring PHP Extension
  - OpenSSL PHP Extension
  - PCRE PHP Extension
  - PDO PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
- **Composer 2.0+** - PHP dependency manager
- **Node.js 18.0+** and **npm 8.0+** - For asset compilation
- **Database** - One of the following:
  - MySQL 8.0+ (recommended)
  - PostgreSQL 12+
  - SQLite 3.8.8+
  - SQL Server 2017+
- **Web Server** - Apache 2.4+ or Nginx 1.15+ (for production)

### Optional Requirements
- **Redis** - For caching and queues (recommended for production)
- **Supervisor** - For queue worker management (production)

## 🚀 Installation Instructions

Follow these step-by-step instructions to set up Nexus on your local development environment:

### 1. Clone the Repository

```bash
git clone https://github.com/alimarchal/Nexus.git
cd Nexus
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit the `.env` file with your specific configuration:

```env
APP_NAME=Nexus
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

#### Create Database
First, create a database named `nexus` (or your chosen name) in your database server.

#### Run Migrations and Seeders
**Important**: Use the following command to set up the database with initial data:

```bash
php artisan migrate:fresh --seed
```

This command will:
- Drop all existing tables (if any)
- Run all migrations to create the database structure
- Seed the database with essential data including:
  - Regions, districts, and branches
  - User roles and permissions
  - Complaint categories and status types
  - Audit types and checklists
  - Division structures
  - Sample data for development

### 7. Storage Link

Create a symbolic link for file storage:

```bash
php artisan storage:link
```

### 8. File Permissions Setup

Set proper permissions for storage and cache directories:

#### Linux/macOS:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Windows:
Ensure the web server has read/write access to `storage` and `bootstrap/cache` directories.

### 9. Asset Compilation

Compile and build the frontend assets:

#### For Development:
```bash
npm run dev
```

#### For Production:
```bash
npm run build
```

### 10. Start Development Server

```bash
php artisan serve
```

Your application will be available at `http://localhost:8000`.

## 🔄 Development Workflow

### Asset Development with Hot Reload

For frontend development with automatic browser refresh:

```bash
npm run dev
```

This will start Vite development server with hot module replacement.

### Queue Workers (if using queues)

Start queue workers for background job processing:

```bash
php artisan queue:work
```

### Scheduled Tasks (if applicable)

For scheduled tasks, add to your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🛠 Common Artisan Commands

Here are frequently used Laravel Artisan commands for this project:

### Database Operations
```bash
# Fresh migration with seeding (destroys existing data)
php artisan migrate:fresh --seed

# Run migrations only
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Run specific seeder
php artisan db:seed --class=BranchSeeder
```

### Cache Management
```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache optimization (production)
php artisan optimize
```

### Development Tools
```bash
# Generate app key
php artisan key:generate

# Create symbolic link for storage
php artisan storage:link

# List all routes
php artisan route:list

# Start development server
php artisan serve --host=0.0.0.0 --port=8000
```

### User Management
```bash
# Create new user (if applicable)
php artisan make:user

# List permissions and roles (Spatie)
php artisan permission:show
```

### Queue Management
```bash
# Process queue jobs
php artisan queue:work

# Restart queue workers
php artisan queue:restart

# Failed jobs
php artisan queue:failed
php artisan queue:retry all
```

## 📁 Project Structure

```
Nexus/
├── app/
│   ├── Http/Controllers/          # Application controllers
│   │   ├── AuditController.php    # Audit management
│   │   ├── ComplaintController.php # Complaint handling
│   │   ├── BranchController.php   # Branch management
│   │   └── ...
│   ├── Models/                    # Eloquent models
│   ├── Policies/                  # Authorization policies
│   └── Providers/                 # Service providers
├── database/
│   ├── migrations/                # Database migrations
│   └── seeders/                   # Database seeders
│       ├── DatabaseSeeder.php     # Main seeder
│       ├── BranchSeeder.php       # Branch data
│       ├── AuditSeeder.php        # Audit sample data
│       └── ...
├── resources/
│   ├── views/                     # Blade templates
│   ├── js/                        # JavaScript files
│   └── css/                       # CSS files
├── routes/
│   ├── web.php                    # Web routes
│   └── api.php                    # API routes
├── config/                        # Configuration files
├── public/                        # Public assets
└── storage/                       # File storage
```

### Key Modules

1. **Audit Management** (`app/Http/Controllers/Audit*.php`)
   - Audit lifecycle management
   - Findings and risk tracking
   - Compliance monitoring

2. **Complaint System** (`app/Http/Controllers/Complaint*.php`)
   - Complaint registration and tracking
   - Category and status management
   - Escalation workflows

3. **Branch Management** (`app/Http/Controllers/Branch*.php`, `Region*.php`, `District*.php`)
   - Organizational structure
   - Regional management
   - Branch targets and performance

4. **User Management** (`app/Http/Controllers/User*.php`, `Role*.php`)
   - Authentication and authorization
   - Role-based access control
   - Permission management

## 🌐 Deployment Guidelines

### Production Environment Setup

1. **Server Requirements**
   - Web server (Apache/Nginx)
   - PHP 8.2+ with required extensions
   - Database server (MySQL recommended)
   - Redis (for caching and queues)
   - SSL certificate

2. **Environment Configuration**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=your_db_host
   DB_DATABASE=nexus_production
   DB_USERNAME=secure_username
   DB_PASSWORD=secure_password
   
   CACHE_DRIVER=redis
   QUEUE_CONNECTION=redis
   SESSION_DRIVER=redis
   ```

3. **Deployment Steps**
   ```bash
   # Install dependencies
   composer install --optimize-autoloader --no-dev
   npm ci && npm run build
   
   # Configure environment
   cp .env.example .env
   # Edit .env with production values
   
   # Setup database
   php artisan migrate --force
   php artisan db:seed --force
   
   # Optimize application
   php artisan optimize
   php artisan storage:link
   
   # Set permissions
   chmod -R 755 storage bootstrap/cache
   ```

4. **Web Server Configuration**

   **Apache (.htaccess in public folder)**
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteRule ^ index.php [L]
   ```

   **Nginx**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com;
       root /var/www/Nexus/public;
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

5. **Queue Workers (Production)**
   ```bash
   # Install Supervisor
   sudo apt install supervisor
   
   # Create worker configuration
   sudo nano /etc/supervisor/conf.d/nexus-worker.conf
   ```

   Supervisor configuration:
   ```ini
   [program:nexus-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /var/www/Nexus/artisan queue:work --sleep=3 --tries=3
   autostart=true
   autorestart=true
   user=www-data
   numprocs=8
   redirect_stderr=true
   stdout_logfile=/var/www/Nexus/storage/logs/worker.log
   ```

## 🔍 Troubleshooting

### Common Issues and Solutions

#### 1. Installation Issues

**Composer dependencies fail to install:**
```bash
# Clear composer cache
composer clear-cache
composer install --ignore-platform-reqs
```

**NPM installation fails:**
```bash
# Clear npm cache
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

#### 2. Database Issues

**Migration fails:**
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Reset migrations
php artisan migrate:fresh --seed
```

**Seeding fails:**
```bash
# Run specific seeder with verbose output
php artisan db:seed --class=DatabaseSeeder -v

# Check for foreign key constraints
# Ensure seeders run in correct order (check DatabaseSeeder.php)
```

#### 3. Permission Issues

**Storage/cache permission denied:**
```bash
# Linux/macOS
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Or for development
chmod -R 777 storage bootstrap/cache
```

#### 4. Asset Compilation Issues

**Vite build fails:**
```bash
# Clear node modules and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

#### 5. Application Key Issues

**Application key not set:**
```bash
php artisan key:generate
```

#### 6. Environment Issues

**Configuration cached:**
```bash
# Clear all caches
php artisan optimize:clear
```

### Debugging Tools

1. **Laravel Debugbar** (if installed)
   ```bash
   composer require barryvdh/laravel-debugbar --dev
   ```

2. **Log Monitoring**
   ```bash
   # Watch logs in real-time
   tail -f storage/logs/laravel.log
   
   # Using Laravel Pail (if installed)
   php artisan pail
   ```

3. **Database Debugging**
   ```bash
   # Database tinker
   php artisan tinker
   >>> User::count();
   >>> DB::table('branches')->get();
   ```

## 📞 Support

For issues and questions:

1. Check the troubleshooting section above
2. Review Laravel documentation: https://laravel.com/docs
3. Check application logs: `storage/logs/laravel.log`
4. Create an issue in the project repository

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
