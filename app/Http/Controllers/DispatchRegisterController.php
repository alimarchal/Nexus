<?php

namespace App\Http\Controllers;

use App\Models\DispatchRegister;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Carbon\Carbon;
class DispatchRegisterController extends Controller
{


    public function index(Request $request)
    {
        $query = DispatchRegister::with('division');

        // Extract filters from nested input
        $filters = $request->input('filter', []);

        // Year filter (applied on 'date' column)
        if (!empty($filters['year'])) {
            $query->whereYear('date', $filters['year']);
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', Carbon::parse($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', Carbon::parse($filters['date_to']));
        }

        // Division filter
        if (!empty($filters['division_id'])) {
            $query->where('division_id', $filters['division_id']);
        }

        $dispatches = $query->latest()->paginate(10);
        $divisions = Division::all();

        return view('dispatch_registers.index', compact('dispatches', 'divisions'));
    }

    public function create()
    {
        $divisions = Division::all();
        return view('dispatch_registers.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'dispatch_no' => 'required|unique:dispatch_registers',
                'date' => 'required|date',
                'particulars' => 'required',
                'address' => 'required',
                'division_id' => 'required|exists:divisions,id',
                'attachment' => 'nullable|file|max:2048',
                // reference_number is no longer required from the user
            ]);

            // Generate unique reference number
            $referenceNumber = $this->generateReferenceNumber();

            // Create data array with all needed fields
            $data = $request->only([
                'dispatch_no',
                'date',
                'particulars',
                'address',
                'division_id',
                'name_of_courier_service',
                'receipt_no'
            ]);

            // Add generated reference number
            $data['reference_number'] = $referenceNumber;

            // Add user IDs for created_by and updated_by
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            // Handle file upload if present
            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
            }

            // Create the record with all required fields
            $dispatch = DispatchRegister::create($data);

            DB::commit();

            return redirect()
                ->route('dispatch_registers.index')
                ->with('success', "Dispatch record created successfully! Reference Number: {$referenceNumber}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create dispatch record. ' . $e->getMessage());
        }

    }

    /**
     * Generate a unique reference number for dispatch registers.
     */
    private function generateReferenceNumber()
    {
        $prefix = 'DISP-' . date('Y');

        // Check if the model uses SoftDeletes trait
        $usesSoftDeletes = in_array(
            'Illuminate\Database\Eloquent\SoftDeletes',
            class_uses_recursive(DispatchRegister::class)
        );

        // Build the query
        $query = DispatchRegister::query();

        // Add withTrashed only if SoftDeletes is used
        if ($usesSoftDeletes) {
            $query->withTrashed();
        }

        // Get the last record for this year
        $lastDispatch = $query->whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastDispatch && $lastDispatch->reference_number) {
            // Extract the numerical part after the last hyphen
            $lastNumberStr = substr($lastDispatch->reference_number, strrpos($lastDispatch->reference_number, '-') + 1);
            $lastNumber = is_numeric($lastNumberStr) ? (int) $lastNumberStr : 0;
        } else {
            $lastNumber = 0;
        }

        return $prefix . '-' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    public function show(DispatchRegister $dispatchRegister)
    {
        return view('dispatch_registers.show', compact('dispatchRegister'));
    }

    public function edit(DispatchRegister $dispatchRegister)
    {
        $divisions = Division::all();
        return view('dispatch_registers.edit', compact('dispatchRegister', 'divisions'));
    }

    public function update(Request $request, DispatchRegister $dispatchRegister)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'receipt_no' => 'nullable|string|max:255',
                'attachment' => 'nullable|file|max:2048',
            ]);

            $data = [
                'receipt_no' => $request->receipt_no,
                'updated_by' => Auth::id(),
            ];

            // Handle file upload if present
            if ($request->hasFile('attachment')) {
                // Delete old file if it exists
                if ($dispatchRegister->attachment) {
                    Storage::disk('public')->delete($dispatchRegister->attachment);
                }

                // Store new file
                $path = $request->file('attachment')->store('attachments', 'public');
                $data['attachment'] = $path;
            }

            $dispatchRegister->update($data);

            DB::commit();

            return redirect()
                ->route('dispatch_registers.index')
                ->with('success', 'Receipt number and attachment updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update receipt number. ' . $e->getMessage());
        }
    }


    // public function destroy(DispatchRegister $dispatchRegister)
    // {
    //     try {
    //         // Delete attachment if exists
    //         if ($dispatchRegister->attachment) {
    //             Storage::disk('public')->delete($dispatchRegister->attachment);
    //         }

    //         $dispatchRegister->delete();

    //         return redirect()
    //             ->route('dispatch_registers.index')
    //             ->with('success', 'Dispatch record deleted successfully.');
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Failed to delete dispatch record. Please try again.');
    //     }
    }
