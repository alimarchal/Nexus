<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Permission names guarding the File Categories module (bank FMS master data).
     *
     * @var array<int, string>
     */
    private array $permissions = [
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
    public function up(): void
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

        $this->seedPermissions();
        $this->seedDefaultCategories();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->revokePermissions();

        Schema::dropIfExists('file_categories');
    }

    /**
     * Create the module permissions and grant them to the appropriate roles.
     * Mirrors the restriction pattern used for "managers" (view for
     * branch/region, view+create+edit for division, full access for
     * head-office/super-admin) since file categories are shared master data
     * used by branch, region and division when scanning documents.
     */
    private function seedPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $rolePermissions = [
            'branch' => ['view file categories'],
            'region' => ['view file categories'],
            'division' => ['view file categories', 'create file categories', 'edit file categories'],
            'head-office' => $this->permissions,
            'super-admin' => $this->permissions,
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permissionNames);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Revoke and remove the module permissions from every role.
     */
    private function revokePermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permissionName) {
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
};
