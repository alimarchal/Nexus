<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileManagementSystemRequest;
use App\Http\Requests\UpdateFileManagementSystemRequest;
use App\Models\Branch;
use App\Models\Division;
use App\Models\FileCategory;
use App\Models\FileManagementSystem;
use App\Models\Region;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class FileManagementSystemController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view file management systems', only: ['index', 'show']),
            new Middleware('role_or_permission:create file management systems', only: ['create', 'store']),
            new Middleware('role_or_permission:edit file management systems', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete file management systems', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fileManagementSystems = QueryBuilder::for(FileManagementSystem::class)
            ->visibleTo(auth()->user())
            ->allowedFilters([
                AllowedFilter::exact('file_category_id'),
                AllowedFilter::exact('fileable_type'),
                AllowedFilter::exact('fileable_id'),
                AllowedFilter::partial('digital_id'),
                AllowedFilter::partial('title'),
                AllowedFilter::scope('document_date_from', 'documentDateFrom'),
                AllowedFilter::scope('document_date_to', 'documentDateTo'),
            ])
            ->with(['fileCategory', 'fileable', 'media'])
            ->defaultSort('-document_date')
            ->paginate(request('per_page', 10))
            ->appends(request()->query());

        return view('file-management-systems.index', [
            'fileManagementSystems' => $fileManagementSystems,
            ...$this->formOptions(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('file-management-systems.create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFileManagementSystemRequest $request)
    {
        $fileable = $this->resolveFileableFromUser() ?? [
            'type' => $request->fileable_type,
            'id' => $request->fileable_id,
        ];

        $orgUnit = $this->resolveOrgUnit($fileable['type'], $fileable['id']);

        $fileManagementSystem = FileManagementSystem::create([
            ...$request->safe()->except(['pages', 'fileable_type', 'fileable_id']),
            'fileable_type' => $fileable['type'],
            'fileable_id' => $fileable['id'],
            'digital_id' => generateUniqueIdWithPrefix($orgUnit->code ?? $fileable['type'], 'file_management_systems', 'digital_id'),
        ]);

        foreach ($request->file('pages', []) as $page) {
            $fileManagementSystem->addMedia($page)->toMediaCollection('pages');
        }

        return redirect()->route('file-management-systems.index')->with('success', 'Document record created successfully. Digital ID: '.$fileManagementSystem->digital_id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FileManagementSystem $fileManagementSystem)
    {
        $fileManagementSystem->load(['fileCategory', 'fileable', 'media']);

        return view('file-management-systems.edit', [
            'fileManagementSystem' => $fileManagementSystem,
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFileManagementSystemRequest $request, FileManagementSystem $fileManagementSystem)
    {
        $fileable = $this->resolveFileableFromUser() ?? [
            'type' => $request->fileable_type,
            'id' => $request->fileable_id,
        ];

        $fileManagementSystem->update([
            ...$request->safe()->except(['pages', 'fileable_type', 'fileable_id']),
            'fileable_type' => $fileable['type'],
            'fileable_id' => $fileable['id'],
        ]);

        foreach ($request->file('pages', []) as $page) {
            $fileManagementSystem->addMedia($page)->toMediaCollection('pages');
        }

        return redirect()->route('file-management-systems.index')->with('success', 'Document record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FileManagementSystem $fileManagementSystem)
    {
        $fileManagementSystem->delete();

        return redirect()->route('file-management-systems.index')->with('success', 'Document record deleted successfully.');
    }

    /**
     * Delete a single uploaded page/media item from a document record.
     */
    public function destroyMedia(FileManagementSystem $fileManagementSystem, int $media)
    {
        abort_unless(auth()->user()->can('edit file management systems'), 403);

        $fileManagementSystem->media()->where('id', $media)->firstOrFail()->delete();

        return redirect()->back()->with('success', 'Page removed successfully.');
    }

    /**
     * Shared dropdown data for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'fileCategories' => FileCategory::where('is_active', '1')->orderBy('category_name')->get(),
            'branches' => Branch::orderBy('name')->get(),
            'regions' => Region::orderBy('name')->get(),
            'divisions' => Division::orderBy('name')->get(),
            'autoFileable' => $this->resolveFileableFromUser(),
        ];
    }

    /**
     * Force the org unit to the logged-in user's own branch when they belong to
     * one, so branch/region/division staff can't reassign documents to another
     * unit. Users without a branch (head-office/super-admin) pick manually.
     *
     * @return array{type: string, id: int, label: string}|null
     */
    private function resolveFileableFromUser(): ?array
    {
        $user = auth()->user();

        if (! $user->branch_id) {
            return null;
        }

        $branch = $user->branch ?? Branch::find($user->branch_id);

        return [
            'type' => 'branch',
            'id' => $user->branch_id,
            'label' => ($branch?->code ? $branch->code.' - ' : '').($branch?->name ?? 'Branch #'.$user->branch_id),
        ];
    }

    /**
     * Resolve the polymorphic org-unit model for digital ID prefix generation.
     */
    private function resolveOrgUnit(string $fileableType, int $fileableId): Branch|Region|Division|null
    {
        return match ($fileableType) {
            'branch' => Branch::find($fileableId),
            'region' => Region::find($fileableId),
            'division' => Division::find($fileableId),
            default => null,
        };
    }
}
