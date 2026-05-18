<?php

use Alimarchal\IdGenerator\IdGenerator;

describe('Real World Usage Scenarios', function () {
    it('simulates e-commerce order flow', function () {
        // Create order
        $orderNumber = generateUniqueIdWithPrefix('ORD', 'test_orders', 'order_no');
        createTestOrder($orderNumber);

        // Create invoice for the order
        $invoiceNumber = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
        createTestInvoice($invoiceNumber);

        // Create tracking number
        $trackingNumber = generateUniqueIdWithPrefix('TRK', 'test_orders', 'order_no');

        expect($orderNumber)->toStartWith('ORD-');
        expect($invoiceNumber)->toStartWith('INV-');
        expect($trackingNumber)->toStartWith('TRK-');

        expect([$orderNumber, $invoiceNumber, $trackingNumber])
            ->toHaveCount(3)
            ->and(array_unique([$orderNumber, $invoiceNumber, $trackingNumber]))
            ->toHaveCount(3);
    });

    it('simulates customer support ticket system', function () {
        // Create multiple tickets
        $tickets = [];
        for ($i = 0; $i < 3; $i++) {
            $ticketNumber = generateUniqueId('complaint', 'test_invoices', 'invoice_no');
            $tickets[] = $ticketNumber;
            createTestInvoice($ticketNumber);
        }

        expect($tickets)->toHaveCount(3);
        expect($tickets[0])->toEndWith('-0001');
        expect($tickets[1])->toEndWith('-0002');
        expect($tickets[2])->toEndWith('-0003');

        foreach ($tickets as $ticket) {
            expect($ticket)->toStartWith('CMP-');
        }
    });

    // FIX: Simplify this test
    it('simulates high volume document generation', function () {
        $invoices = [];
        $quotations = [];
        $customs = [];

        // Generate 6 invoices
        for ($i = 0; $i < 6; $i++) {
            $invoiceId = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
            $invoices[] = $invoiceId;
            createTestInvoice($invoiceId);
        }

        // Generate 6 quotations
        for ($i = 0; $i < 6; $i++) {
            $quotationId = generateUniqueId('quotation', 'test_invoices', 'invoice_no');
            $quotations[] = $quotationId;
            createTestInvoice($quotationId);
        }

        // Generate 6 custom
        for ($i = 0; $i < 6; $i++) {
            $customId = generateUniqueIdWithPrefix('CUSTOM', 'test_orders', 'order_no');
            $customs[] = $customId;
            createTestOrder($customId);
        }

        $allDocuments = array_merge($invoices, $quotations, $customs);

        expect($allDocuments)->toHaveCount(18);
        expect(array_unique($allDocuments))->toHaveCount(18);

        // Check that invoices increment correctly
        expect($invoices[0])->toEndWith('-0001');
        expect($invoices[1])->toEndWith('-0002');
        expect($invoices[5])->toEndWith('-0006');
    });

    // FIX: Understand that different tables have independent serial numbers
    it('handles mixed usage patterns', function () {
        // Mix helper functions and class usage on SAME table
        $helperInvoice = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
        createTestInvoice($helperInvoice);

        $generator = app(IdGenerator::class);
        $classInvoice = $generator->generate('invoice', 'test_invoices', 'invoice_no');
        createTestInvoice($classInvoice);

        // Different table = different serial sequence
        $helperOrder = generateUniqueIdWithPrefix('ORD', 'test_orders', 'order_no');
        createTestOrder($helperOrder);

        $classOrder = $generator->generateWithPrefix('ORD', 'test_orders', 'order_no');
        createTestOrder($classOrder);

        // Same table increments together
        expect($helperInvoice)->toEndWith('-0001');
        expect($classInvoice)->toEndWith('-0002');

        // Different table starts from 0001 again
        expect($helperOrder)->toEndWith('-0001');
        expect($classOrder)->toEndWith('-0002');
    });
});
