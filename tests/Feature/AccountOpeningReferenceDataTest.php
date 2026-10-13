<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @return array<string, int>
 */
function referenceTableCounts(): array
{
    $tables = collect(Schema::getTableListing(schemaQualified: false))
        ->reject(fn (string $table): bool => str_starts_with($table, 'sqlite_'));

    return $tables->mapWithKeys(fn (string $table): array => [$table => DB::table($table)->count()])->all();
}

test('the sync command fills the empty option lists the form needs', function () {
    expect(DB::table('account_products')->count())->toBe(0)
        ->and(DB::table('currencies')->count())->toBe(0);

    $this->artisan('aof:sync-reference-data')->assertSuccessful();

    expect(DB::table('account_products')->count())->toBeGreaterThan(0)
        ->and(DB::table('currencies')->where('code', 'JPY')->exists())->toBeTrue()
        ->and(DB::table('card_types')->count())->toBeGreaterThanOrEqual(3)
        ->and(DB::table('special_categories')->where('code', 'ILLITERATE')->exists())->toBeTrue()
        ->and(DB::table('income_sources')->where('code', 'FAMILY_BUSINESS')->exists())->toBeTrue()
        ->and(DB::table('customer_categories')->count())->toBeGreaterThan(0)
        ->and(DB::table('document_requirements')->count())->toBeGreaterThan(0);
});

test('running the sync again changes nothing and removes nothing', function () {
    $this->artisan('aof:sync-reference-data')->assertSuccessful();
    $afterFirst = referenceTableCounts();

    DB::table('currencies')->where('code', 'JPY')->update(['name' => 'Edited by hand']);
    $this->artisan('aof:sync-reference-data')->assertSuccessful();

    expect(referenceTableCounts())->toBe($afterFirst)
        ->and(DB::table('currencies')->where('code', 'JPY')->value('name'))->toBe('Japanese Yen');
});

test('an option added by hand survives the sync', function () {
    $this->artisan('aof:sync-reference-data')->assertSuccessful();
    $before = DB::table('currencies')->count();

    DB::table('currencies')->insert(['id' => (string) Str::uuid(), 'code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'Fr', 'is_local' => false, 'is_active' => true, 'sort_order' => 99, 'created_at' => now(), 'updated_at' => now()]);
    $this->artisan('aof:sync-reference-data')->assertSuccessful();

    expect(DB::table('currencies')->count())->toBe($before + 1);
});

test('the load migration is part of the migrations and does not run in the test suite', function () {
    expect(glob(database_path('migrations/*_load_account_opening_reference_data.php')))->toHaveCount(1)
        ->and(DB::table('migrations')->where('migration', 'like', '%load_account_opening_reference_data')->exists())->toBeTrue()
        ->and(DB::table('account_products')->count())->toBe(0);
});
