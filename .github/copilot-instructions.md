# Nexus - AI Assistant Guidelines

## Project Overview
Nexus is a comprehensive Laravel 12 business management system designed for organizational operations, with modules for audit management, complaint handling, branch administration, stationery management, and employee resources.

## Architecture

### Core Application Structure
- **Laravel 12 MVC Framework** - Routes (`/routes`) define endpoints, Controllers (`/app/Http/Controllers`) handle requests, Models (`/app/Models`) represent data, Views (`/resources/views`) render UI
- **Authentication** - Uses Laravel Jetstream with Livewire
- **Authorization** - Implements Spatie Laravel Permission with role-based access control
- **Database** - Uses UUIDs for primary keys in most tables with proper relationships

### Key Domain Models & Their Relationships

#### Organizational Structure
- `Region` → `District` → `Branch` hierarchy (one-to-many relationships)
- `Division` for departmental organization

#### Audit System
- `Audit` is the central entity, connected to:
  - `AuditType` defines audit templates and configuration
  - `AuditAuditor` assigns team members to audits
  - `AuditChecklistItem` and `AuditItemResponse` for audit procedures
  - `AuditFinding` → `AuditAction` workflow for issues and remediation
  - `AuditRisk`, `AuditScope`, `AuditDocument` for supporting elements
  - Uses soft deletes for data preservation

#### Complaint Management
- `Complaint` entity with:
  - Branch/region/division association
  - Assignment workflow (`assigned_to`, `assigned_by`, timestamps)
  - Status tracking with history
  - Multiple specialized complaint types (harassment, grievance)
  - Related `ComplaintAttachment`, `ComplaintComment`, etc.
  - Uses soft deletes and UUIDs

#### Stationery Management
- `PrintedStationery` for inventory tracking
- `StationeryTransaction` for movement records
- `DispatchRegister` for delivery tracking

## Development Workflow

### Local Environment Setup
```bash
# Clone and install dependencies
git clone https://github.com/alimarchal/Nexus.git
cd Nexus
composer install
npm install
cp .env.example .env
php artisan key:generate

# Database setup with initial data
php artisan migrate:fresh --seed

# Storage link for file uploads
php artisan storage:link

# Start development servers
npm run dev  # Frontend assets with hot reload
php artisan serve  # PHP development server
```

### Project-Specific Conventions

1. **ID Generation**:
   - UUIDs are used as primary keys in most models
   - Human-readable IDs are generated with custom prefixes:
     ```php
     $complaint->complaint_number = generateUniqueId('complaint', 'complaints', 'complaint_number');
     ```

2. **File Storage**:
   - Files are stored in `/storage/app/public/` with symbolic link to `/public/storage/`
   - Use the `FileStorageHelper` class for consistent file handling:
     ```php
     FileStorageHelper::storeFile($request->file('document'), 'audits/documents');
     ```

3. **Activity Logging**:
   - Uses Spatie Activity Log package:
     ```php
     // Example from Complaint model
     public function getActivitylogOptions(): LogOptions
     {
         return LogOptions::defaults()
             ->logAll()
             ->logOnlyDirty()
             ->dontSubmitEmptyLogs();
     }
     ```

4. **Authorization Pattern**:
   - Uses role-based checks in controllers:
     ```php
     if (!auth()->user()->can('view-audits')) {
         abort(403);
     }
     ```

5. **API Response Structure**:
   - Standard JSON structure for all API responses:
     ```php
     return response()->json([
         'status' => 'success',
         'data' => $data,
         'message' => 'Audit fetched successfully'
     ]);
     ```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=AuditTest

# Generate test coverage
php artisan test --coverage
```

## Common Issues & Solutions

1. **UUID vs Integer IDs**:
   - Many models use UUIDs, but relations like `assigned_to` use integer IDs (users table)
   - Always check the migration to confirm ID type before creating relationships

2. **Permission System**:
   - Permissions are defined in the `RolesAndPermissionsSeeder` and checked via `can()` method
   - When adding new features, ensure proper permissions are defined

3. **Audit vs ComplaintAudit**:
   - Don't confuse the audit feature (system audits) with audit trails (activity logs) 
   - Audit trails are handled by Spatie Activity Log package

4. **File Structure Patterns**:
   - Main controllers are in `/app/Http/Controllers/`
   - Complex modules have additional controller subdirectories
   - Utility code should go in `/app/Helpers/`

## Key Files

- `/routes/web.php` - All web routes organized by module
- `/app/Models/Audit.php` - Central audit system model with relationships
- `/app/Models/Complaint.php` - Complex complaint handling model
- `/database/seeders/DatabaseSeeder.php` - Main seeder that orchestrates all seed operations
- `/app/Helpers/FileStorageHelper.php` - Utility functions for file operations

## Deployment

For production deployment, follow standard Laravel best practices:
- Set environment to production in `.env`
- Optimize autoloader: `composer install --optimize-autoloader --no-dev`
- Cache configuration: `php artisan config:cache`
- Cache routes: `php artisan route:cache`
- Compile assets: `npm run build`