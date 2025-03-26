<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManualRequest;
use App\Http\Requests\UpdateManualRequest;
use App\Models\Category;
use App\Models\Manual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManualController extends Controller
{
   
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('manual.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manual.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreManualRequest $request)
    {
        // Handle file upload
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('', 'public');
            $request->merge(['document' => $path]);
        }

        $request->merge(['user_id' => Auth::user()->id]);

        Manual::create($request->all());
        // Redirect or perform any additional actions
        return to_route('manual.index')->with('status', 'Manual uploaded successfully!');
    }

    public function categoryId(Request $request, $categoryId)
    {
        $manuals = Manual::where('category_id', $categoryId)->get();
        return view('manual.category', compact('manuals','categoryId'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Manual $manual)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manual $manual)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateManualRequest $request, Manual $manual)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manual $manual)
    {
        //
    }
}
