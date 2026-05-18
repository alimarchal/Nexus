<?php

use Alimarchal\IdGenerator\Tests\TestCase;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Global Functions
|--------------------------------------------------------------------------
*/

function createTestInvoice(string $invoiceNo): void
{
    DB::table('test_invoices')->insert([
        'invoice_no' => $invoiceNo,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function createTestOrder(string $orderNo): void
{
    DB::table('test_orders')->insert([
        'order_no' => $orderNo,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
