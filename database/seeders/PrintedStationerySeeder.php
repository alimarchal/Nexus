<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class PrintedStationerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a system user ID for tracking
        $userId = User::first()->id ?? 1;
        $now = Carbon::now();

        // Data extracted from Excel file (Book1.xlsx)
        $items = [
            ['item_code' => 'BAJK-27', 'name' => 'INWARD CLEARING REGISTER'],
            ['item_code' => 'BAJK-28', 'name' => 'CASH ORDER REGISTER'],
            ['item_code' => 'BAJK-29', 'name' => 'DEMAND DRAFT PAYABLE REGISTER'],
            ['item_code' => 'BAJK-30', 'name' => 'OUTWARD CLEARING REGISTER'],
            ['item_code' => 'BAJK-32', 'name' => 'DEPOSIT AT CALL REGISTER'],
            ['item_code' => 'BAJK-33', 'name' => 'DEMAND DRAFT ISSUANCE REGISTER'],
            ['item_code' => 'BAJK-34', 'name' => 'SUBISIDERIES'],
            ['item_code' => 'BAJK-35', 'name' => 'FURNITURE AND FIXTURE REGISTER'],
            ['item_code' => 'BAJK-36', 'name' => 'ACCOUNT OPENING AND CLOSED REGISTER'],
            ['item_code' => 'BAJK-37', 'name' => 'CHEQUE BOOK ISSUANCE REGISTER'],
            ['item_code' => 'BAJK-38', 'name' => 'TRANSFER REGISTER'],
            ['item_code' => 'BAJK-39', 'name' => 'OUTWARD BILL FOR COLLECTION REGISTER'],
            ['item_code' => 'BAJK-40', 'name' => 'INWARD BILL FOR COLLECTION REGISTER'],
            ['item_code' => 'BAJK-41', 'name' => 'DEMAND GOLD FINANCE REGISTER'],
            ['item_code' => 'BAJK-42', 'name' => 'DEMAND FINANCE REGISTER'],
            ['item_code' => 'BAJK-43', 'name' => 'SAFE IN SAFE OUT REGISTER'],
            ['item_code' => 'BAJK-44', 'name' => 'RUNNING FINANCE REGISTER'],
            ['item_code' => 'BAJK-45', 'name' => 'CASH SAFE IN SAFE OUT REGISTER'],
            ['item_code' => 'BAJK-46', 'name' => 'PLS TERM DEPOSIT REGISTER'],
            ['item_code' => 'BAJK-47', 'name' => 'CHEQUE RETURN REGISTER'],
            ['item_code' => 'BAJK-49', 'name' => 'SECURITY STATIONERY REGISTER'],
            ['item_code' => 'BAJK-50', 'name' => 'ATTENDENCE REGISTER'],
            ['item_code' => 'BAJK-51', 'name' => 'RECEIVING CASHIER REGISTER'],
            ['item_code' => 'BAJK-52', 'name' => 'PAYING CASHIER REGISTER'],
            ['item_code' => 'BAJK-53', 'name' => 'LETTER DISPATCH REGISTER'],
            ['item_code' => 'BAJK-54', 'name' => 'LETTER RECEIPT REGISTER'],
            ['item_code' => 'BAJK-55', 'name' => 'KEY REGISTER'],
            ['item_code' => 'BAJK-56', 'name' => 'STOCK STATIONERY REGISTER'],
            ['item_code' => 'BAJK-57', 'name' => 'LEAVE RECORD REGISTER'],
            ['item_code' => 'BAJK-58', 'name' => 'VOUCHER REGISTER'],
            ['item_code' => 'BAJK-59', 'name' => 'PAY ORDER REGISTER'],
            ['item_code' => 'BAJK-60', 'name' => 'SAFE IN SAFE OUT REGISTER CAD'],
            ['item_code' => 'Empty-01', 'name' => 'REMITTANCE REGISTER'],
            ['item_code' => 'Empty-22', 'name' => 'LOCKER RENT REGISTER'],
            ['item_code' => 'BAJK-101', 'name' => 'SAVING LEDGER'],
            ['item_code' => 'BAJK-102', 'name' => 'CURRENT LEDGER'],
            ['item_code' => 'BAJK-103', 'name' => 'GENERAL LEDGER'],
            ['item_code' => 'F-01', 'name' => 'CB/SB/CHEQUE BOOK ISSUE'],
            ['item_code' => 'F-02', 'name' => 'DEBIT TRANSFER VOUCHER'],
            ['item_code' => 'F-03', 'name' => 'CREDIT TRANSFER VOUCHER'],
            ['item_code' => 'F-04', 'name' => 'CREDIT CASH VOUCHER'],
            ['item_code' => 'F-05', 'name' => 'DEBIT CASH VOUCHER'],
            ['item_code' => 'F-07', 'name' => 'HO DEBIT ADVICE'],
            ['item_code' => 'F-08', 'name' => 'SCHEDULE OF BILL FOR COLLECTION'],
            ['item_code' => 'F-09', 'name' => 'DEBIT SUPLEMENTRY'],
            ['item_code' => 'F-10', 'name' => 'CREDIT SUPLEMENTRY'],
            ['item_code' => 'F-11', 'name' => 'HO EXTRACT RESPONDING'],
            ['item_code' => 'F-12', 'name' => 'HO EXTRACT ORIGINATING'],
            ['item_code' => 'F-13', 'name' => 'CALL DEPOSIT APPLICATION'],
            ['item_code' => 'F-14', 'name' => 'APPLICATION FOR TERM DEPOSIT'],
            ['item_code' => 'F-15', 'name' => 'LETTER OF THANKS TO CLIENT'],
            ['item_code' => 'F-17', 'name' => 'CHEQUE BOOK REQUISITION'],
            ['item_code' => 'F-18', 'name' => 'OBJECTION MEMO'],
            ['item_code' => 'F-19', 'name' => 'CASH MEMO'],
            ['item_code' => 'F-20', 'name' => 'CASH BALANCE BOOK'],
            ['item_code' => 'F-21', 'name' => 'BALANCE CONFIRMATION'],
            ['item_code' => 'EMPTY-02', 'name' => 'LOCKER FORM'],
            ['item_code' => 'Empty-03', 'name' => 'EDD FORM'],
            ['item_code' => 'Empty-04', 'name' => 'ACCOUNT OPENING FORM'],
            ['item_code' => 'Empty-05', 'name' => 'SPECIMEN SIGNATURE CARD'],
            ['item_code' => 'Empty-06', 'name' => 'PEON BOOK'],
            ['item_code' => 'Empty-07', 'name' => 'PASS BOOK'],
            ['item_code' => 'Empty-08', 'name' => 'FILE COVER'],
            ['item_code' => 'Empty-09', 'name' => 'FILE FOLDER'],
            ['item_code' => 'Empty-10', 'name' => 'VOUCHER COVER'],
            ['item_code' => 'Empty-11', 'name' => 'LETTER HEAD HEAD OFFICE'],
            ['item_code' => 'Empty-12', 'name' => 'LETTER HEAD BRANCHES'],
            ['item_code' => 'Empty-13', 'name' => 'DEPOSIT SLIP'],
            ['item_code' => 'Empty-14', 'name' => 'FILE SIZE ENVELOPE'],
            ['item_code' => 'Empty-15', 'name' => 'A-4 SIZE ENVELOPE'],
            ['item_code' => 'Empty-16', 'name' => 'LARGE SIZE ENVELOPE'],
            ['item_code' => 'Empty-17', 'name' => 'SMALL SIZE ENVELOPE'],
            ['item_code' => 'Empty-18', 'name' => 'WINDOW ENVELOPE'],
            ['item_code' => 'Empty-19', 'name' => 'FILE SIZE ENVELOPE FOR CHECQUE BOOK'],
            ['item_code' => 'Empty-20', 'name' => 'A-4 SIZE ENVELOPE FOR CHECQUE BOOK'],
            ['item_code' => 'Empty-21', 'name' => 'LARGE SIZE ENVELOPE FOR CHECQUE BOOK'],
        ];

        // Add user tracking fields to each item
        foreach ($items as &$item) {
            $item['created_by'] = $userId;
            $item['updated_by'] = $userId;
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        // Insert data into the database
        DB::table('printed_stationeries')->insert($items);

        $this->command->info('Printed stationery items seeded successfully: ' . count($items) . ' records.');
    }
}
