# Laravel ID Generator

A powerful Laravel package to generate **unique, prefixed IDs** with database transaction safety and race condition protection. Perfect for invoices, complaints, quotations, orders, and any document that needs professional numbering.

```php
// Generate professional IDs instantly
$invoiceId = generateUniqueId('invoice', 'invoices', 'invoice_no');
// Output: INV-20250814-0001

$complaintId = generateUniqueId('complaint', 'complaints', 'complaint_no');
// Output: CMP-20250814-0001
```

## 🎯 Why You Need This Package

### The Problem
When building applications, you often need to generate unique document numbers like:
- Invoice Numbers: `INV-20250814-0001`
- Complaint IDs: `CMP-20250814-0001` 
- Order Numbers: `ORD-20250814-0001`

**Common challenges:**
- **Race Conditions**: Two users creating records simultaneously can get the same number
- **Non-Professional Format**: Simple auto-increment IDs look unprofessional
- **No Business Logic**: Hard to identify document type from the number
- **Maintenance Overhead**: Writing custom numbering logic for each model

### The Solution
This package provides:
- ✅ **Race Condition Safe**: Uses database transactions and row locking
- ✅ **Professional Format**: `PREFIX-YYYYMMDD-XXXX` format
- ✅ **Zero Conflicts**: Guaranteed unique IDs across your entire application
- ✅ **Laravel Native**: Follows Laravel conventions and best practices
- ✅ **Future Proof**: Compatible with Laravel 11, 12, and beyond
- ✅ **Auto-Scalable**: Handles millions of records without performance issues

## 🚀 Installation

```bash
composer require alimarchal/id-generator
```

**That's it!** Thanks to Laravel's auto-discovery, the package is immediately ready to use.

## 📋 Setup (One-Time)

### 1. Publish and Run Migration

```bash
php artisan vendor:publish --tag=id-generator-migrations
php artisan migrate
```

This creates an `id_prefixes` table with sample data:

| id | name      | prefix | created_at | updated_at |
|----|-----------|--------|------------|------------|
| 1  | invoice   | INV    | ...        | ...        |
| 2  | complaint | CMP    | ...        | ...        |
| 3  | quotation | QTN    | ...        | ...        |

### 2. Add Your Custom Prefixes (Optional)

```php
use Illuminate\Support\Facades\DB;

DB::table('id_prefixes')->insert([
    ['name' => 'order', 'prefix' => 'ORD'],
    ['name' => 'receipt', 'prefix' => 'RCP'],
    ['name' => 'estimate', 'prefix' => 'EST'],
]);
```

## 🎯 Usage

### Method 1: Helper Functions (Recommended)

The simplest way to generate IDs anywhere in your application.

#### Using Database Prefixes

```php
// Generate invoice number using 'invoice' type from database
$invoiceId = generateUniqueId('invoice', 'invoices', 'invoice_no');
// Output: INV-20250814-0001

// Generate complaint number
$complaintId = generateUniqueId('complaint', 'complaints', 'complaint_no');
// Output: CMP-20250814-0001

// Generate quotation number
$quotationId = generateUniqueId('quotation', 'quotations', 'quotation_no');
// Output: QTN-20250814-0001
```

#### Using Direct Prefixes

```php
// Generate ID with custom prefix (no database lookup)
$orderId = generateUniqueIdWithPrefix('ORD', 'orders', 'order_no');
// Output: ORD-20250814-0001

$customId = generateUniqueIdWithPrefix('CUST', 'customers', 'customer_id');
// Output: CUST-20250814-0001
```

### Method 2: Dependency Injection

Perfect when you need more control or are following SOLID principles.

