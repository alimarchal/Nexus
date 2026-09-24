<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * AKSIC markup subsidy claims: claim tables and their permissions.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createClaimTables();
        $this->seedClaimPermissions();
    }

    public function down(): void
    {
        $this->removeClaimPermissions();
        $this->dropClaimTables();
    }

    // ------------------------------------------------------------------
    // Claims (was 2026_09_23_000002_create_aksic_claims_tables.php)
    // ------------------------------------------------------------------
    /**
     * AKSIC Portal Change #5 -- Claim lodging by District / Branch-Region / Gender.
     *
     * aksic_claims       one lodged claim: the period it covers and the filters it
     *                    was lodged for (district / region / branch / gender, all
     *                    optional), with its totals and settlement status.
     * aksic_claim_items  one line per loan in the claim. District, region, branch
     *                    and gender are snapshotted at lodging time so MIS reports
     *                    stay correct even if the loan or branch is edited later.
     *
     * Claim amount per loan = markup of the schedule instalments falling due
     * inside the claim period. A loan cannot sit in two live claims whose periods
     * overlap (enforced in App\Services\AksicClaimService).
     */
    private function createClaimTables(): void
    {
        Schema::create('aksic_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('claim_no', 30)->unique();
            $table->date('claim_date');
            $table->date('period_from');
            $table->date('period_to');

            // Filters the claim was lodged for (null = all).
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('gender', 20)->nullable();

            $table->unsignedInteger('total_loans')->default(0);
            $table->decimal('total_principal_outstanding', 20, 2)->default(0);
            $table->decimal('total_markup', 20, 2)->default(0);

            $table->enum('status', ['Lodged', 'Settled', 'Rejected'])->default('Lodged');
            $table->date('status_date')->nullable()->comment('Date settled / rejected');
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();

            $table->index(['period_from', 'period_to']);
            $table->index('status');
        });

        Schema::create('aksic_claim_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('aksic_claim_id')->constrained('aksic_claims')->cascadeOnDelete();
            $table->foreignUuid('aksic_id')->constrained('aksics')->cascadeOnDelete();

            // Snapshot for District / Region / Branch / Gender-wise reporting.
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('gender', 20)->nullable();

            $table->unsignedSmallInteger('installments_count');
            $table->decimal('principal_outstanding', 20, 2)->comment('Opening principal of the first instalment in the period');
            $table->decimal('markup_amount', 20, 2)->comment('Markup of instalments due within the claim period');
            $table->timestamps();

            $table->unique(['aksic_claim_id', 'aksic_id']);
            $table->index(['district_id', 'region_id', 'branch_id', 'gender'], 'aksic_claim_items_mis_index');
        });
    }

    private function dropClaimTables(): void
    {
        Schema::dropIfExists('aksic_claim_items');
        Schema::dropIfExists('aksic_claims');
    }

    // ------------------------------------------------------------------
    // ClaimPermissions (was 2026_09_23_000003_seed_aksic_claim_permissions.php)
    // ------------------------------------------------------------------
    /**
     * Permissions for AKSIC claim lodging (Portal Change #5).
     * Mirrors AKSIC itself: head-office lodges and settles, region/division view.
     */
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'view aksic claims',
        'lodge aksic claims',
        'settle aksic claims',
        'delete aksic claims',
    ];

    private function seedClaimPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'region' => ['view aksic claims'],
            'division' => ['view aksic claims'],
            'head-office' => $this->permissions,
            'super-admin' => $this->permissions,
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permissionNames);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function removeClaimPermissions(): void
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
