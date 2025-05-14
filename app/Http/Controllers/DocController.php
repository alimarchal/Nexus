<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Division;
use App\Models\Doc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Carbon\Carbon;

class DocController extends Controller
{
    public function index(Request $request)
    {
        // Implement Spatie Query Builder properly
        $docs = QueryBuilder::for(Doc::class)
            ->allowedFilters([
                // Basic filters
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('division_id'),

                // Custom filters
                AllowedFilter::callback('created_at', function ($query, $value) {
                    $query->whereDate('created_at', $value);
                }),

                // Partial filters
                AllowedFilter::partial('title'),

                // User filter (if needed)
                AllowedFilter::exact('user_id'),
            ])
            ->with(['user', 'category', 'division']) // Eager load relationships
            ->latest()
            ->paginate(10)
            ->withQueryString(); // Add query string to pagination links

        $categories = Category::all();
        $divisions = Division::all();

        return view('docs.index', compact('docs', 'categories', 'divisions'));
    }

    public function create()
    {
        $categories = Category::all();
        $divisions = Division::all();
        return view('docs.create', compact('categories', 'divisions'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'division_id' => 'required|exists:divisions,id',
                'document' => 'required|file|mimes:pdf,doc,docx|max:2048'
            ]);

            // Add user ID
            $validated['user_id'] = Auth::id();

            // Handle file upload
            if ($request->hasFile('document')) {
                $validated['document'] = $request->file('document')->store('documents', 'public');
            }

            // Create the document
            $doc = Doc::create($validated);

            DB::commit();

            return redirect()
                ->route('docs.index')
                ->with('success', 'Document created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create document. ' . $e->getMessage());
        }
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
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'division_id' => 'required|exists:divisions,id',
                'document' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
            ]);

            // Handle file upload if a new file is provided
            if ($request->hasFile('document')) {
                // Delete old document if it exists
                if ($doc->document && Storage::disk('public')->exists($doc->document)) {
                    Storage::disk('public')->delete($doc->document);
                }

                // Store the new document
                $validated['document'] = $request->file('document')->store('documents', 'public');
            }

            // Update the document
            $doc->update($validated);

            DB::commit();

            return redirect()
                ->route('docs.index')
                ->with('success', 'Document updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update document. ' . $e->getMessage());
        }
    }

    public function destroy(Doc $doc)
    {
        DB::beginTransaction();

        try {
            // Delete associated document file
            if ($doc->document && Storage::disk('public')->exists($doc->document)) {
                Storage::disk('public')->delete($doc->document);
            }

            // Delete the record
            $doc->delete();

            DB::commit();

            return redirect()
                ->route('docs.index')
                ->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete document. ' . $e->getMessage());
        }
    }
}