```php
<?php

namespace App\Http\Controllers;

use Alimarchal\IdGenerator\IdGenerator;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function store(Request $request, IdGenerator $idGenerator)
    {
        // Method 1: Using database prefix
        $invoiceNumber = $idGenerator->generate('invoice', 'invoices', 'invoice_no');
        
        // Method 2: Using direct prefix
        $orderNumber = $idGenerator->generateWithPrefix('ORD', 'orders', 'order_no');

        $invoice = Invoice::create([
            'invoice_no' => $invoiceNumber,
            'customer_name' => $request->customer_name,
            'amount' => $request->amount,
        ]);

        return response()->json(['invoice' => $invoice], 201);
    }
}
```

## 📊 Real-World Examples

### E-Commerce Platform

```php
class OrderController extends Controller
{
    public function createOrder(Request $request, IdGenerator $idGenerator)
    {
        $orderNumber = $idGenerator->generate('order', 'orders', 'order_no');
        // Output: ORD-20250814-0001
        
        $invoiceNumber = $idGenerator->generate('invoice', 'invoices', 'invoice_no');
        // Output: INV-20250814-0001
        
        $trackingNumber = $idGenerator->generateWithPrefix('TRK', 'shipments', 'tracking_no');
        // Output: TRK-20250814-0001
        
        // Create order with all numbers...
    }
}
```

### Customer Support System

```php
class TicketController extends Controller
{
    public function createTicket(Request $request)
    {
        $ticketNumber = generateUniqueId('ticket', 'tickets', 'ticket_no');
        // Output: TKT-20250814-0001
        
        $ticket = Ticket::create([
            'ticket_no' => $ticketNumber,
            'title' => $request->title,
            'priority' => $request->priority,
        ]);
        
        return response()->json(['ticket' => $ticket], 201);
    }
}
```

### Complete Controller Example

```php
<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        // Validate request...
        $request->validate([
            'customer_name' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        // Generate unique invoice number
        $invoiceNumber = generateUniqueId('invoice', 'invoices', 'invoice_no');

        // Create invoice
        $invoice = Invoice::create([
            'invoice_no' => $invoiceNumber,
            'customer_name' => $request->customer_name,
            'amount' => $request->amount,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Invoice created successfully!',
            'invoice' => $invoice
        ], 201);
    }
}
```

## 🚀 Scalability & Performance

### Built for Scale
- **Millions of Records**: Tested with millions of records without performance degradation
- **High Concurrency**: Can handle hundreds of simultaneous requests without conflicts
- **Database Optimized**: Uses efficient queries with proper indexing
- **Memory Efficient**: Minimal memory footprint

### Race Condition Protection

```php
// This package handles this scenario automatically:
// User A and User B create invoices at the EXACT same millisecond

// Without protection:
// User A gets: INV-20250814-0001
// User B gets: INV-20250814-0001 ❌ (DUPLICATE!)

// With this package:
// User A gets: INV-20250814-0001 ✅
// User B gets: INV-20250814-0002 ✅ (UNIQUE!)
```

## 🔄 Laravel Compatibility

This package is designed to work with current and future Laravel versions:

- ✅ **Laravel 11** - Fully supported
- ✅ **Laravel 12** - Ready for future releases
- ✅ **PHP 8.2+** - Modern PHP support

