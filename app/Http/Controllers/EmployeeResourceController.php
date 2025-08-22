<?php

namespace App\Http\Controllers;

use App\Models\EmployeeResource;
use App\Models\User;
use App\Models\Category;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Exception;

class EmployeeResourceController extends Controller
{
    /**
     * Display paginated list of employee resources with advanced filters
     */
    public function index(Request $request)
    {
        $query = QueryBuilder::for(EmployeeResource::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('resource_number'),
                AllowedFilter::partial('title'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('division_id'),
                // Add custom callback filters for date range
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }),
                // Alias: allow filtering via resource_no -> resource_number
                AllowedFilter::partial('resource_no', 'resource_number'),
            ])
            ->allowedIncludes(['attachments', 'histories'])
            ->with(['user', 'category', 'division'])
            ->latest();

        $resources = $query->paginate(15)->withQueryString();

        return view('employee_resources.index', compact('resources'));
    }

    /**
     * Show form for creating a new resource
     */
    public function create()
    {
        $users = User::all();
        $categories = Category::all();
        $divisions = Division::all();

        return view('employee_resources.create', compact('users', 'categories', 'divisions'));
    }

    /**
     * Store a new employee resource (transactional)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'division_id' => 'required|exists:divisions,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $resource = new EmployeeResource();
            $resource->id = Str::uuid();
            $resource->user_id = $request->user_id;
            $resource->category = $request->category_id;
            $resource->division_id = $request->division_id;
            $resource->resource_number = strtoupper('RES-' . Str::random(8));
            $resource->title = $request->title;
            $resource->description = $request->description;

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('employee_resources');
                $resource->attachment = $path;
            }

            $resource->save();

            DB::commit();

            return redirect()->route('employee_resources.index')
                ->with('success', 'Employee Resource created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create employee resource: ' . $e->getMessage());

            return back()->with('error', 'Failed to create employee resource. Please try again.');
        }
    }

    /**
     * Show form for editing a resource
     */
    public function edit(EmployeeResource $employeeResource)
    {
        $users = User::all();
        $categories = Category::all();
        $divisions = Division::all();

        return view('employee_resources.edit', compact('employeeResource', 'users', 'categories', 'divisions'));
    }

    /**
     * Update an existing resource (transactional)
     */
    public function update(Request $request, EmployeeResource $employeeResource)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'division_id' => 'required|exists:divisions,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $employeeResource->user_id = $request->user_id;
            $employeeResource->category = $request->category_id;
            $employeeResource->division_id = $request->division_id;
            $employeeResource->title = $request->title;
            $employeeResource->description = $request->description;

            if ($request->hasFile('attachment')) {
                if ($employeeResource->attachment) {
                    Storage::delete($employeeResource->attachment);
                }
                $path = $request->file('attachment')->store('employee_resources');
                $employeeResource->attachment = $path;
            }

            $employeeResource->save();

            DB::commit();

            return redirect()->route('employee_resources.index')
                ->with('success', 'Employee Resource updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update employee resource: ' . $e->getMessage());

            return back()->with('error', 'Failed to update employee resource. Please try again.');
        }
    }

    /**
     * Delete a resource (transactional)
     */
    public function destroy(EmployeeResource $employeeResource)
    {
        DB::beginTransaction();

        try {
            if ($employeeResource->attachment) {
                Storage::delete($employeeResource->attachment);
            }

            $employeeResource->delete();

            DB::commit();

            return redirect()->route('employee_resources.index')
                ->with('success', 'Employee Resource deleted successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete employee resource: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete employee resource. Please try again.');
        }
    }
}
