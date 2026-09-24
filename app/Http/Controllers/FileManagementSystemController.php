<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecideFileManagementTransferRequest;
use App\Http\Requests\StoreBoxRequest;
use App\Http\Requests\StoreFileArchivingRequest;
use App\Http\Requests\StoreFileManagementSystemRequest;
use App\Http\Requests\StoreFileManagementTransferRequest;
use App\Http\Requests\UpdateFileManagementSystemRequest;
use App\Models\Box;
use App\Models\Branch;
use App\Models\Division;
use App\Models\FileCategory;
use App\Models\FileManagementSystem;
use App\Models\FileManagementTransfer;
use App\Models\HeadOffice;
use App\Models\Region;
use App\Models\User;
use App\Services\FileManagementExportService;
use App\Services\FileManagementSystemPathGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
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
            new Middleware('role_or_permission:export file management systems', only: ['exportCsv', 'exportZip']),
            new Middleware('role_or_permission:create file management systems', only: ['create', 'store']),
            new Middleware('role_or_permission:edit file management systems', only: ['edit', 'update']),
            new Middleware('role_or_permission:transfer file management systems', only: ['transfer', 'storeTransfer']),
            new Middleware('role_or_permission:approve file management transfers', only: ['decideTransfer']),
            new Middleware('role_or_permission:delete file management systems', only: ['destroy']),
            new Middleware('role_or_permission:archive file management systems', only: ['archiveForm', 'storeArchive']),
            new Middleware('role_or_permission:create boxes', only: ['createBox', 'storeBox']),
            new Middleware('role_or_permission:manage boxes', only: ['boxesList']),
        ];
    }

    /** Rows-per-page choices on the list. */
    public const PER_PAGE = [10, 25, 50, 100, 300];

    /**
     * File list: /product/file-management-systems -- same layout as the AKSIC
     * case list (KPI cards, status tabs, filters with chips, sortable table).
     * Only the user's own office's files are listed (visibleTo).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = in_array((int) $request->query('per_page'), self::PER_PAGE, true) ? (int) $request->query('per_page') : 25;

        $fileManagementSystems = $this->listQuery($request)
            ->with(['fileCategory:id,category_name', 'fileable', 'box:id,box_number,location', 'currentCustodian:id,name'])
            ->withCount(['media as pages_count', 'transfers as pending_transfers_count' => fn ($q) => $q->where('status', 'pending')])
            ->paginate($perPage)
            ->withQueryString();

        $request->session()->put('fms.list_url', $request->fullUrl());

        return view('file-management-systems.index', [
            'fileManagementSystems' => $fileManagementSystems,
            'stats' => $this->listStats($request),
            'perPage' => $perPage,
            'officeLabel' => FileManagementSystem::officeLabelFor($user),
            ...$this->formOptions(),
        ]);
    }

    /**
     * The list query (office scope, filters, sort) shared by the screen and
     * the exports, so an export always contains exactly what the list shows.
     */
    private function listQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(FileManagementSystem::query()->visibleTo($request->user()), $request)
            ->allowedFilters($this->listFilters())
            ->allowedSorts(['document_date', 'created_at', 'digital_id', 'file_no', 'title'])
            ->defaultSort('-document_date')
            ->orderByDesc('created_at');
    }

    /**
     * Filtered list (or the ticked rows) as a CSV for Excel.
     */
    public function exportCsv(Request $request, FileManagementExportService $export)
    {
        $query = $this->exportQuery($request);
        $this->logExport($request, 'csv', (clone $query)->count());

        return $export->csv($query, 'file-management-'.now()->format('Ymd-His').'.csv');
    }

    /**
     * Filtered list (or the ticked rows) as a ZIP: one folder per file with
     * all its scanned pages and documents, plus 00-index.csv.
     */
    public function exportZip(Request $request, FileManagementExportService $export)
    {
        abort_unless(class_exists(\ZipArchive::class), 500, 'The PHP zip extension is not installed on the server.');

        $query = $this->exportQuery($request);
        $size = $export->measure($query);
        $back = session('fms.list_url', route('file-management-systems.index'));

        if ($size['files'] === 0) {
            return redirect($back)->with('error', 'Nothing to download: no files match.');
        }
        if ($size['files'] > FileManagementExportService::MAX_ZIP_FILES || $size['bytes'] > FileManagementExportService::MAX_ZIP_BYTES) {
            return redirect($back)->with('error', sprintf(
                'Too much for one ZIP: %s files, %s of pages (limit %s files / %s). Narrow the filter or tick fewer rows.',
                number_format($size['files']), FileManagementExportService::humanBytes($size['bytes']),
                number_format(FileManagementExportService::MAX_ZIP_FILES), FileManagementExportService::humanBytes(FileManagementExportService::MAX_ZIP_BYTES)
            ));
        }

        @set_time_limit(300);
        $label = Str::slug(FileManagementSystem::officeLabelFor($request->user()), '_') ?: 'files';
        $zip = $export->zip($query, $label);
        $this->logExport($request, 'zip', $zip['files'], ['pages' => $zip['pages'], 'missing' => $zip['missing']]);

        return response()->download($zip['path'], $zip['name'], ['Content-Type' => 'application/zip'])->deleteFileAfterSend();
    }

    /**
     * List query plus, when rows were ticked, only those rows (still limited
     * to the user's office by visibleTo).
     */
    private function exportQuery(Request $request): Builder
    {
        $ids = array_values(array_filter((array) $request->input('ids', []), fn ($id) => is_string($id) && Str::isUuid($id)));

        return $this->listQuery($request)
            ->when($ids !== [], fn ($q) => $q->whereIn('file_management_systems.id', $ids))
            ->getEloquentBuilder();
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function logExport(Request $request, string $format, int $count, array $extra = []): void
    {
        activity('file-management')
            ->causedBy($request->user())
            ->event('exported')
            ->withProperties(['format' => $format, 'files' => $count, 'filters' => (array) $request->input('filter', []), 'selected' => count((array) $request->input('ids', []))] + $extra)
            ->log('File list exported as '.strtoupper($format));
    }

    /**
     * @return array<int, AllowedFilter>
     */
    private function listFilters(): array
    {
        return [
            AllowedFilter::exact('file_category_id'),
            AllowedFilter::partial('digital_id'),
            AllowedFilter::partial('file_no'),
            AllowedFilter::partial('title'),
            AllowedFilter::scope('branch_id'),
            AllowedFilter::scope('region_id'),
            AllowedFilter::scope('division_id'),
            AllowedFilter::scope('document_date_from', 'documentDateFrom'),
            AllowedFilter::scope('document_date_to', 'documentDateTo'),
            AllowedFilter::scope('box_number'),
            // One search box: digital id / file no from the start, title anywhere.
            AllowedFilter::callback('search', function ($query, $value): void {
                $value = trim((string) $value);
                if ($value === '') {
                    return;
                }
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
                $query->where(fn ($q) => $q
                    ->where('digital_id', 'like', $escaped.'%')
                    ->orWhere('file_no', 'like', $escaped.'%')
                    ->orWhere('title', 'like', '%'.$escaped.'%'));
            }),
            // Tabs: in circulation / archived in a box.
            AllowedFilter::callback('status', function ($query, $value): void {
                $query->where('is_archived', $value === 'archived');
            }),
        ];
    }

    /**
     * Totals for the KPI cards and tabs: same filters as the list except the
     * status tab, so the tab counts always add up.
     *
     * @return array{files: int, archived: int, active: int, pages: int, this_month: int, incoming: int}
     */
    private function listStats(Request $request): array
    {
        $statsRequest = $request->duplicate(['filter' => collect((array) $request->query('filter', []))->except('status')->all()]);
        $scoped = fn () => QueryBuilder::for(FileManagementSystem::query()->visibleTo($request->user()), $statsRequest)
            ->allowedFilters($this->listFilters())
            ->getEloquentBuilder();

        $totals = $scoped()->toBase()
            ->selectRaw('count(*) as files, sum(case when is_archived = 1 then 1 else 0 end) as archived')
            ->first();
        $unit = FileManagementSystem::officeUnitOf($request->user());

        return [
            'files' => (int) $totals->files,
            'archived' => (int) $totals->archived,
            'active' => (int) $totals->files - (int) $totals->archived,
            'pages' => Media::query()->where('model_type', (new FileManagementSystem)->getMorphClass())
                ->whereIn('model_id', $scoped()->select('file_management_systems.id'))->count(),
            'this_month' => $scoped()->where('created_at', '>=', now()->startOfMonth())->count(),
            'incoming' => FileManagementTransfer::query()->where('status', 'pending')
                ->when(! $this->isSuperAdmin(), fn ($q) => $unit
                    ? $q->where('destination_fileable_type', $unit[0])->where('destination_fileable_id', $unit[1])
                    : $q->whereRaw('1 = 0'))
                ->count(),
        ];
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
            'current_custodian_id' => auth()->id(),
            'digital_id' => generateUniqueIdWithPrefix($orgUnit->code ?? $fileable['type'], 'file_management_systems', 'digital_id'),
        ]);

        foreach ($request->file('pages', []) as $page) {
            $this->logPageUploaded($fileManagementSystem, $this->addPage($fileManagementSystem, $page));
        }

        return redirect()->route('file-management-systems.show', $fileManagementSystem)->with('success', 'File saved. Digital ID: '.$fileManagementSystem->digital_id);
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

        $fileManagementSystem->load(['fileCategory', 'fileable', 'creator', 'updater', 'currentCustodian', 'media', 'box', 'transfers.recipient', 'transfers.requester', 'transfers.decider']);

        return view('file-management-systems.show', [
            'fileManagementSystem' => $fileManagementSystem,
            'activityHistory' => $this->activityHistory($fileManagementSystem),
            'approvableTransferIds' => $this->approvableTransferIds($fileManagementSystem),
            'listUrl' => session('fms.list_url', route('file-management-systems.index')),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FileManagementSystem $fileManagementSystem)
    {
        $this->findVisible($fileManagementSystem);

        $fileManagementSystem->load(['fileCategory', 'fileable', 'creator', 'updater', 'currentCustodian', 'media', 'transfers.recipient', 'transfers.requester', 'transfers.decider']);

        return view('file-management-systems.edit', [
            'fileManagementSystem' => $fileManagementSystem,
            'activityHistory' => $this->activityHistory($fileManagementSystem),
            'approvableTransferIds' => $this->approvableTransferIds($fileManagementSystem),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFileManagementSystemRequest $request, FileManagementSystem $fileManagementSystem)
    {
        $this->findVisible($fileManagementSystem);

        $fileManagementSystem->update([
            ...$request->safe()->except(['pages']),
        ]);

        foreach ($request->file('pages', []) as $page) {
            $this->logPageUploaded($fileManagementSystem, $this->addPage($fileManagementSystem, $page));
        }

        return redirect()->route('file-management-systems.index')->with('success', 'Document record updated successfully.');
    }

    public function transfer(FileManagementSystem $fileManagementSystem)
    {
        $this->findVisible($fileManagementSystem);

        return view('file-management-systems.transfer', [
            'fileManagementSystem' => $fileManagementSystem->load(['fileable', 'currentCustodian']),
            ...$this->transferOptions(),
        ]);
    }

    public function storeTransfer(StoreFileManagementTransferRequest $request, FileManagementSystem $fileManagementSystem)
    {
        $fileManagementSystem = $this->findVisible($fileManagementSystem);
        $destination = $this->resolveOrgUnit($request->string('destination_fileable_type')->toString(), $request->integer('destination_fileable_id'));
        $recipient = User::query()->findOrFail($request->integer('recipient_id'));

        abort_unless($destination && $this->userBelongsToUnit($recipient, $request->string('destination_fileable_type')->toString(), $destination->id), 422);

        $hasPendingTransfer = $fileManagementSystem->transfers()->where('status', 'pending')->exists();
        abort_if($hasPendingTransfer, 422, 'A pending transfer request already exists for this record.');

        FileManagementTransfer::create([
            'file_management_system_id' => $fileManagementSystem->id,
            'source_fileable_type' => $fileManagementSystem->fileable_type,
            'source_fileable_id' => $fileManagementSystem->fileable_id,
            'destination_fileable_type' => $request->string('destination_fileable_type')->toString(),
            'destination_fileable_id' => $destination->id,
            'recipient_id' => $recipient->id,
            'requested_by' => auth()->id(),
            'reason' => $request->string('reason')->toString(),
        ]);

        return redirect()->route('file-management-systems.show', $fileManagementSystem)->with('success', 'Transfer request submitted for approval.');
    }

    public function decideTransfer(DecideFileManagementTransferRequest $request, FileManagementSystem $fileManagementSystem, FileManagementTransfer $transfer)
    {
        abort_unless($transfer->file_management_system_id === $fileManagementSystem->id, 404);
        abort_unless($this->canApproveTransfer($transfer), 403);
        abort_unless($transfer->status === 'pending', 422, 'This transfer request has already been decided.');

        if ($request->string('decision')->toString() === 'rejected') {
            $transfer->update([
                'status' => 'rejected',
                'decided_by' => auth()->id(),
                'decision_note' => $request->string('decision_note')->toString(),
            ]);

            return redirect()->route('file-management-systems.show', $fileManagementSystem)->with('success', 'Transfer request rejected.');
        }

        $previousFileable = [
            'type' => $fileManagementSystem->fileable_type,
            'id' => $fileManagementSystem->fileable_id,
        ];

        DB::transaction(function () use ($transfer, $fileManagementSystem, $previousFileable, $request): void {
            $fileManagementSystem->update([
                'fileable_type' => $transfer->destination_fileable_type,
                'fileable_id' => $transfer->destination_fileable_id,
                'current_custodian_id' => $transfer->recipient_id,
            ]);
            $fileManagementSystem->load('media');
            $this->moveMediaForTransfer($fileManagementSystem, $previousFileable);

            $transfer->update([
                'status' => 'approved',
                'decided_by' => auth()->id(),
                'decision_note' => $request->string('decision_note')->toString() ?: null,
            ]);

            activity('file-management')
                ->performedOn($fileManagementSystem)
                ->causedBy(auth()->user())
                ->event('transferred')
                ->withProperties([
                    'transfer_id' => $transfer->id,
                    'from' => $previousFileable,
                    'to' => ['type' => $transfer->destination_fileable_type, 'id' => $transfer->destination_fileable_id],
                    'reason' => $transfer->reason,
                    'requester_id' => $transfer->requested_by,
                    'recipient_id' => $transfer->recipient_id,
                    'approver_id' => auth()->id(),
                ])
                ->log('File management record transferred');
        });

        return redirect()->route('file-management-systems.show', $fileManagementSystem)->with('success', 'Transfer request approved and record ownership updated.');
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

    /**
     * @return array<string, mixed>
     */
    private function transferOptions(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(),
            'regions' => Region::orderBy('name')->get(),
            'divisions' => Division::orderBy('name')->get(),
            'headOffices' => HeadOffice::orderBy('name')->get(),
            'transferRecipients' => User::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'branch_id', 'region_id', 'division_id', 'head_office_id']),
        ];
    }

    private function userBelongsToUnit(User $user, string $type, int $id): bool
    {
        return match ($type) {
            'branch' => $user->branch_id === $id,
            'region' => $user->region_id === $id,
            'division' => $user->division_id === $id,
            'head-office' => $user->head_office_id === $id,
            default => false,
        };
    }

    private function canApproveTransfer(FileManagementTransfer $transfer): bool
    {
        $user = auth()->user();

        return $user->can('approve file management transfers')
            && ($this->isSuperAdmin() || $user->hasRole('head-office') || $this->userBelongsToUnit($user, $transfer->destination_fileable_type, $transfer->destination_fileable_id));
    }

    /**
     * @return array<int, string>
     */
    private function approvableTransferIds(FileManagementSystem $fileManagementSystem): array
    {
        return $fileManagementSystem->transfers
            ->filter(fn (FileManagementTransfer $transfer): bool => $transfer->status === 'pending' && $this->canApproveTransfer($transfer))
            ->pluck('id')
            ->all();
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
     * Show archiving form for a file.
     */
    public function archiveForm(FileManagementSystem $fileManagementSystem)
    {
        // Authorization check - user can only archive files from their org unit
        if (! $fileManagementSystem->load('fileable')->fileable) {
            abort(404, 'Record not found');
        }

        $userOrgUnit = $this->getUserOrgUnit();
        if ($fileManagementSystem->fileable_type !== $userOrgUnit['type'] ||
            $fileManagementSystem->fileable_id !== $userOrgUnit['id']) {
            abort(403, 'You cannot archive files outside your organization unit');
        }

        // Get available boxes for this org unit
        $boxes = Box::where('boxable_type', $fileManagementSystem->fileable_type)
            ->where('boxable_id', $fileManagementSystem->fileable_id)
            ->where('status', 'open')
            ->get();

        return view('file-management-systems.archive', compact('fileManagementSystem', 'boxes'));
    }

    /**
     * Archive a file to a box.
     */
    public function storeArchive(FileManagementSystem $fileManagementSystem, StoreFileArchivingRequest $request)
    {
        $validated = $request->validated();
        $box = Box::findOrFail($validated['box_id']);

        // Verify box belongs to same org unit
        if ($box->boxable_type !== $fileManagementSystem->fileable_type ||
            $box->boxable_id !== $fileManagementSystem->fileable_id) {
            return redirect()->back()->with('error', 'Selected box does not belong to your organization unit');
        }

        // Check if box can accept more files
        if (! $box->canAcceptFiles()) {
            return redirect()->back()->with('error', 'Selected box is full or closed');
        }

        DB::transaction(function () use ($fileManagementSystem, $box) {
            $fileManagementSystem->load('media');

            $fileManagementSystem->update([
                'box_id' => $box->id,
                'is_archived' => true,
                'archived_at' => now(),
                'position_in_box' => $box->file_count + 1,
            ]);

            $this->moveMediaForArchive($fileManagementSystem, $box);

            // Update box file count
            $box->increment('file_count');

            // Check if box is now full
            if ($box->file_count >= $box->capacity) {
                $box->update(['status' => 'full']);
            }

            // Log activity
            activity('file-management')
                ->performedOn($fileManagementSystem)
                ->causedBy(auth()->user())
                ->event('archived')
                ->withProperties([
                    'box_id' => $box->id,
                    'box_number' => $box->box_number,
                    'position_in_box' => $fileManagementSystem->position_in_box,
                    'archived_at' => $fileManagementSystem->archived_at->toIso8601String(),
                ])
                ->log('File management record archived to box');
        });

        return redirect()
            ->route('file-management-systems.show', $fileManagementSystem)
            ->with('success', "File archived to {$box->box_number}");
    }

    /**
     * Show create box form.
     */
    public function createBox()
    {
        $userOrgUnit = $this->getUserOrgUnit();

        return view('file-management-systems.create-box', compact('userOrgUnit'));
    }

    /**
     * Store a new box.
     */
    public function storeBox(StoreBoxRequest $request)
    {
        $validated = $request->validated();
        $userOrgUnit = $this->getUserOrgUnit();

        $box = DB::transaction(function () use ($validated, $userOrgUnit) {
            $box = Box::create([
                'box_number' => Box::generateBoxNumber($userOrgUnit['type'], $userOrgUnit['id']),
                'boxable_type' => $userOrgUnit['type'],
                'boxable_id' => $userOrgUnit['id'],
                'location' => $validated['location'],
                'capacity' => $validated['capacity'] ?? 100,
                'created_by' => auth()->id(),
            ]);

            activity('file-management')
                ->performedOn($box)
                ->causedBy(auth()->user())
                ->event('created')
                ->withProperties([
                    'box_number' => $box->box_number,
                    'location' => $box->location,
                    'capacity' => $box->capacity,
                ])
                ->log('Archive box created');

            return $box;
        });

        return redirect()
            ->route('file-management-systems.boxes')
            ->with('success', "Box {$box->box_number} created successfully");
    }

    /**
     * List all boxes for user's org unit.
     */
    public function boxesList()
    {
        $boxes = Box::visibleTo(auth()->user())
            ->with(['creator', 'boxable'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('file-management-systems.boxes-list', compact('boxes'));
    }

    /**
     * Get current user's organization unit.
     *
     * @return array{type: string, id: int}
     */
    private function getUserOrgUnit(): array
    {
        $user = auth()->user();

        if ($user->hasRole('branch') && $user->branch_id) {
            return ['type' => (new Branch)->getMorphClass(), 'id' => $user->branch_id];
        }

        if ($user->hasRole('region') && $user->region_id) {
            return ['type' => (new Region)->getMorphClass(), 'id' => $user->region_id];
        }

        if ($user->hasRole('division') && $user->division_id) {
            return ['type' => (new Division)->getMorphClass(), 'id' => $user->division_id];
        }

        if ($user->hasRole('head-office') && $user->head_office_id) {
            return ['type' => (new HeadOffice)->getMorphClass(), 'id' => $user->head_office_id];
        }

        abort(403, 'User does not belong to any organization unit');
    }

    /**
     * Move media files into the box's folder after archiving.
     */
    private function moveMediaForArchive(FileManagementSystem $fileManagementSystem, Box $box): void
    {
        $previousRecord = clone $fileManagementSystem;
        $previousRecord->forceFill(['box_id' => null])->setRelation('box', null);

        $newRecord = clone $fileManagementSystem;
        $newRecord->setRelation('box', $box);

        $pathGenerator = app(FileManagementSystemPathGenerator::class);
        $folders = [];

        foreach ($fileManagementSystem->media as $page) {
            $page->setRelation('model', $previousRecord);
            $previousPath = $pathGenerator->getPath($page);

            $page->setRelation('model', $newRecord);
            $newPath = $pathGenerator->getPath($page);

            $folders[$page->disk.':'.$previousPath] = [
                'disk' => $page->disk,
                'previous_path' => $previousPath,
                'new_path' => $newPath,
            ];
        }

        foreach ($folders as $folder) {
            if ($folder['previous_path'] !== $folder['new_path'] && Storage::disk($folder['disk'])->exists($folder['previous_path'])) {
                Storage::disk($folder['disk'])->move($folder['previous_path'], $folder['new_path']);
            }
        }
    }

    /**
     * Move media files during file transfer between organization units.
     *
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
