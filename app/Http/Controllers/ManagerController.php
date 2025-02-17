<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Models\Manager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Instead of get(), use paginate() for paginated results
        $managers = Manager::paginate(10); // Adjust the number as needed

        return view('managers.index', compact('managers'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = \App\Models\User::all();
        $divisions = \App\Models\Division::all();
        return view('managers.create', compact('users', 'divisions'));
    }

    public function edit(Manager $manager)
    {
        $users = \App\Models\User::all();
        $divisions = \App\Models\Division::all();
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