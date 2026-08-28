<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Permission names guarding the File Management System module.
     *
     * @var array<int, string>
     */
    private array $permissions = [
        'view file management systems',
        'create file management systems',
        'edit file management systems',
        'delete file management systems',
        'transfer file management systems',
        'approve file management transfers',
    ];

    public function up(): void
    {
        Schema::create('file_management_systems', function (Blueprint $table) {
            // Stores the unique UUID identifier for each document record.
            $table->uuid('id')->primary();

            // digital_id      VARCHAR(40)  NOT NULL UNIQUE,     -- e.g. BR014-20260827-CASH-000123
            $table->string('digital_id', 40)->unique();

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

        $this->seedPermissions();
    }

    public function down(): void
    {
        $this->revokePermissions();

        Schema::dropIfExists('file_management_systems');
    }

    /**
     * Create the module permissions and grant them to the appropriate roles.
     * Branch/region/division scan and manage their own documents (view, create,
     * edit); only head-office/super-admin may delete them.
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

        $editable = ['view file management systems', 'create file management systems', 'edit file management systems', 'transfer file management systems'];

        $rolePermissions = [
            'branch' => $editable,
            'region' => $editable,
            'division' => $editable,
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
};