### Auto-Update Strategy
- **Semantic Versioning**: We follow [SemVer](https://semver.org/) for predictable updates
- **Laravel Compatibility**: New Laravel versions are supported within 30 days of release
- **Backward Compatibility**: Minor updates never break existing functionality

## ⚙️ Advanced Configuration

### Multiple Environments

```php
// Different prefixes for different environments
// In your AppServiceProvider boot method:

if (app()->environment('production')) {
    DB::table('id_prefixes')->updateOrInsert(
        ['name' => 'invoice'],
        ['prefix' => 'INV']
    );
} else {
    DB::table('id_prefixes')->updateOrInsert(
        ['name' => 'invoice'],
        ['prefix' => 'TEST-INV']
    );
}
```

### Custom Prefixes Management

```php
// Add new prefix types dynamically
DB::table('id_prefixes')->insert([
    'name' => 'purchase-order',
    'prefix' => 'PO',
    'created_at' => now(),
    'updated_at' => now()
]);

// Use the new prefix
$poNumber = generateUniqueId('purchase-order', 'purchase_orders', 'po_number');
// Output: PO-20250814-0001
```

## 🐛 Error Handling

### Graceful Degradation

```php
// The package includes built-in error handling
try {
    $invoiceId = generateUniqueId('invoice', 'invoices', 'invoice_no');
} catch (\Exception $e) {
    // If normal generation fails, package provides fallback
    // Fallback format: INVOICE-1692012345-1234 (timestamp-based)
    Log::error("ID generation failed: " . $e->getMessage());
    // You still get a unique ID, just less pretty
}
```

### Common Issues & Solutions

#### Issue: "Prefix not found"
```php
// Solution: Add the prefix to your database
DB::table('id_prefixes')->insert([
    'name' => 'your-type',
    'prefix' => 'YOUR-PREFIX'
]);
```

#### Issue: Column doesn't exist
```php
// Solution: Ensure your table has the target column
Schema::table('your_table', function (Blueprint $table) {
    $table->string('your_id_column')->unique();
});
```

## 📚 API Reference

### Helper Functions

#### `generateUniqueId(string $type, string $table, string $column): string`

**Parameters:**
- `$type`: Type name from `id_prefixes` table (e.g., 'invoice')
- `$table`: Target database table name
- `$column`: Target column name for the ID

**Returns:** Formatted unique ID (e.g., 'INV-20250814-0001')

**Throws:** `\Exception` if type not found in database

#### `generateUniqueIdWithPrefix(string $prefix, string $table, string $column): string`

**Parameters:**
- `$prefix`: Direct prefix string (e.g., 'INV', 'CUSTOM')
- `$table`: Target database table name  
- `$column`: Target column name for the ID

**Returns:** Formatted unique ID with custom prefix

### Class Methods

#### `IdGenerator::generate(string $type, string $table, string $column): string`

Class method equivalent to `generateUniqueId()` helper function.

#### `IdGenerator::generateWithPrefix(string $prefix, string $table, string $column): string`

Class method equivalent to `generateUniqueIdWithPrefix()` helper function.

## 🔧 Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- MySQL, PostgreSQL, SQLite, or SQL Server

## 📖 Format Specification

### ID Format: `PREFIX-YYYYMMDD-XXXX`

- **PREFIX**: 2-10 characters identifying the document type
- **YYYYMMDD**: Date in ISO format (e.g., 20250814 for August 14, 2025)
- **XXXX**: 4-digit sequential number starting from 0001 each day

### Examples

| Document Type | Generated ID | Description |
|---------------|--------------|-------------|
| Invoice | `INV-20250814-0001` | First invoice of August 14, 2025 |
| Complaint | `CMP-20250814-0001` | First complaint of August 14, 2025 |
| Order | `ORD-20250814-0053` | 53rd order of August 14, 2025 |
| Custom | `CUSTOM-20250814-0001` | Custom prefix example |

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
git clone https://github.com/alimarchal/id-generator.git
cd id-generator
composer install
```

### Reporting Issues

If you find a bug or have a feature request, please [open an issue](https://github.com/alimarchal/id-generator/issues) on GitHub.

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🙋 Support

- **Issues**: [GitHub Issues](https://github.com/alimarchal/id-generator/issues)
- **Discussions**: [GitHub Discussions](https://github.com/alimarchal/id-generator/discussions)
- **Email**: kh.marchal@gmail.com

## 🔗 Related Packages

- [Laravel UUID](https://github.com/webpatser/laravel-uuid) - For UUID generation
- [Laravel Hashids](https://github.com/vinkla/laravel-hashids) - For URL-safe ID encoding

---

**Made with ❤️ for the Laravel community by [Ali Marchal](https://github.com/alimarchal)**