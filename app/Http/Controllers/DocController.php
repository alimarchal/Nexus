<?php

// app/Http/Controllers/DocController.php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Division;
use App\Models\Doc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class DocController extends Controller
{
    public function index()
    {
        $docs = QueryBuilder::for(Doc::class)
            ->with(['user', 'category', 'division'])
            ->allowedFilters([
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('division_id'),
                AllowedFilter::scope('title'),
                AllowedFilter::scope('created_at'),
            ])
            ->latest()
            ->paginate(10);

        $categories = Category::all();
        $divisions = Division::all();

        return view('docs.index', compact('docs', 'categories', 'divisions')); // ✅ Pass $docs to view
    }

    public function create()
    {
        $categories = Category::all();
        $divisions = Division::all();
        return view('docs.create', compact('categories', 'divisions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'division_id' => 'required|exists:divisions,id',
            'document' => 'required|file|mimes:pdf,doc,docx|max:2048'
        ]);

        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')->store('documents', 'public');

        }

        $validated['user_id'] = auth()->id();

        Doc::create($validated);

        return redirect()->route('docs.index')->with('success', 'Document created successfully.');
    }

    public function show(Doc $doc)
    {
        return view('docs.show', compact('doc'));
    }

    public function edit(Doc $doc)
    {
        $categories = Category::all();
        $divisions = Division::all();
        return view('docs.edit', compact('doc', 'categories', 'divisions'));
    }


public function update(Request $request, Doc $doc)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category_id' => 'required|exists:categories,id',
        'division_id' => 'required|exists:divisions,id',
        'document' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
    ]);

    if ($request->hasFile('document')) {
        // Delete old document if it exists
        if ($doc->document && Storage::disk('public')->exists($doc->document)) {
            Storage::disk('public')->delete($doc->document);
        }

        // Store the new document in the 'public/documents' folder
        $validated['document'] = $request->file('document')->store('documents', 'public');
    }

    $doc->update($validated);

    return redirect()->route('docs.index')->with('success', 'Document updated successfully.');
}
    public function destroy(Doc $doc)
    {
        // Delete associated document
        Storage::delete($doc->document);
        $doc->delete();

        return redirect()->route('docs.index')->with('success', 'Document deleted successfully.');
    }
}
