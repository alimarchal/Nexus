<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aksic_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('district_name');
            $table->decimal('population_percentage', 5, 2);
            $table->unsignedInteger('proposed_beneficiaries');
            $table->decimal('male_percentage', 5, 2)->default(48);
            $table->decimal('female_percentage', 5, 2)->default(48);
            $table->decimal('special_person_percentage', 5, 2)->default(2);
            $table->decimal('transgender_percentage', 5, 2)->default(2);
            $table->boolean('requires_site_visit')->default(true);
            $table->boolean('requires_business_nature')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique('district_id');
        });

        $this->seedDefaultRules();
        $this->seedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('aksic_rules');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['view aksic rules', 'create aksic rules', 'edit aksic rules', 'delete aksic rules'] as $permissionName) {
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

    private function seedDefaultRules(): void
    {
        $rules = [
            'Muzaffarabad' => ['population_percentage' => 16.24, 'proposed_beneficiaries' => 456],
            'Neelum' => ['population_percentage' => 5.18, 'proposed_beneficiaries' => 145],
            'Jhelum Valley' => ['population_percentage' => 5.98, 'proposed_beneficiaries' => 168],
            'Bagh' => ['population_percentage' => 10.22, 'proposed_beneficiaries' => 287],
            'Haveli' => ['population_percentage' => 3.99, 'proposed_beneficiaries' => 112],
            'Poonch' => ['population_percentage' => 12.60, 'proposed_beneficiaries' => 353],
            'Sudhnoti' => ['population_percentage' => 7.21, 'proposed_beneficiaries' => 202, 'aliases' => ['Sudhanoti']],
            'Kotli' => ['population_percentage' => 18.45, 'proposed_beneficiaries' => 518],
            'Mirpur' => ['population_percentage' => 10.02, 'proposed_beneficiaries' => 281],
            'Bhimber' => ['population_percentage' => 10.09, 'proposed_beneficiaries' => 283],
        ];

        foreach ($rules as $districtName => $rule) {
            $district = DB::table('districts')
                ->where('name', $districtName)
                ->orWhereIn('name', $rule['aliases'] ?? [])
                ->first();

            if (! $district) {
                continue;
            }

            DB::table('aksic_rules')->updateOrInsert(
                ['district_id' => $district->id],
                [
                    'district_name' => $districtName,
                    'population_percentage' => $rule['population_percentage'],
                    'proposed_beneficiaries' => $rule['proposed_beneficiaries'],
                    'male_percentage' => 48,
                    'female_percentage' => 48,
                    'special_person_percentage' => 2,
                    'transgender_percentage' => 2,
                    'requires_site_visit' => true,
                    'requires_business_nature' => true,
                    'is_active' => true,
                    'notes' => 'AKSIC district-wise disbursement rule from 16-03-2026 letter.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function seedPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = ['view aksic rules', 'create aksic rules', 'edit aksic rules', 'delete aksic rules'];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        foreach (['head-office', 'super-admin'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
