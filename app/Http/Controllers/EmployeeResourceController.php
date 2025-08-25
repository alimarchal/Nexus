<?php

namespace App\Http\Controllers;

use App\Models\EmployeeResource;
use App\Models\User;
use App\Models\Category;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class EmployeeResourceController extends Controller
{
    /**
     * Display a listing of the employee resources.
     */
    public function index()
    {
        $resources = EmployeeResource::with(['user', 'category', 'division'])->latest()->paginate(10);
        return view('employee_resources.index', compact('resources'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        $categories = Category::all();
        $divisions = Division::all();

        return view('employee_resources.create', compact('users', 'categories', 'divisions'));
    }

    /**
     * Store a newly created resource in storage.
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

        try {
            $resource = new EmployeeResource();
            $resource->id = Str::uuid();
            $resource->user_id = $request->user_id;

            $resource->category_id = $request->category_id;
            $resource->division_id = $request->division_id;
            $resource->resource_no = $request->resource_no;
            $resource->resource_number = strtoupper('RES-' . Str::random(8));
            $resource->title = $request->title;
            $resource->description = $request->description;

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('employee_resources');
                $resource->attachment = $path;
            }

            $resource->save();

            return redirect()->route('employee_resources.index')->with('success', 'Employee Resource created successfully!');
        } catch (Exception $e) {
            Log::error('Failed to create employee resource: ' . $e->getMessage());
            return back()->with('error', 'Failed to create employee resource. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeResource $employeeResource)
    {
        $users = User::all();
        $categories = Category::all();
        $divisions = Division::all();

        return view('employee_resources.edit', compact('employeeResource', 'users', 'categories', 'divisions'));
    }

    /**
     * Update the specified resource in storage.
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

        try {
            $employeeResource->user_id = $request->user_id;
            $employeeResource->category = $request->category_id;

            $employeeResource->division_id = $request->division_id;
            $employeeResource->resource_no = $request->resource_no;
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

            return redirect()->route('employee_resources.index')->with('success', 'Employee Resource updated successfully!');
        } catch (Exception $e) {
            Log::error('Failed to update employee resource: ' . $e->getMessage());
            return back()->with('error', 'Failed to update employee resource. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeResource $employeeResource)
    {
        try {
            if ($employeeResource->attachment) {
                Storage::delete($employeeResource->attachment);
            }

            $employeeResource->delete();

            return redirect()->route('employee_resources.index')->with('success', 'Employee Resource deleted successfully!');
        } catch (Exception $e) {
            Log::error('Failed to delete employee resource: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete employee resource. Please try again.');
        }
    }
}
