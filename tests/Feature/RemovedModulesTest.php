<?php

use App\Models\User;
use App\Support\PermissionCatalog;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function dropMigration(): Migration
{
    return require database_path('migrations/'.collect(scandir(database_path('migrations')))->first(fn ($f) => str_contains($f, 'drop_audit_circular_stationery_dispatch_and_daily_position_tables')));
}

test('the audits, circulars, stationery, dispatch register and daily position routes are gone', function () {
    foreach (['audits.index', 'audits.full', 'audit-types.index', 'audit-tags.index', 'circulars.index', 'printed-stationeries.index', 'stationery-transactions.index',
        'dispatch-registers.index', 'daily-positions.index', 'dashboard.daily-positions', 'reports.daily-position-report', 'report.printed-stationeries'] as $name) {
        expect(Route::has($name))->toBeFalse($name);
    }

    expect(Route::has('complaints.index'))->toBeTrue()->and(Route::has('aksic.index'))->toBeTrue()->and(Route::has('file-management-systems.index'))->toBeTrue();
});

test('the seeders no longer create audit permissions and the permission list has no Audits module', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Permission::where('name', 'like', '% audits')->count())->toBe(0)
        ->and(collect(PermissionCatalog::build(Permission::all()))->pluck('key')->all())->not->toContain('audits');
});

test('the drop migration removes the old tables, their permissions, activity log and media rows, and nothing else', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    Schema::create('audit_types', fn (Blueprint $t) => $t->id());
    Schema::create('audits', function (Blueprint $t) {
        $t->id();
        $t->foreignId('audit_type_id')->constrained('audit_types');
    });
    foreach (['circulars', 'daily_positions', 'printed_stationeries', 'dispatch_registers'] as $table) {
        Schema::create($table, fn (Blueprint $t) => $t->id());
    }
    DB::table('audit_types')->insert(['id' => 1]);
    DB::table('audits')->insert(['id' => 1, 'audit_type_id' => 1]);

    $role = Role::firstOrCreate(['name' => 'branch', 'guard_name' => 'web']);
    $audit = Permission::create(['name' => 'view audits', 'guard_name' => 'web']);
    $role->givePermissionTo($audit, 'view complaints');
    DB::table('activity_log')->insert([
        ['log_name' => 'default', 'description' => 'audit', 'subject_type' => 'App\\Models\\Audit', 'subject_id' => '1', 'created_at' => now(), 'updated_at' => now()],
        ['log_name' => 'default', 'description' => 'file', 'subject_type' => 'App\\Models\\FileManagementSystem', 'subject_id' => '1', 'created_at' => now(), 'updated_at' => now()],
    ]);

    dropMigration()->up();

    foreach (['audit_types', 'audits', 'circulars', 'daily_positions', 'printed_stationeries', 'dispatch_registers'] as $table) {
        expect(Schema::hasTable($table))->toBeFalse($table);
    }

    expect(Permission::where('name', 'view audits')->exists())->toBeFalse()
        ->and($role->fresh()->hasPermissionTo('view complaints'))->toBeTrue()
        ->and(DB::table('activity_log')->where('description', 'audit')->exists())->toBeFalse()
        ->and(DB::table('activity_log')->where('description', 'file')->exists())->toBeTrue();
});

test('the drop migration also runs on a database that never had the tables', function () {
    dropMigration()->up();

    expect(Schema::hasTable('audits'))->toBeFalse();
});

test('the product and reports pages still open for the super admin without the removed cards', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::where('email', 'superadmin@example.com')->firstOrFail();

    foreach (['product.index', 'reports.index', 'dashboard'] as $route) {
        $this->actingAs($admin)->get(route($route))->assertSuccessful();
    }
});
