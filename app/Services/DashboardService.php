<?php

namespace App\Services;

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
 *
 * Everything is aggregated in SQL (no row loading) and cached for a minute per
 * visibility scope; AKSIC figures refresh at once when a case changes.
 */
class DashboardService
{
    public const MONTHS = 12;

    public function __construct(private readonly AksicBudgetService $budgets) {}

    /**
     * @return array<string, mixed>|null null when the user may not view AKSIC cases
     */
    public function aksic(User $user): ?array
    {
        if (! $user->can('view aksics')) {
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
                'monthly' => $this->monthly($scoped(), 'created_at', true),
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
        if (! $user->can('view file management systems')) {
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
                'monthly' => $this->monthly($scoped(), 'created_at', false),
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
     * Last 12 months (oldest first), counts per month; with $approved also the
     * approved count, so both series share one axis.
     *
     * @return array{labels: array<int, string>, total: array<int, int>, approved?: array<int, int>}
     */
    private function monthly(Builder $query, string $column, bool $approved): array
    {
        $from = now()->startOfMonth()->subMonths(self::MONTHS - 1);
        $month = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "date_format({$column}, '%Y-%m')";

        $rows = $query->toBase()->where($column, '>=', $from)
            ->selectRaw("{$month} as ym, count(*) as total".($approved ? ", sum(case when status = 'Approved' then 1 else 0 end) as approved" : ''))
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
