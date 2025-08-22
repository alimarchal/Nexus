<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComplaintWitnessHarassmentController extends Controller
{
      // Form show karne ke liye
    public function create()
    {
        return view('complaints.harassment_create'); // tumhari Blade file ka path
    }

    // Form submit handle karne ke liye
    public function store(Request $request)
    {
        $validated = $request->validate([
            'complaint_id' => 'required|integer',
            'accused_name' => 'required|string|max:255',
            'accused_designation' => 'required|string|max:255',
            'accused_id' => 'nullable|string|max:255',
            'incident_datetime' => 'required|date',
            'incident_location' => 'required|string|max:255',
            'harassment_type' => 'required|string',
            'witnesses' => 'nullable|array',
            'witnesses.*.name' => 'required|string|max:255',
            'witnesses.*.designation' => 'required|string|max:255',
            'witnesses.*.id' => 'nullable|string|max:255',
        ]);

        ComplaintWitnessHarassment::create($validated);

        return redirect()->route('complaints.index')->with('success', 'Harassment complaint saved successfully!');
    }
}
