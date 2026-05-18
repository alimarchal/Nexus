<?php

it('has generateUniqueId function', function () {
    expect(function_exists('generateUniqueId'))->toBeTrue();
});

it('has generateUniqueIdWithPrefix function', function () {
    expect(function_exists('generateUniqueIdWithPrefix'))->toBeTrue();
});

it('generates unique ID with helper function', function () {
    $id = generateUniqueId('invoice', 'test_invoices', 'invoice_no');

    expect($id)
        ->toStartWith('INV-')
        ->toContain(now()->format('Ymd'));
});

it('generates unique ID with prefix helper', function () {
    $id = generateUniqueIdWithPrefix('ORDER', 'test_orders', 'order_no');

    expect($id)
        ->toStartWith('ORDER-')
        ->toContain(now()->format('Ymd'));
});

it('helper function increments correctly', function () {
    $firstId = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
    createTestInvoice($firstId);

    $secondId = generateUniqueId('invoice', 'test_invoices', 'invoice_no');

    expect($firstId)->toEndWith('-0001');
    expect($secondId)->toEndWith('-0002');
});
