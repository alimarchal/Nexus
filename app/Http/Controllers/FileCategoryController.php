<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileCategoryRequest;
use App\Http\Requests\UpdateFileCategoryRequest;
use App\Models\FileCategory;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class FileCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view file categories', only: ['index', 'show']),
            new Middleware('role_or_permission:create file categories', only: ['create', 'store']),
            new Middleware('role_or_permission:edit file categories', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete file categories', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fileCategories = QueryBuilder::for(FileCategory::class)
            ->allowedFilters([
                AllowedFilter::partial('category_name'),
                AllowedFilter::exact('category_code'),
                AllowedFilter::exact('is_active'),
            ])
            ->defaultSort('category_name')
            ->paginate(request('per_page', 10))
            ->appends(request()->query());

        return view('file-categories.index', compact('fileCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('file-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFileCategoryRequest $request)
    {
        FileCategory::create($request->validated());

        return redirect()->route('file-categories.index')->with('success', 'File category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FileCategory $fileCategory)
    {
        return view('file-categories.edit', compact('fileCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFileCategoryRequest $request, FileCategory $fileCategory)
    {
        $fileCategory->update($request->validated());

        return redirect()->route('file-categories.index')->with('success', 'File category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FileCategory $fileCategory)
    {
        $fileCategory->delete();

        return redirect()->route('file-categories.index')->with('success', 'File category deleted successfully.');
    }
}
