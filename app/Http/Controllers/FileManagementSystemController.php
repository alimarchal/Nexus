<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileManagementSystemRequest;
use App\Http\Requests\UpdateFileManagementSystemRequest;
use App\Models\Branch;
use App\Models\Division;
use App\Models\FileCategory;
use App\Models\FileManagementSystem;
use App\Models\HeadOffice;
use App\Models\Region;
use App\Services\FileManagementSystemPathGenerator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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
                AllowedFilter::partial('digital_id'),
                AllowedFilter::partial('file_no'),
                AllowedFilter::partial('title'),
                AllowedFilter::scope('branch_id'),
                AllowedFilter::scope('region_id'),
                AllowedFilter::scope('division_id'),
                AllowedFilter::scope('document_date_from', 'documentDateFrom'),
                AllowedFilter::scope('document_date_to', 'documentDateTo'),
            ])
            ->with(['fileCategory', 'fileable', 'creator', 'updater', 'media'])
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
            $this->logPageUploaded($fileManagementSystem, $this->addPage($fileManagementSystem, $page));
        }

        return redirect()->route('file-management-systems.index')->with('success', 'Document record created successfully. Digital ID: '.$fileManagementSystem->digital_id);
    }

    /**
     * Display the specified resource.
     */
    public function show(FileManagementSystem $fileManagementSystem)
    {
        abort_unless(
            FileManagementSystem::visibleTo(auth()->user())->whereKey($fileManagementSystem->id)->exists(),
            404
        );

        $fileManagementSystem->load(['fileCategory', 'fileable', 'creator', 'updater', 'media']);

        return view('file-management-systems.show', [
            'fileManagementSystem' => $fileManagementSystem,
            'activityHistory' => $this->activityHistory($fileManagementSystem),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FileManagementSystem $fileManagementSystem)
    {
        $this->findVisible($fileManagementSystem);

        $fileManagementSystem->load(['fileCategory', 'fileable', 'media']);

        return view('file-management-systems.edit', [
            'fileManagementSystem' => $fileManagementSystem,
            'activityHistory' => $this->activityHistory($fileManagementSystem),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFileManagementSystemRequest $request, FileManagementSystem $fileManagementSystem)
    {
        $this->findVisible($fileManagementSystem);

        $fileable = $this->resolveFileableFromUser() ?? [
            'type' => $request->fileable_type,
            'id' => $request->fileable_id,
        ];

        $previousFileable = [
            'type' => $fileManagementSystem->fileable_type,
            'id' => $fileManagementSystem->fileable_id,
        ];

        $fileManagementSystem->update([
            ...$request->safe()->except(['pages', 'fileable_type', 'fileable_id']),
            'fileable_type' => $fileable['type'],
            'fileable_id' => $fileable['id'],
        ]);

        if ($previousFileable !== $fileable) {
            $this->moveMediaForTransfer($fileManagementSystem, $previousFileable);

            activity('file-management')
                ->performedOn($fileManagementSystem)
                ->causedBy(auth()->user())
                ->event('transferred')
                ->withProperties([
                    'from' => $previousFileable,
                    'to' => $fileable,
                ])
                ->log('File management record transferred');
        }

        foreach ($request->file('pages', []) as $page) {
            $this->logPageUploaded($fileManagementSystem, $this->addPage($fileManagementSystem, $page));
        }

        return redirect()->route('file-management-systems.index')->with('success', 'Document record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FileManagementSystem $fileManagementSystem)
    {
        $this->findVisible($fileManagementSystem);

        $fileManagementSystem->delete();

        return redirect()->route('file-management-systems.index')->with('success', 'Document record deleted successfully.');
    }

    /**
     * Delete a single uploaded page/media item from a document record.
     */
    public function destroyMedia(FileManagementSystem $fileManagementSystem, int $media)
    {
        abort_unless(auth()->user()->can('edit file management systems'), 403);

        $this->findVisible($fileManagementSystem);

        $page = Media::query()
            ->whereKey($media)
            ->where('model_type', $fileManagementSystem->getMorphClass())
            ->where('model_id', $fileManagementSystem->getKey())
            ->firstOrFail();

        activity('file-management')
            ->performedOn($fileManagementSystem)
            ->causedBy(auth()->user())
            ->event('page_removed')
            ->withProperties($this->pageActivityProperties($page))
            ->log('File management page removed');

        $page->delete();

        return redirect()->back()->with('success', 'Page removed successfully.');
    }

    /**
     * Shared dropdown data for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $isSuperAdmin = $this->isSuperAdmin();

        return [
            'fileCategories' => FileCategory::where('is_active', '1')->orderBy('category_name')->get(),
            'branches' => $isSuperAdmin ? Branch::orderBy('name')->get() : collect(),
            'regions' => $isSuperAdmin ? Region::orderBy('name')->get() : collect(),
            'divisions' => $isSuperAdmin ? Division::orderBy('name')->get() : collect(),
            'headOffices' => $isSuperAdmin ? HeadOffice::orderBy('name')->get() : collect(),
            'autoFileable' => $this->resolveFileableFromUser(),
            'isSuperAdmin' => $isSuperAdmin,
        ];
    }

    /**
     * Resolve the logged-in user's assigned organization unit. Non-super-admins
     * cannot create or move records outside this unit.
     *
     * @return array{type: string, id: int, label: string}|null
     */
    private function resolveFileableFromUser(): ?array
    {
        $user = auth()->user();

        if ($this->isSuperAdmin()) {
            return null;
        }

        $orgUnits = [
            'branch' => $user->branch,
            'region' => $user->region,
            'division' => $user->division,
            'head-office' => $user->headOffice,
        ];

        foreach (['branch', 'region', 'division', 'head-office'] as $type) {
            $orgUnit = $orgUnits[$type];

            if ($user->hasRole($type) && $orgUnit) {
                return [
                    'type' => $type,
                    'id' => $orgUnit->id,
                    'label' => $type === 'division' ? ($orgUnit->short_name ?: $orgUnit->name) : $orgUnit->name,
                ];
            }
        }

        return null;
    }

    /**
     * Resolve the polymorphic org-unit model for digital ID prefix generation.
     */
    private function resolveOrgUnit(string $fileableType, int $fileableId): Branch|Region|Division|HeadOffice|null
    {
        return match ($fileableType) {
            'branch' => Branch::find($fileableId),
            'region' => Region::find($fileableId),
            'division' => Division::find($fileableId),
            'head-office' => HeadOffice::find($fileableId),
            default => null,
        };
    }

    private function isSuperAdmin(): bool
    {
        $user = auth()->user();

        return $user->is_super_admin === 'Yes' || $user->hasRole('super-admin');
    }

    private function findVisible(FileManagementSystem $fileManagementSystem): FileManagementSystem
    {
        return FileManagementSystem::visibleTo(auth()->user())->findOrFail($fileManagementSystem->id);
    }

    private function addPage(FileManagementSystem $fileManagementSystem, mixed $page): Media
    {
        $originalFilename = $page->getClientOriginalName();

        return $fileManagementSystem->addMedia($page)
            ->usingFileName(Str::uuid().'_'.$originalFilename)
            ->withCustomProperties(['original_filename' => $originalFilename])
            ->toMediaCollection('pages');
    }

    private function logPageUploaded(FileManagementSystem $fileManagementSystem, Media $page): void
    {
        activity('file-management')
            ->performedOn($fileManagementSystem)
            ->causedBy(auth()->user())
            ->event('page_uploaded')
            ->withProperties($this->pageActivityProperties($page))
            ->log('File management page uploaded');
    }

    /**
     * @return array{media_id: int, original_filename: string, stored_filename: string}
     */
    private function pageActivityProperties(Media $page): array
    {
        return [
            'media_id' => $page->id,
            'original_filename' => $page->getCustomProperty('original_filename', $page->file_name),
            'stored_filename' => $page->file_name,
        ];
    }

    private function activityHistory(FileManagementSystem $fileManagementSystem): Collection
    {
        return Activity::query()
            ->where('subject_type', FileManagementSystem::class)
            ->where('subject_id', $fileManagementSystem->getKey())
            ->with('causer')
            ->latest()
            ->get();
    }

    /**
     * @param  array{type: string, id: int}  $previousFileable
     */
    private function moveMediaForTransfer(FileManagementSystem $fileManagementSystem, array $previousFileable): void
    {
        $previousRecord = clone $fileManagementSystem;
        $previousRecord->forceFill([
            'fileable_type' => $previousFileable['type'],
            'fileable_id' => $previousFileable['id'],
        ]);

        $pathGenerator = app(FileManagementSystemPathGenerator::class);
        $folders = [];

        foreach ($fileManagementSystem->media as $page) {
            $page->setRelation('model', $previousRecord);
            $previousPath = $pathGenerator->getPath($page);

            $page->setRelation('model', $fileManagementSystem);
            $newPath = $pathGenerator->getPath($page);

            $folders[$page->disk.':'.$previousPath] = [
                'disk' => $page->disk,
                'previous_path' => $previousPath,
                'new_path' => $newPath,
            ];
        }

        foreach ($folders as $folder) {
            if ($folder['previous_path'] !== $folder['new_path']) {
                Storage::disk($folder['disk'])->move($folder['previous_path'], $folder['new_path']);
            }
        }
    }
}
