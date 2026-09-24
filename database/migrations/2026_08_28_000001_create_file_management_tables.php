<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * File management module: categories, files, head offices, org-unit columns on users,
 * transfers, archive boxes and archiving columns (with their permissions and default categories).
 *
 * One migration per module; each former migration is kept below as its own step, in the same order.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createFileCategoriesTable();
        $this->createFileManagementSystemsTable();
        $this->createHeadOfficesTable();
        $this->addOfficeColumnsToUsers();
        $this->createTransfersTable();
        $this->createBoxesTable();
        $this->addArchivingColumns();
    }

    public function down(): void
    {
        $this->dropArchivingColumns();
        $this->dropBoxesTable();
        $this->dropTransfersTable();
        $this->dropOfficeColumnsFromUsers();
        $this->dropHeadOfficesTable();
        $this->dropFileManagementSystemsTable();
        $this->dropFileCategoriesTable();
    }

    // ------------------------------------------------------------------
    // FileCategories (was 2026_08_28_055120_create_file_categories_table.php)
    // ------------------------------------------------------------------
    /**
     * Permission names guarding the File Categories module (bank FMS master data).
     *
     * @var array<int, string>
     */
    private array $permissionsFileCategories = [
        'view file categories',
        'create file categories',
        'edit file categories',
        'delete file categories',
    ];

    /**
     * Default document/file categories used to classify scanned records across
     * branch, region, and division levels of the Bank's File Management System.
     *
     * @var array<int, array{code: string, name: string}>
     */
    private array $defaultCategories = [
        ['code' => 'FC-001', 'name' => 'Account Opening Forms (KYC)'],
        ['code' => 'FC-002', 'name' => 'Cheque Books & Requisitions'],
        ['code' => 'FC-003', 'name' => 'Loan & Credit Documents'],
        ['code' => 'FC-004', 'name' => 'Fixed Deposit Receipts'],
        ['code' => 'FC-005', 'name' => 'Correspondence & Letters'],
        ['code' => 'FC-006', 'name' => 'Circulars & Notifications'],
        ['code' => 'FC-007', 'name' => 'Audit Reports'],
        ['code' => 'FC-008', 'name' => 'Legal & Litigation Documents'],
        ['code' => 'FC-009', 'name' => 'HR & Personnel Files'],
        ['code' => 'FC-010', 'name' => 'Inspection & Compliance Reports'],
    ];

    /**
     * Run the migrations.
     */
    private function createFileCategoriesTable(): void
    {
        Schema::create('file_categories', function (Blueprint $table) {
            // Stores the unique UUID identifier for each document category.
            $table->uuid('id')->primary();

            // Stores the unique code used to identify the document category.
            $table->string('category_code', 20)->unique();

            // Stores the display name of the document category.
            $table->string('category_name', 100);

            // Indicates whether the document category is currently active.
            $table->enum('is_active', ['0', '1'])->default('1');

            // Adds audit tracking columns such as created_by/updated_by to record who created or updated the category.
            $table->userTracking();

            // Adds a deleted_at column so records can be soft-deleted without being permanently removed from the database.
            $table->softDeletes();

            // Adds created_at and updated_at timestamps for record lifecycle tracking.
            $table->timestamps();
        });

        $this->seedPermissionsFileCategories();
        $this->seedDefaultCategories();
    }

    /**
     * Reverse the migrations.
     */
    private function dropFileCategoriesTable(): void
    {
        $this->revokePermissionsFileCategories();

        Schema::dropIfExists('file_categories');
    }

    /**
     * Create the module permissions and grant them to the appropriate roles.
     * Mirrors the restriction pattern used for "managers" (view for
     * branch/region, view+create+edit for division, full access for
     * head-office/super-admin) since file categories are shared master data
     * used by branch, region and division when scanning documents.
     */
    private function seedPermissionsFileCategories(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissionsFileCategories as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $rolePermissions = [
            'branch' => ['view file categories'],
            'region' => ['view file categories'],
            'division' => ['view file categories', 'create file categories', 'edit file categories'],
            'head-office' => $this->permissionsFileCategories,
            'super-admin' => $this->permissionsFileCategories,
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permissionNames);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Revoke and remove the module permissions from every role.
     */
    private function revokePermissionsFileCategories(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissionsFileCategories as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();

            if ($permission) {
                foreach ($permission->roles as $role) {
                    $role->revokePermissionTo($permission);
                }

                $permission->delete();
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Seed the common bank FMS document categories so the module works out
     * of the box on both `migrate` and `migrate:fresh --seed`.
     */
    private function seedDefaultCategories(): void
    {
        foreach ($this->defaultCategories as $category) {
            DB::table('file_categories')->updateOrInsert(
                ['category_code' => $category['code']],
                [
                    'id' => (string) Str::uuid(),
                    'category_name' => $category['name'],
                    'is_active' => '1',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    // ------------------------------------------------------------------
    // FileManagementSystems (was 2026_08_28_055121_create_file_management_systems_table.php)
    // ------------------------------------------------------------------
    /**
     * Permission names guarding the File Management System module.
     *
     * @var array<int, string>
     */
    private array $permissionsFileManagementSystems = [
        'view file management systems',
        'create file management systems',
        'edit file management systems',
        'delete file management systems',
        'transfer file management systems',
        'approve file management transfers',
        'archive file management systems',
        'create boxes',
        'manage boxes',
    ];

    private function createFileManagementSystemsTable(): void
    {
        Schema::create('file_management_systems', function (Blueprint $table) {
            // Stores the unique UUID identifier for each document record.
            $table->uuid('id')->primary();

            // digital_id      VARCHAR(40)  NOT NULL UNIQUE,     -- e.g. BR014-20260827-CASH-000123
            $table->string('digital_id', 40)->unique();

            // Manually assigned file number for the user's own convenience (e.g. HRMS/12/ABC), independent of the auto-generated digital_id.
            $table->string('file_no', 60)->nullable()->index();

            // document_categories foreign id
            $table->foreignUuid('file_category_id')
                ->constrained('file_categories')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // Polymorphic org-unit scope: branch, region, division, or head-office.
            // Creates fileable_type + fileable_id (bigint, matches your org tables' auto-increment ids).
            $table->morphs('fileable');

            // The date of the underlying work/record being scanned (not the upload date).
            $table->date('document_date');

            // Optional human-readable title/description of the document.
            $table->string('title')->nullable();

            // Adds audit tracking columns such as created_by/updated_by to record who created or updated the record.
            $table->userTracking();

            // The user currently holding/responsible for this physical/scanned file.
            // (No ->after(): column position is set by declaration order inside
            // Schema::create, and MariaDB rejects AFTER in CREATE TABLE.)
            $table->foreignId('current_custodian_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Adds a deleted_at column so records can be soft-deleted without being permanently removed from the database.
            $table->softDeletes();

            // Adds created_at and updated_at timestamps for record lifecycle tracking.
            $table->timestamps();

            // Note: morphs() already creates an index on (fileable_type, fileable_id).
            // Add document_date to that composite for the date-range queries you'll run often.
            $table->index(['fileable_type', 'fileable_id', 'document_date'], 'idx_fileable_date');
        });

        $this->seedPermissionsFileManagementSystems();
    }

    private function dropFileManagementSystemsTable(): void
    {
        $this->revokePermissionsFileManagementSystems();

        Schema::dropIfExists('file_management_systems');
    }

    /**
     * Create the module permissions and grant them to the appropriate roles.
     * Branch/region/division scan and manage their own documents (view, create,
     * edit); only head-office/super-admin may delete them.
     */
    private function seedPermissionsFileManagementSystems(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissionsFileManagementSystems as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $editable = ['view file management systems', 'create file management systems', 'edit file management systems', 'transfer file management systems', 'archive file management systems', 'create boxes', 'manage boxes'];

        $rolePermissions = [
            'branch' => $editable,
            'region' => $editable,
            'division' => $editable,
            'head-office' => $this->permissionsFileManagementSystems,
            'super-admin' => $this->permissionsFileManagementSystems,
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permissionNames);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Revoke and remove the module permissions from every role.
     */
    private function revokePermissionsFileManagementSystems(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissionsFileManagementSystems as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();

            if ($permission) {
                foreach ($permission->roles as $role) {
                    $role->revokePermissionTo($permission);
                }

                $permission->delete();
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    // ------------------------------------------------------------------
    // HeadOffices (was 2026_08_28_092853_create_head_offices_table.php)
    // ------------------------------------------------------------------
    /**
     * Run the migrations.
     */
    private function createHeadOfficesTable(): void
    {
        Schema::create('head_offices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    private function dropHeadOfficesTable(): void
    {
        Schema::dropIfExists('head_offices');
    }

    // ------------------------------------------------------------------
    // UserOffices (was 2026_08_28_092854_add_region_division_head_office_to_users_table.php)
    // ------------------------------------------------------------------
    /**
     * Run the migrations.
     */
    private function addOfficeColumnsToUsers(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->foreignId('division_id')->nullable()->after('region_id')->constrained()->nullOnDelete();
            $table->foreignId('head_office_id')->nullable()->after('division_id')->constrained('head_offices')->nullOnDelete();
            $table->boolean('is_president_office')->default(false)->after('head_office_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    private function dropOfficeColumnsFromUsers(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
            $table->dropConstrainedForeignId('division_id');
            $table->dropConstrainedForeignId('head_office_id');
            $table->dropColumn('is_president_office');
        });
    }

    // ------------------------------------------------------------------
    // Transfers (was 2026_08_28_123441_create_file_management_transfers_table.php)
    // ------------------------------------------------------------------
    /**
     * Run the migrations.
     */
    private function createTransfersTable(): void
    {
        Schema::create('file_management_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('file_management_system_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('source_fileable_type');
            $table->unsignedBigInteger('source_fileable_id');
            $table->string('destination_fileable_type');
            $table->unsignedBigInteger('destination_fileable_id');
            $table->foreignId('recipient_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_note')->nullable();
            $table->timestamps();

            $table->index(['file_management_system_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    private function dropTransfersTable(): void
    {
        Schema::dropIfExists('file_management_transfers');
    }

    // ------------------------------------------------------------------
    // Boxes (was 2026_08_31_104457_create_boxes_table.php)
    // ------------------------------------------------------------------
    /**
     * Run the migrations.
     */
    private function createBoxesTable(): void
    {
        Schema::create('boxes', function (Blueprint $table) {
            // UUID primary key for consistency with file_management_systems
            $table->uuid('id')->primary();

            // Auto-generated box number: BOX-2026-001
            $table->string('box_number')->unique();

            // Polymorphic relation to Branch, Region, Division, HeadOffice
            $table->morphs('boxable');

            // Status: open (accepting files), closed (full/sealed), archived
            $table->enum('status', ['open', 'closed', 'full'])->default('open');

            // Physical storage location (e.g., "Vault-A-Shelf-2")
            $table->string('location')->nullable();

            // Current file count in this box
            $table->integer('file_count')->default(0);

            // Maximum capacity of this box
            $table->integer('capacity')->default(100);

            // When this box was sealed/closed
            $table->timestamp('archived_date')->nullable();

            // Who created this box
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['boxable_type', 'boxable_id'], 'idx_boxable');
            $table->index('status', 'idx_box_status');
            $table->index('box_number', 'idx_box_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    private function dropBoxesTable(): void
    {
        Schema::dropIfExists('boxes');
    }

    // ------------------------------------------------------------------
    // Archiving (was 2026_08_31_104459_add_archiving_to_file_management_systems_table.php)
    // ------------------------------------------------------------------
    /**
     * Run the migrations.
     */
    private function addArchivingColumns(): void
    {
        Schema::table('file_management_systems', function (Blueprint $table) {
            if (! Schema::hasColumn('file_management_systems', 'current_custodian_id')) {
                $table->foreignId('current_custodian_id')
                    ->nullable()
                    ->after('updated_by')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('file_management_systems', 'box_id')) {
                $table->foreignUuid('box_id')
                    ->nullable()
                    ->after('current_custodian_id')
                    ->constrained('boxes')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('file_management_systems', 'is_archived')) {
                $table->boolean('is_archived')
                    ->default(false)
                    ->after('box_id');
            }

            if (! Schema::hasColumn('file_management_systems', 'archived_at')) {
                $table->timestamp('archived_at')
                    ->nullable()
                    ->after('is_archived');
            }

            if (! Schema::hasColumn('file_management_systems', 'position_in_box')) {
                $table->integer('position_in_box')
                    ->nullable()
                    ->after('archived_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    private function dropArchivingColumns(): void
    {
        Schema::table('file_management_systems', function (Blueprint $table) {
            if (Schema::hasColumn('file_management_systems', 'box_id')) {
                $table->dropConstrainedForeignId('box_id');
            }

            if (Schema::hasColumn('file_management_systems', 'current_custodian_id')) {
                $table->dropConstrainedForeignId('current_custodian_id');
            }
        });

        Schema::table('file_management_systems', function (Blueprint $table) {
            foreach (['position_in_box', 'archived_at', 'is_archived'] as $column) {
                if (Schema::hasColumn('file_management_systems', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
