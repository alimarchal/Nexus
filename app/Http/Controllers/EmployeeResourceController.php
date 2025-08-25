<?php

namespace App\Http\Controllers;

use App\Helpers\FileStorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeResourceRequest;
use App\Http\Requests\UpdateEmployeeResourceRequest;
use App\Models\Division;
use App\Models\EmployeeResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Str;

class EmployeeResourceController extends Controller
{
    public function index(Request $request)
    {
        $employeeResources = QueryBuilder::for(EmployeeResource::class)
            ->allowedFilters([
                AllowedFilter::exact('division_id'),
                AllowedFilter::partial('resource_number'),
                AllowedFilter::partial('resource_no'),
                AllowedFilter::partial('reference_no'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }),
            ])
            ->with(['division', 'category', 'user', 'updatedBy'])
            ->latest()
            ->paginate(10);

        return view('employee_resources.index', compact('employeeResources'));
    }

    public function show(EmployeeResource $employee_resource)
    {
        $employee_resource->load(['division', 'category', 'user', 'updatedBy']);
        return view('employee_resources.show', ['resource' => $employee_resource]);
    }

    public function create()
    {
        $divisions = Division::all();
        $categories = Category::orderBy('name')->get();
        return view('employee_resources.create', compact('divisions', 'categories'));
    }

    public function store(StoreEmployeeResourceRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            // Generate system reference using existing id_prefixes naming pattern (use 'employee_resource')
            $validated['resource_number'] = generateUniqueId('employee_resource', 'employee_resources', 'resource_number');

            $division = Division::find($validated['division_id']);
            $divisionFolder = $division->name; // Use proper division name (no slug)
            // Use system generated reference number as sole folder; sanitize disallowed chars
            $referenceFolder = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '-', $validated['resource_number']);
            $folderPath = 'Employee Resources/' . $divisionFolder . '/' . $referenceFolder; // e.g. Employee Resources/Commercial Retail Banking Division/ER-2025-0001

            if ($request->hasFile('attachment')) {
                $validated['attachment'] = FileStorageHelper::storeSinglePrivateFile(
                    $request->file('attachment'),
                    $folderPath
                );
            }

            // Mirror resource_number into reference_no unless explicitly provided
            $validated['reference_no'] = $validated['reference_no'] ?? $validated['resource_number'];

            $resource = EmployeeResource::create($validated);
            DB::commit();
            return redirect()->route('employee_resources.index')->with('success', "Employee Resource '{$resource->title}' created successfully.");
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'resource_no')) {
                return back()->withInput()->with('error', 'Resource number already exists.');
            }
            Log::error('DB error creating employee resource', ['error' => $e->getMessage(), 'user_id' => auth()->id()]);
            return back()->withInput()->with('error', 'Database error occurred.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating employee resource', ['error' => $e->getMessage(), 'user_id' => auth()->id()]);
            return back()->withInput()->with('error', 'Failed to create employee resource.');
        }
    }

    public function edit(EmployeeResource $employee_resource)
    {
        $divisions = Division::all();
        $categories = Category::orderBy('name')->get();
        return view('employee_resources.edit', ['resource' => $employee_resource, 'divisions' => $divisions, 'categories' => $categories]);
    }

    public function update(UpdateEmployeeResourceRequest $request, EmployeeResource $employee_resource)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $division = Division::find($validated['division_id']);
            $divisionFolder = $division->name; // proper name
            $referenceFolder = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '-', $employee_resource->resource_number); // immutable system ref
            $folderPath = 'Employee Resources/' . $divisionFolder . '/' . $referenceFolder;

            if ($request->hasFile('attachment')) {
                if ($employee_resource->attachment) {
                    FileStorageHelper::deletePrivateFile($employee_resource->attachment);
                }
                $validated['attachment'] = FileStorageHelper::storeSinglePrivateFile(
                    $request->file('attachment'),
                    $folderPath
                );
            }

            // Preserve original resource_number (system reference) if not changed intentionally
            if (isset($validated['resource_number'])) {
                unset($validated['resource_number']);
            }
            // Keep reference_no synced if not manually changed
            if (!isset($validated['reference_no'])) {
                $validated['reference_no'] = $employee_resource->reference_no ?? $employee_resource->resource_number;
            }
            $isUpdated = $employee_resource->update($validated);
            if (!$isUpdated) {
                DB::rollBack();
                return back()->with('info', 'No changes were made.');
            }
            DB::commit();
            return redirect()->route('employee_resources.index')->with('success', 'Employee Resource updated successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'resource_no')) {
                return back()->withInput()->with('error', 'Resource number already exists.');
            }
            Log::error('DB error updating employee resource', ['id' => $employee_resource->id, 'error' => $e->getMessage(), 'user_id' => auth()->id()]);
            return back()->withInput()->with('error', 'Database error occurred.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee resource', ['id' => $employee_resource->id, 'error' => $e->getMessage(), 'user_id' => auth()->id()]);
            return back()->withInput()->with('error', 'Failed to update employee resource.');
        }
    }

    public function destroy(EmployeeResource $employee_resource)
    {
        DB::beginTransaction();
        try {
            // Delete attachment file if exists
            if ($employee_resource->attachment) {
                try {
                    FileStorageHelper::deletePrivateFile($employee_resource->attachment);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete employee resource file', ['id' => $employee_resource->id, 'error' => $e->getMessage()]);
                }
            }
            $employee_resource->delete();
            DB::commit();
            return redirect()->route('employee_resources.index')->with('success', 'Employee Resource deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting employee resource', ['id' => $employee_resource->id, 'error' => $e->getMessage()]);
            return redirect()->route('employee_resources.index')->with('error', 'Failed to delete Employee Resource.');
        }
    }
}
