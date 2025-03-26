<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHrdRequest;
use App\Http\Requests\UpdateHrdRequest;
use App\Models\Hrd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrdController extends Controller
{
  
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('hrd.index');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hrd.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHrdRequest $request)
    {
        // Handle file upload
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('', 'public');
            $request->merge(['document' => $path]);
        }

        $request->merge(['user_id' => Auth::user()->id]);

        Hrd::create($request->all());
        // Redirect or perform any additional actions
        return to_route('hrd.index')->with('status', 'HRD file has been uploaded successfully!');
    }

    public function categoryId(Request $request, $categoryId)
    {
        $manuals = Hrd::where('category_id', $categoryId)->get();
        return view('hrd.category', compact('manuals','categoryId'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Hrd $hrd)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hrd $hrd)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHrdRequest $request, Hrd $hrd)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hrd $hrd)
    {
        //
    }
}

