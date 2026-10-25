<?php

use App\Models\Report;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    Report::create(['date' => '2026-10-01', 'branch_name' => 'Muzaffarabad', 'branch_code' => '0001']);
    Report::create(['date' => '2026-10-01', 'branch_name' => 'Mirpur', 'branch_code' => '0002']);
});

test('reports can be filtered by branch name', function () {
    $this->actingAs($this->admin)
        ->get(route('reports.index', ['branch_name' => 'Mirpur']))
        ->assertSuccessful()
        ->assertViewHas('reports', fn ($reports) => $reports->pluck('branch_name')->all() === ['Mirpur']);
});

test('reports can be filtered by branch code', function () {
    $this->actingAs($this->admin)
        ->get(route('reports.index', ['branch_code' => '0001']))
        ->assertViewHas('reports', fn ($reports) => $reports->pluck('branch_code')->all() === ['0001']);
});
