<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Models\Division;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ManagerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view managers', only: ['index', 'show']),
            new Middleware('role_or_permission:create managers', only: ['create', 'store']),
            new Middleware('role_or_permission:edit managers', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete managers', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Manager::with(['createdBy', 'division', 'managerUser']);

        if ($divisionId = $request->input('filter.division_id')) {
            $query->where('division_id', $divisionId);
        }

        if ($title = $request->input('filter.title')) {
            $query->where('title', 'LIKE', '%'.$title.'%');
        }

        if ($createdByUserId = $request->input('filter.created_by_user_id')) {
            $query->where('created_by_user_id', $createdByUserId);
        }

        if ($createdAt = $request->input('filter.created_at')) {
            $query->whereDate('created_at', $createdAt);
        }

        $divisions = Division::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $managers = $query->latest()->paginate(10)->withQueryString();

        return view('managers.index', compact('divisions', 'managers', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        $divisions = Division::all();

        return view('managers.create', compact('users', 'divisions'));
    }

    public function edit(Manager $manager)
    {
        $users = User::all();
        $divisions = Division::all();

        return view('managers.edit', compact('manager', 'users', 'divisions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreManagerRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['created_by_user_id'] = auth()->id();
        $validatedData['updated_by'] = auth()->id();

        Manager::create($validatedData);

        return redirect()->route('managers.index')->with('success', 'Manager created successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateManagerRequest $request, Manager $manager)
    {
        $validatedData = $request->validated();
        $validatedData['updated_by'] = auth()->id();

        $manager->update($validatedData);

        return redirect()->route('managers.index')->with('success', 'Manager updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Manager $manager)
    {
        return view('managers.show', compact('manager'));
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manager $manager)
    {
        $manager->delete();

        return redirect()->route('managers.index')->with('success', 'Manager deleted successfully.');
    }
}
