<?php

namespace App\Services;

use App\Models\AccountOpeningRequest;
use App\Models\Aksic;
use App\Models\AksicBudget;
use App\Models\Branch;
use App\Models\Division;
use App\Models\FileManagementSystem;
use App\Models\FileManagementTransfer;
use App\Models\HeadOffice;
use App\Models\Region;
use App\Models\User;
use App\Support\OfficeAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Figures for the home dashboard, always limited to what the user may see:
 *
 *   AKSIC            -> OfficeAccess (own branch / own region / every branch with "view all aksic cases")
 *   File management  -> FileManagementSystem::visibleTo (the user's own office unit)
 *   Account opening  -> AccountOpeningRequest::visibleTo (own branch / region; head office all)
 *
 * Each section needs its module view permission AND its dashboard permission
 * (SECTION_PERMISSIONS), so a role can use a module without seeing it here.
 *
 * Everything is aggregated in SQL (no row loading) and cached for a minute per
 * visibility scope; AKSIC figures refresh at once when a case changes.
 */
class DashboardService
{
    public const MONTHS = 12;

    /** @var array<string, array{0: string, 1: string}> section => [module permission, dashboard permission] */
    public const SECTION_PERMISSIONS = [
        'aksic' => ['view aksics', 'view aksic dashboard'],
        'files' => ['view file management systems', 'view file management dashboard'],
        'account_openings' => ['view account openings', 'view account opening dashboard'],
    ];

    /** Account opening statuses in display order. */
    public const AOF_STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_review' => 'Under review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public function __construct(private readonly AksicBudgetService $budgets) {}

    /**
     * @return array<string, mixed>|null null when the user may not view AKSIC cases
     */
    public function aksic(User $user): ?array
    {
        if (! $this->shows($user, 'aksic')) {
            return null;
        }

        $office = OfficeAccess::for($user);
        $canBudget = $office['level'] === OfficeAccess::ALL && $user->can('view aksic budget');
        $key = 'dashboard:aksic:v1:'.Cache::get(Aksic::STATS_VERSION_KEY, 0).':'.md5(json_encode([$office['branch_ids'], $canBudget]));

        return Cache::remember($key, 60, function () use ($user, $office, $canBudget): array {
            $scoped = fn (): Builder => OfficeAccess::scope(Aksic::query(), $user);

            $totals = $scoped()->toBase()->selectRaw("
                    count(*) as cases,
                    sum(case when status = 'Pending' then 1 else 0 end) as pending,
                    sum(case when status = 'Approved' then 1 else 0 end) as approved,
                    sum(case when status = 'Approved' then principal_amount else 0 end) as principal,
                    sum(case when status = 'Approved' then total_interest else 0 end) as markup,
                    sum(case when quota = 'Female' then 1 else 0 end) as female
                ")->first();

            $breakdownBy = match ($office['level']) {
                OfficeAccess::ALL => 'district',
                OfficeAccess::REGION => 'branch',
                default => 'category',
            };

            return [
                'office' => $office,
                'totals' => [
                    'cases' => (int) $totals->cases,
                    'pending' => (int) $totals->pending,
                    'approved' => (int) $totals->approved,
                    'principal' => round((float) $totals->principal, 2),
                    'markup' => round((float) $totals->markup, 2),
                    'female_share' => $totals->cases ? round($totals->female / $totals->cases * 100, 1) : 0.0,
                ],
                'monthly' => $this->monthly($scoped(), 'created_at', 'Approved'),
                'quota' => $this->quotaMix($scoped()),
                'breakdown_by' => $breakdownBy,
                'breakdown' => $this->aksicBreakdown($scoped(), $breakdownBy),
                // Plain arrays: the cache store does not unserialize model objects.
                'recent_pending' => $scoped()->with('branch:id,code')->where('status', 'Pending')
                    ->latest()->orderByDesc('id')->limit(6)
                    ->get(['id', 'application_no', 'name', 'branch_id', 'principal_amount', 'quota', 'created_at'])
                    ->map(fn (Aksic $case) => [
                        'url' => route('aksic.show', $case).'?nav=pending',
                        'name' => $case->name,
                        'application_no' => $case->application_no,
                        'branch' => $case->branch?->code,
                        'quota' => $case->quota,
                        'amount' => (float) $case->principal_amount,
                        'date' => $case->created_at?->format('d.m.Y'),
                    ])->all(),
                'budget' => $canBudget ? $this->budgetSummary() : null,
            ];
        });
    }

    /**
     * @return array<string, mixed>|null null when the user may not view files
     */
    public function files(User $user): ?array
    {
        if (! $this->shows($user, 'files')) {
            return null;
        }

        $unit = $this->orgUnit($user);
        $key = 'dashboard:files:v1:'.md5(json_encode($unit));

        return Cache::remember($key, 60, function () use ($user, $unit): array {
            $scoped = fn (): Builder => FileManagementSystem::query()->visibleTo($user);

            $totals = $scoped()->toBase()->selectRaw('
                    count(*) as files,
                    sum(case when is_archived = 1 then 1 else 0 end) as archived
                ')->first();
            $monthStart = now()->startOfMonth();

            $incoming = FileManagementTransfer::query()->where('status', 'pending')
                ->when($unit !== 'all', fn ($q) => $unit === null
                    ? $q->whereRaw('1 = 0')
                    : $q->where('destination_fileable_type', $unit[0])->where('destination_fileable_id', $unit[1]));
            $outgoing = FileManagementTransfer::query()->where('status', 'pending')
                ->whereIn('file_management_system_id', $scoped()->select('id'));

            return [
                'unit_label' => $this->unitLabel($user, $unit),
                'totals' => [
                    'files' => (int) $totals->files,
                    'archived' => (int) $totals->archived,
                    'active' => (int) $totals->files - (int) $totals->archived,
                    'this_month' => $scoped()->where('created_at', '>=', $monthStart)->count(),
                    'pages' => (int) DB::table('media')
                        ->where('model_type', (new FileManagementSystem)->getMorphClass())
                        ->whereIn('model_id', $scoped()->select('id'))->count(),
                    'incoming' => $incoming->count(),
                    'outgoing' => $outgoing->count(),
                ],
                'monthly' => $this->monthly($scoped(), 'created_at', null),
                'categories' => $scoped()->toBase()
                    ->leftJoin('file_categories', 'file_categories.id', '=', 'file_management_systems.file_category_id')
                    ->selectRaw("coalesce(file_categories.category_name, 'Uncategorised') as label, count(*) as total")
                    ->groupBy('label')->orderByDesc('total')->limit(8)
                    ->pluck('total', 'label')->map(fn ($v) => (int) $v)->all(),
                'recent' => $scoped()->with('fileCategory:id,category_name')->latest()->orderByDesc('id')->limit(6)
                    ->get(['id', 'digital_id', 'file_no', 'title', 'file_category_id', 'is_archived', 'document_date', 'created_at'])
                    ->map(fn (FileManagementSystem $file) => [
                        'url' => route('file-management-systems.show', $file),
                        'title' => $file->title ?: $file->file_no,
                        'digital_id' => $file->digital_id,
                        'category' => $file->fileCategory?->category_name ?? 'Uncategorised',
                        'archived' => (bool) $file->is_archived,
                        'date' => $file->document_date?->format('d.m.Y'),
                    ])->all(),
            ];
        });
    }

    /**
     * @return array<string, mixed>|null null when the section is not allowed
     */
    public function accountOpenings(User $user): ?array
    {
        if (! $this->shows($user, 'account_openings')) {
            return null;
        }

        [$level, $label] = $this->aofScope($user);
        $key = 'dashboard:aof:v1:'.md5(json_encode([$level, $user->branch_id, $user->region_id]));

        return Cache::remember($key, 60, function () use ($user, $level, $label): array {
            $scoped = fn (): Builder => AccountOpeningRequest::query()->visibleTo($user);

            $byStatus = $scoped()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
            $status = collect(self::AOF_STATUSES)->map(fn ($l, $key) => (int) ($byStatus[$key] ?? 0))->all();
            $forms = $scoped()->toBase()->selectRaw('aof_form_type, count(*) as total')->groupBy('aof_form_type')->pluck('total', 'aof_form_type');

            // Correlated sub-selects instead of joins: visibleTo() filters on an
            // unqualified branch_id, which a join would make ambiguous.
            $breakdownBy = $level === 'branch' ? 'category' : 'branch';
            $labelSql = $breakdownBy === 'branch'
                ? '(select branches.code from branches where branches.id = account_opening_requests.branch_id)'
                : '(select customer_categories.name from customers join customer_categories on customer_categories.id = customers.customer_category_id where customers.id = account_opening_requests.customer_id)';

            return [
                'label' => $label,
                'totals' => [
                    'requests' => array_sum($status),
                    'in_progress' => $status['draft'],
                    'awaiting' => $status['submitted'] + $status['under_review'],
                    'approved' => $status['approved'],
                    'rejected' => $status['rejected'],
                    'this_month' => $scoped()->where('request_date', '>=', now()->startOfMonth()->toDateString())->count(),
                    'individual' => (int) ($forms[AccountOpeningRequest::FORM_INDIVIDUAL] ?? 0),
                    'entity' => (int) ($forms[AccountOpeningRequest::FORM_ENTITY] ?? 0),
                ],
                'status' => $status,
                'monthly' => $this->monthly($scoped(), 'request_date', 'approved'),
                'breakdown_by' => $breakdownBy,
                'breakdown' => $scoped()->toBase()
                    ->selectRaw("coalesce({$labelSql}, 'Not set') as label,
                        sum(case when status = 'approved' then 1 else 0 end) as approved,
                        sum(case when status <> 'approved' then 1 else 0 end) as open_count,
                        count(*) as total")
                    ->groupBy('label')->orderByDesc('total')->limit(10)->get()
                    ->map(fn ($r) => ['label' => (string) $r->label, 'approved' => (int) $r->approved, 'pending' => (int) $r->open_count])
                    ->all(),
                'products' => $scoped()->toBase()->where('status', 'approved')->whereNotNull('account_id')
                    ->selectRaw("coalesce((select account_products.name from accounts join account_products on account_products.id = accounts.account_product_id where accounts.id = account_opening_requests.account_id), 'Other') as label, count(*) as total")
                    ->groupBy('label')->orderByDesc('total')->limit(8)
                    ->pluck('total', 'label')->map(fn ($v) => (int) $v)->all(),
                // Plain arrays: the cache store does not unserialize model objects.
                'awaiting' => $scoped()->whereIn('status', ['submitted', 'under_review'])
                    ->with(['branch:id,code', 'customer.individual:id,customer_id,full_name', 'customer.organization:id,customer_id,business_name'])
                    ->latest('request_date')->orderByDesc('id')->limit(6)
                    ->get(['id', 'request_number', 'aof_form_type', 'branch_id', 'customer_id', 'request_date', 'status'])
                    ->map(fn (AccountOpeningRequest $r) => [
                        'url' => route('account-openings.show', $r),
                        'number' => $r->request_number,
                        'name' => $r->customer?->individual?->full_name ?? $r->customer?->organization?->business_name ?? 'Customer not entered',
                        'branch' => $r->branch?->code,
                        'form' => $r->aof_form_type === AccountOpeningRequest::FORM_ENTITY ? 'Entity' : 'Individual',
                        'status' => self::AOF_STATUSES[$r->status] ?? $r->status,
                        'date' => $r->request_date?->format('d.m.Y'),
                    ])->all(),
            ];
        });
    }

    /**
     * Whether a dashboard section is shown:
     *   - module view permission AND the section's dashboard permission
     *     (migration 2026_09_23_000004_seed_access_permissions), and
     *   - the user has something to see in it: an office (branch / region /
     *     ...) or bank-wide access. A user with the permission but no office
     *     posting gets no section rather than a section of zeros.
     */
    public function shows(User $user, string $section): bool
    {
        [$module, $dashboard] = self::SECTION_PERMISSIONS[$section];

        if (! $user->can($module) || ! $this->hasDashboardPermission($user, $dashboard)) {
            return false;
        }

        return match ($section) {
            'aksic' => OfficeAccess::for($user)['level'] !== OfficeAccess::NONE,
            'files' => self::isSuperAdmin($user) || FileManagementSystem::officeUnitOf($user) !== null,
            'account_openings' => $this->aofScope($user)[0] !== 'none',
            default => false,
        };
    }

    /**
     * The section's dashboard permission. Until migration 2026_09_23_000004_seed_access_permissions has
     * created it, it does not exist yet and the module permission alone
     * decides, so nobody loses the dashboard just because a migration is pending.
     */
    private function hasDashboardPermission(User $user, string $permission): bool
    {
        if (self::isSuperAdmin($user)) {
            return true;
        }

        return rescue(fn () => $user->hasPermissionTo($permission), fn () => true, false);
    }

    /**
     * Sections this user sees, in page order.
     *
     * @return array<int, string>
     */
    public function sectionsFor(User $user): array
    {
        return array_values(array_filter(
            array_keys(self::SECTION_PERMISSIONS),
            fn (string $section) => $this->shows($user, $section)
        ));
    }

    /**
     * The dashboard is shown only to users who have at least one module on it
     * (and the "view dashboard" permission); super admin always sees it.
     */
    public function isVisibleTo(?User $user): bool
    {
        if (! $user) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        return $user->can('view dashboard') && $this->sectionsFor($user) !== [];
    }

    /**
     * Where to send someone who cannot see the dashboard (e.g. after login):
     * the first module they can open, otherwise their profile.
     */
    public function landingUrl(User $user): string
    {
        foreach ([
            'view aksics' => 'aksic.index',
            'view account openings' => 'account-openings.index',
            'view file management systems' => 'file-management-systems.index',
        ] as $permission => $route) {
            if ($user->can($permission)) {
                return route($route);
            }
        }

        return route('profile.show');
    }

    public static function isSuperAdmin(User $user): bool
    {
        return $user->is_super_admin === 'Yes' || $user->hasRole('super-admin');
    }

    /**
     * Same rule as AccountOpeningRequest::visibleTo, for the label and cache key.
     *
     * @return array{0: string, 1: string}
     */
    private function aofScope(User $user): array
    {
        if ($user->is_super_admin === 'Yes' || $user->hasRole(['super-admin', 'head-office'])) {
            return ['all', 'All branches'];
        }
        if ($user->hasRole('branch') && $user->branch_id) {
            return ['branch', 'Branch: '.trim(($user->branch?->code ?? '').' - '.($user->branch?->name ?? ''), ' -')];
        }
        if ($user->hasRole('region') && $user->region_id) {
            return ['region', 'Region: '.($user->region?->name ?? '#'.$user->region_id)];
        }

        return ['none', 'No office set'];
    }

    /**
     * Last 12 months (oldest first), counts per month; with $approvedStatus also
     * the count in that status, so both series share one axis.
     *
     * @return array{labels: array<int, string>, total: array<int, int>, approved?: array<int, int>}
     */
    private function monthly(Builder $query, string $column, ?string $approvedStatus): array
    {
        $approved = $approvedStatus !== null;
        $from = now()->startOfMonth()->subMonths(self::MONTHS - 1);
        $month = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "date_format({$column}, '%Y-%m')";

        $rows = $query->toBase()->where($column, '>=', $from)
            ->selectRaw("{$month} as ym, count(*) as total".($approved ? ', sum(case when status = ? then 1 else 0 end) as approved' : ''), $approved ? [$approvedStatus] : [])
            ->groupBy('ym')->get()->keyBy('ym');

        $out = ['labels' => [], 'total' => []] + ($approved ? ['approved' => []] : []);
        for ($i = 0; $i < self::MONTHS; $i++) {
            $m = $from->copy()->addMonths($i);
            $row = $rows->get($m->format('Y-m'));
            $out['labels'][] = $m->format('M y');
            $out['total'][] = (int) ($row->total ?? 0);
            if ($approved) {
                $out['approved'][] = (int) ($row->approved ?? 0);
            }
        }

        return $out;
    }

    /**
     * Quota counts in a fixed order, so each quota keeps its chart colour for
     * every user (Special Person is counted with Disabled).
     *
     * @return array<string, int>
     */
    private function quotaMix(Builder $query): array
    {
        $counts = $query->toBase()->selectRaw('quota, count(*) as total')->groupBy('quota')->pluck('total', 'quota');
        $mix = ['Male' => 0, 'Female' => 0, 'Disabled' => 0, 'Transgender' => 0];

        foreach ($counts as $quota => $total) {
            $key = match ($quota) {
                'Special Person' => 'Disabled',
                'Male', 'Female', 'Disabled', 'Transgender' => $quota,
                default => 'Not set',
            };
            $mix[$key] = ($mix[$key] ?? 0) + (int) $total;
        }

        return $mix;
    }

    /**
     * Top 10 by district (bank-wide), by branch (region) or by business category (branch).
     *
     * @return array<int, array{label: string, approved: int, pending: int}>
     */
    private function aksicBreakdown(Builder $query, string $by): array
    {
        $base = $query->toBase();
        [$label, $group] = match ($by) {
            'district' => ["coalesce(districts.name, 'Not set')", 'districts.name'],
            'branch' => ["coalesce(branches.code, 'Not set')", 'branches.code'],
            default => ["coalesce(aksic_business_categories.name, 'Not set')", 'aksic_business_categories.name'],
        };

        if ($by === 'district') {
            $base->leftJoin('districts', 'districts.id', '=', 'aksics.district_id');
        }
        if ($by === 'branch') {
            $base->leftJoin('branches', 'branches.id', '=', 'aksics.branch_id');
        }
        if ($by === 'category') {
            $base->leftJoin('aksic_business_categories', 'aksic_business_categories.id', '=', 'aksics.business_category_id');
        }

        return $base->selectRaw("{$label} as label,
                sum(case when aksics.status = 'Approved' then 1 else 0 end) as approved,
                sum(case when aksics.status = 'Pending' then 1 else 0 end) as pending,
                count(*) as total")
            ->groupBy($group)->orderByDesc('total')->limit(10)->get()
            ->map(fn ($r) => ['label' => (string) $r->label, 'approved' => (int) $r->approved, 'pending' => (int) $r->pending])
            ->all();
    }

    /**
     * @return array{title: string, budget: float, used: float, remaining: float, percent: float, url: string}|null
     */
    private function budgetSummary(): ?array
    {
        $budget = AksicBudget::active();
        if (! $budget) {
            return null;
        }
        $t = $this->budgets->position($budget)['totals'];

        return [
            'title' => $budget->title,
            'budget' => (float) $t['allocated'],
            'used' => (float) $t['used'],
            'remaining' => (float) $t['remaining'],
            'percent' => $t['allocated'] > 0 ? round($t['used'] / $t['allocated'] * 100, 1) : 0.0,
            'url' => route('aksic-budgets.show', $budget),
        ];
    }

    /**
     * Same unit rule as FileManagementSystem::visibleTo: 'all' for super admin,
     * [morph type, id] for an office user, null when no unit is set.
     *
     * @return 'all'|array{0: string, 1: int}|null
     */
    private function orgUnit(User $user): string|array|null
    {
        if ($user->is_super_admin === 'Yes' || $user->hasRole('super-admin')) {
            return 'all';
        }

        return match (true) {
            $user->hasRole('branch') && $user->branch_id => [(new Branch)->getMorphClass(), (int) $user->branch_id],
            $user->hasRole('region') && $user->region_id => [(new Region)->getMorphClass(), (int) $user->region_id],
            $user->hasRole('division') && $user->division_id => [(new Division)->getMorphClass(), (int) $user->division_id],
            $user->hasRole('head-office') && $user->head_office_id => [(new HeadOffice)->getMorphClass(), (int) $user->head_office_id],
            default => null,
        };
    }

    private function unitLabel(User $user, string|array|null $unit): string
    {
        if ($unit === 'all') {
            return 'All offices';
        }
        if ($unit === null) {
            return 'No office set';
        }

        $model = match ($unit[0]) {
            (new Branch)->getMorphClass() => $user->branch,
            (new Region)->getMorphClass() => $user->region,
            (new Division)->getMorphClass() => $user->division,
            default => $user->headOffice,
        };

        return ucfirst(str_replace('-', ' ', $unit[0])).': '.trim(($model->code ?? '').' '.($model->name ?? ''));
    }

    /** Greeting for the page header (app time zone). */
    public static function greeting(): string
    {
        $hour = (int) Carbon::now()->format('G');

        return $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    }
}
