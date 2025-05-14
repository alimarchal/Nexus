<?php

namespace App\Http\Controllers;

use App\Models\DispatchRegister;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Carbon\Carbon;

class DispatchRegisterController extends Controller
{
    public function index(Request $request)
    {
        // Implement Spatie Query Builder properly
        $dispatches = QueryBuilder::for(DispatchRegister::class)
            ->allowedFilters([
                // Basic filters
                AllowedFilter::exact('division_id'),
                AllowedFilter::exact('dispatch_no'),
                // Custom filters
                AllowedFilter::callback('year', function ($query, $value) {
                    $query->whereYear('date', $value);
                }),
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('date', '>=', Carbon::parse($value));
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('date', '<=', Carbon::parse($value));
                }),
                // Add more filters based on the database schema
                AllowedFilter::partial('particulars'),
                AllowedFilter::partial('address'),
                AllowedFilter::partial('name_of_courier_service'),
                AllowedFilter::partial('receipt_no'),
                AllowedFilter::partial('reference_number'),
            ])
            ->with('division') // Eager load division relationship
            ->latest()
            ->paginate(10)
            ->withQueryString(); // Add query string to pagination links

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

            // Get division data for reference number
            $division = Division::findOrFail($request->division_id);

            // Generate unique reference number with division short name
            $referenceNumber = $this->generateReferenceNumber($division);

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
                ->route('dispatch-registers.index')
                ->with('success', "Dispatch record created successfully! Reference Number: {$referenceNumber}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create dispatch record. ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique reference number for dispatch registers including division short name.
     *
     * @param Division $division
     * @return string
     */
    private function generateReferenceNumber(Division $division)
    {
        // Get division short name, fallback to name if short_name is not set
        $divisionCode = $division->short_name ?: $division->name;
        $divisionCode = strtoupper($divisionCode);

        $year = date('Y');
        $prefix = "DISP-{$divisionCode}/{$year}";

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

        // Get the last record for this division and year
        $lastDispatch = $query
            ->where('division_id', $division->id)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        // Set the starting number
        $nextNumber = 1;

        if ($lastDispatch && $lastDispatch->reference_number) {
            // Extract the numerical part after the last hyphen
            $parts = explode('-', $lastDispatch->reference_number);
            $lastPart = end($parts);

            // Handle multiple formats (with or without division code)
            if (strpos($lastPart, '/') !== false) {
                // Format is like DISP-DIV/2025-00001
                $lastNumberStr = substr($lastPart, strpos($lastPart, '-') + 1);
            } else {
                // Format is like DISP-2025-00001
                $lastNumberStr = $lastPart;
            }

            $nextNumber = is_numeric($lastNumberStr) ? (int) $lastNumberStr + 1 : 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
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
                ->route('dispatch-registers.index')
                ->with('success', 'Receipt number and attachment updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update receipt number. ' . $e->getMessage());
        }
    }

    // The destroy method commented out in original code
    // public function destroy(DispatchRegister $dispatchRegister)
    // {
    //     try {
    //         // Delete attachment if exists
    //         if ($dispatchRegister->attachment) {
    //             Storage::disk('public')->delete($dispatchRegister->attachment);
    //         }

    //         $dispatchRegister->delete();

    //         return redirect()
    //             ->route('dispatch-registers.index')
    //             ->with('success', 'Dispatch record deleted successfully.');
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Failed to delete dispatch record. Please try again.');
    //     }
    // }
}
