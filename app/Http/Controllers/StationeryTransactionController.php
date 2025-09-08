<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStationeryTransactionRequest;
use App\Http\Requests\UpdateStationeryTransactionRequest;
use App\Models\Branch;
use App\Models\Division;
use App\Models\PrintedStationery;
use App\Models\Region;
use App\Models\StationeryTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StationeryTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $transactions = QueryBuilder::for(StationeryTransaction::class)
            ->allowedFilters([
                AllowedFilter::exact('printed_stationery_id'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('stock_out_to'),
                AllowedFilter::exact('branch_id'),   // Added filter for branch
                AllowedFilter::exact('region_id'),   // Added filter for region
                AllowedFilter::exact('division_id'), // Added filter for division
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('transaction_date', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('transaction_date', '<=', $value);
                }),
                AllowedFilter::callback('min_quantity', function ($query, $value) {
                    $query->where('quantity', '>=', $value);
                }),
                AllowedFilter::callback('max_quantity', function ($query, $value) {
                    $query->where('quantity', '<=', $value);
                }),
                AllowedFilter::callback('reference', function ($query, $value) {
                    $query->where('reference_number', 'like', "%{$value}%");
                }),
            ])
            ->defaultSort('-transaction_date')
            ->allowedSorts(['transaction_date', 'quantity', 'balance_after_transaction', 'type'])
            ->with(['printedStationery', 'creator', 'branch', 'region', 'division'])
            ->paginate(10)
            ->withQueryString();

        // Get data for filter dropdowns
        $stationeries = PrintedStationery::orderBy('item_code')->get();
        $branches = Branch::orderBy('name')->get();
        $regions = Region::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();

        return view('stationery-transactions.index', compact('transactions', 'stationeries', 'branches', 'regions', 'divisions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $stationeries = PrintedStationery::orderBy('item_code')->get();
        $branches = Branch::orderBy('name')->get();
        $regions = Region::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();

        // Get the pre-selected stationery if stationery_id is provided
        $selectedStationery = null;
        if ($request->has('stationery_id')) {
            $selectedStationery = PrintedStationery::find($request->stationery_id);
        }

        // Get the pre-selected transaction type if transaction_type is provided
        $selectedTransactionType = null;
        if ($request->has('transaction_type') && in_array($request->transaction_type, ['in', 'out', 'opening_balance'])) {
            $selectedTransactionType = $request->transaction_type;
        }

        // If stationery_id is provided but transaction_type is not,
        // auto-select 'in' as the default transaction type
        if ($selectedStationery && !$selectedTransactionType) {
            $selectedTransactionType = 'in';
        }

        return view('stationery-transactions.create', compact(
            'stationeries',
            'branches',
            'regions',
            'divisions',
            'selectedStationery',
            'selectedTransactionType'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStationeryTransactionRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Use find or findOrFail with the ID directly
            $stationery = PrintedStationery::findOrFail($request->printed_stationery_id);

            // Get the last transaction for this stationery
            $lastTransaction = StationeryTransaction::where('printed_stationery_id', $stationery->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $currentBalance = $lastTransaction ? $lastTransaction->balance_after_transaction : 0;

            // Check for opening balance if there are already transactions
            if ($request->type === 'opening_balance') {
                $existingOpeningBalance = StationeryTransaction::where('printed_stationery_id', $stationery->id)
                    ->where('type', 'opening_balance')
                    ->exists();

                if ($existingOpeningBalance) {
                    return back()->withErrors([
                        'type' => 'An opening balance transaction already exists for this stationery item. Please use "Stock In" instead.',
                    ])->withInput();
                }

                if ($lastTransaction) {
                    return back()->withErrors([
                        'type' => 'Cannot add opening balance as transactions already exist for this item. Please use "Stock In" instead.',
                    ])->withInput();
                }
            }

            // Calculate new balance
            $newBalance = $this->calculateNewBalance($currentBalance, $request->type, $request->quantity);

            // Perform custom validation checks
            $validator = Validator::make(['quantity' => $request->quantity], []);

            // Validate stock levels for "out" transactions
            if ($request->type === 'out' && $currentBalance < $request->quantity) {
                // Add validation error
                $validator->errors()->add('quantity', 'Insufficient stock. Current balance is ' . $currentBalance);

                // Return back with errors and maintain input
                return back()->withErrors($validator)->withInput();
            }

            // Store document if provided
            $documentPath = null;
            if ($request->hasFile('document') && $request->file('document')->isValid()) {
                // Make sure the 'public/transactions' directory exists
                if (!Storage::disk('public')->exists('transactions')) {
                    Storage::disk('public')->makeDirectory('transactions');
                }

                // Store the file and get the path
                $documentPath = $request->file('document')->store('transactions', 'public');

                // For debugging
                if (!$documentPath) {
                    throw new \Exception('Failed to store the document file.');
                }
            }


            // Create the transaction
            $transaction = StationeryTransaction::create([
                'printed_stationery_id' => $stationery->id,
                'stock_out_to' => $request->stock_out_to,
                'division_id' => $request->division_id,
                'branch_id' => $request->branch_id,
                'region_id' => $request->region_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'balance_after_transaction' => $newBalance,
                'transaction_date' => $request->transaction_date,
                'reference_number' => $request->reference_number,
                'document_path' => $documentPath,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->route('stationery-transactions.index')
                ->with('success', 'Transaction recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'An error occurred while recording the transaction: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StationeryTransaction $stationeryTransaction): View
    {
        // Load relationships
        $stationeryTransaction->load(['printedStationery', 'creator', 'updater', 'branch', 'region', 'division']);

        return view('stationery-transactions.show', compact('stationeryTransaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StationeryTransaction $stationeryTransaction): View
    {
        $stationeries = PrintedStationery::orderBy('item_code')->get();
        $branches = Branch::orderBy('name')->get();
        $regions = Region::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();

        return view('stationery-transactions.edit', compact('stationeryTransaction', 'stationeries', 'branches', 'regions', 'divisions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStationeryTransactionRequest $request, StationeryTransaction $stationeryTransaction): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Validate that we're not changing the core transaction details that would affect the balance
            if ($stationeryTransaction->type !== $request->type ||
                $stationeryTransaction->quantity != $request->quantity ||
                $stationeryTransaction->printed_stationery_id != $request->printed_stationery_id) {

                return back()->withErrors([
                    'error' => 'Cannot change transaction type, quantity or stationery item. Please delete this transaction and create a new one instead.',
                ])->withInput();
            }

            // Store document if provided
            if ($request->hasFile('document') && $request->file('document')->isValid()) {
                // Delete old document if exists
                if ($stationeryTransaction->document_path) {
                    Storage::disk('public')->delete($stationeryTransaction->document_path);
                }

                // Make sure the 'public/transactions' directory exists
                if (!Storage::disk('public')->exists('transactions')) {
                    Storage::disk('public')->makeDirectory('transactions');
                }

                // Store the file and get the path
                $documentPath = $request->file('document')->store('transactions', 'public');

                // For debugging
                if (!$documentPath) {
                    throw new \Exception('Failed to store the document file.');
                }

                $stationeryTransaction->document_path = $documentPath;
            }

            // Update non-critical fields
            $stationeryTransaction->stock_out_to = $request->stock_out_to;
            $stationeryTransaction->division_id = $request->division_id;
            $stationeryTransaction->branch_id = $request->branch_id;
            $stationeryTransaction->region_id = $request->region_id;
            $stationeryTransaction->unit_price = $request->unit_price;
            $stationeryTransaction->transaction_date = $request->transaction_date;
            $stationeryTransaction->reference_number = $request->reference_number;
            $stationeryTransaction->notes = $request->notes;
            $stationeryTransaction->save();

            DB::commit();

            return redirect()->route('stationery-transactions.index')
                ->with('success', 'Transaction updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'An error occurred while updating the transaction: ' . $e->getMessage(),
            ])->withInput();
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StationeryTransaction $stationeryTransaction): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Get all transactions for this stationery after the current one
            $subsequentTransactions = StationeryTransaction::where('printed_stationery_id', $stationeryTransaction->printed_stationery_id)
                ->where('created_at', '>', $stationeryTransaction->created_at)
                ->orderBy('created_at')
                ->get();

            // Check if there are subsequent transactions
            if ($subsequentTransactions->count() > 0) {
                return back()->withErrors([
                    'error' => 'Cannot delete this transaction as there are newer transactions that depend on it. Please delete newer transactions first.',
                ]);
            }

            // Delete the transaction
            if ($stationeryTransaction->document_path) {
                \Storage::disk('public')->delete($stationeryTransaction->document_path);
            }

            $stationeryTransaction->delete();

            DB::commit();

            return redirect()->route('stationery-transactions.index')
                ->with('success', 'Transaction deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'An error occurred while deleting the transaction: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Download the supporting document for a stationery transaction.
     */
    public function downloadDocument(StationeryTransaction $stationeryTransaction)
    {
        // Check authorization
        $this->authorize('view', $stationeryTransaction);
        
        // Check if document exists
        if (!$stationeryTransaction->document_path) {
            return redirect()->back()->with('error', 'No document found for this transaction.');
        }

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($stationeryTransaction->document_path)) {
            return redirect()->back()->with('error', 'Document file not found.');
        }

        // Return download response
        return Storage::disk('public')->download(
            $stationeryTransaction->document_path, 
            'transaction_' . $stationeryTransaction->id . '_document.' . pathinfo($stationeryTransaction->document_path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Calculate the new balance after a transaction.
     *
     * @param int $currentBalance
     * @param string $type
     * @param int $quantity
     * @return int
     */
    private function calculateNewBalance(int $currentBalance, string $type, int $quantity): int
    {
        switch ($type) {
            case 'opening_balance':
                return $quantity;
            case 'in':
                return $currentBalance + $quantity;
            case 'out':
                return $currentBalance - $quantity;
            default:
                return $currentBalance;
        }
    }
}
