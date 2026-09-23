<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * AKSIC -- PM Youth Loan Scheme markup budget.
 *
 * Source: AKSIC "Markup Allocation / Budget (Rs. 994.225 million)" sheet --
 * district provision = total x population %, split Male 48 / Female 48 /
 * Special Person 2 / Transgender 2 and Existing 25 / New 75 business.
 *
 * aksic_budgets             one budget version (total, business split, how
 *                           strictly it is enforced at approval). Only one is
 *                           active; a revised scheme is a new version.
 * aksic_budget_allocations  current allocation per district for a budget.
 * aksic_budget_revisions    append-only ledger of every change -- initial
 *                           allocation, enhancement, reduction, redistribution,
 *                           setting changes -- with reference letter and reason.
 *
 * Utilisation is not stored: it is the markup (aksics.total_interest) of
 * approved cases, so it can never drift from the schedules.
 */
return new class extends Migration
{
    /**
     * Sheet figures in Rs million.
     *
     * @var array<string, float>
     */
    private array $sheet = [
        'Kotli' => 183.435,
        'Muzaffarabad' => 161.462,
        'Poonch' => 125.272,
        'Mirpur' => 99.621,
        'Bhimber' => 100.317,
        'Bagh' => 101.610,
        'Sudhnoti' => 71.684,
        'Jhelum Valley' => 59.455,
        'Neelum' => 51.501,
        'Haveli' => 39.670,
    ];

    /**
     * @var array<int, string>
     */
    private array $permissions = ['view aksic budget', 'manage aksic budget'];

    public function up(): void
    {
        Schema::create('aksic_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('reference_no', 100)->nullable()->comment('Sanction / letter number');
            $table->date('reference_date')->nullable();
            $table->decimal('total_amount', 20, 2)->comment('Total markup budget in Rs');
            $table->decimal('existing_business_percentage', 5, 2)->default(25);
            $table->decimal('new_business_percentage', 5, 2)->default(75);
            $table->enum('enforcement', ['report', 'warn', 'block'])->default('block')
                ->comment('report = show only, warn = approve with warning, block = stop approval when a limit is exceeded');
            $table->boolean('is_active')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        Schema::create('aksic_budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aksic_budget_id')->constrained('aksic_budgets')->cascadeOnDelete();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnUpdate();
            $table->foreignId('aksic_rule_id')->nullable()->constrained('aksic_rules')->nullOnDelete();
            $table->decimal('allocated_amount', 20, 2)->comment('Current markup allocation in Rs');
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unique(['aksic_budget_id', 'district_id']);
        });

        Schema::create('aksic_budget_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aksic_budget_id')->constrained('aksic_budgets')->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete()
                ->comment('Null for budget-level changes');
            $table->enum('action', ['initial', 'enhancement', 'reduction', 'redistribution', 'total_change', 'settings_change', 'activation']);
            $table->decimal('previous_amount', 20, 2)->nullable();
            $table->decimal('new_amount', 20, 2)->nullable();
            $table->decimal('change_amount', 20, 2)->nullable()->comment('new - previous');
            $table->string('reference_no', 100)->nullable();
            $table->date('reference_date')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['aksic_budget_id', 'created_at']);
        });

        $this->seedSheetBudget();
        $this->seedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('aksic_budget_revisions');
        Schema::dropIfExists('aksic_budget_allocations');
        Schema::dropIfExists('aksic_budgets');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        foreach ($this->permissions as $name) {
            Permission::where('name', $name)->first()?->delete();
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function seedSheetBudget(): void
    {
        $now = now();
        $rules = DB::table('aksic_rules')->whereNull('deleted_at')->get(['id', 'district_id', 'district_name'])->keyBy('district_name');

        if ($rules->isEmpty()) {
            return;
        }

        $budgetId = DB::table('aksic_budgets')->insertGetId([
            'title' => 'PM Youth Loan Scheme -- Markup Allocation',
            'reference_no' => 'AKSIC Markup Allocation / Budget sheet',
            'total_amount' => 994225000,
            'existing_business_percentage' => 25,
            'new_business_percentage' => 75,
            'enforcement' => 'block',
            'is_active' => true,
            'notes' => 'Seeded from the AKSIC PMYL markup allocation sheet (Rs 994.225 million; district provision = total x population %).',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($this->sheet as $districtName => $millions) {
            $rule = $rules->get($districtName);

            if (! $rule) {
                continue;
            }

            $amount = round($millions * 1000000, 2);

            DB::table('aksic_budget_allocations')->insert([
                'aksic_budget_id' => $budgetId,
                'district_id' => $rule->district_id,
                'aksic_rule_id' => $rule->id,
                'allocated_amount' => $amount,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('aksic_budget_revisions')->insert([
                'aksic_budget_id' => $budgetId,
                'district_id' => $rule->district_id,
                'action' => 'initial',
                'previous_amount' => 0,
                'new_amount' => $amount,
                'change_amount' => $amount,
                'reason' => 'Initial allocation from the markup allocation sheet.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (['region' => ['view aksic budget'], 'division' => ['view aksic budget'],
            'head-office' => $this->permissions, 'super-admin' => $this->permissions] as $role => $names) {
            Role::where('name', $role)->first()?->givePermissionTo($names);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
