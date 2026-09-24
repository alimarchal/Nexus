<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Which branches' data a user may see, following the bank's organogram.
 *
 *   "view all aksic cases" permission, President Office, super admin -> every branch
 *   Region  -> branches of the user's region
 *   Branch  -> the user's own branch only
 *   anything else (no permission and no branch / region posting) -> nothing
 *
 * When a user has several roles the widest one wins. Use it on any model that
 * carries a branch_id:  OfficeAccess::scope($query, $user)
 */
final class OfficeAccess
{
    public const ALL = 'all';

    public const REGION = 'region';

    public const BRANCH = 'branch';

    public const NONE = 'none';

    /** Permission that opens every branch's AKSIC cases (migration 2026_09_24_000001). */
    public const VIEW_ALL_PERMISSION = 'view all aksic cases';

    /** Per request user object (a WeakMap, so no stale entries across users or tests). */
    private static ?\WeakMap $cache = null;

    /**
     * @return array{level: string, branch_ids: array<int, int>|null, label: string}
     */
    public static function for(?User $user): array
    {
        if (! $user) {
            return ['level' => self::NONE, 'branch_ids' => [], 'label' => 'No access'];
        }

        self::$cache ??= new \WeakMap;

        return self::$cache[$user] ??= self::resolve($user);
    }

    /** null = every branch. */
    public static function branchIds(?User $user): ?array
    {
        return self::for($user)['branch_ids'];
    }

    public static function seesAll(?User $user): bool
    {
        return self::for($user)['level'] === self::ALL;
    }

    public static function allowsBranch(?User $user, mixed $branchId): bool
    {
        $ids = self::branchIds($user);

        return $ids === null || ($branchId !== null && in_array((int) $branchId, $ids, true));
    }

    /**
     * Limit a query to the branches the user may see.
     */
    public static function scope(Builder|QueryBuilder $query, ?User $user, string $column = 'branch_id'): Builder|QueryBuilder
    {
        $ids = self::branchIds($user);

        if ($ids === null) {
            return $query;
        }

        return $ids === [] ? $query->whereRaw('1 = 0') : $query->whereIn($column, $ids);
    }

    /**
     * @return array{level: string, branch_ids: array<int, int>|null, label: string}
     */
    private static function resolve(User $user): array
    {
        $bankWide = $user->is_super_admin === 'Yes'
            || (bool) $user->is_president_office
            || $user->hasAnyRole(['super-admin', 'president-office'])
            || rescue(fn () => $user->hasPermissionTo(self::VIEW_ALL_PERMISSION), false, false);

        if ($bankWide) {
            return ['level' => self::ALL, 'branch_ids' => null, 'label' => 'All branches'];
        }

        // Region office: region role, or a region set on a user without a branch role.
        if ($user->hasRole('region') || ($user->region_id && ! $user->hasRole('branch'))) {
            if (! $user->region_id) {
                return ['level' => self::NONE, 'branch_ids' => [], 'label' => 'Region not set on this user'];
            }
            $ids = Branch::query()->where('region_id', $user->region_id)->pluck('id')->map(fn ($id) => (int) $id)->all();

            return ['level' => self::REGION, 'branch_ids' => $ids, 'label' => 'Region: '.($user->region?->name ?? '#'.$user->region_id)];
        }

        // Branch: branch role, or any user posted at a branch.
        if ($user->hasRole('branch') || $user->branch_id) {
            if (! $user->branch_id) {
                return ['level' => self::NONE, 'branch_ids' => [], 'label' => 'Branch not set on this user'];
            }
            $branch = $user->branch;

            return ['level' => self::BRANCH, 'branch_ids' => [(int) $user->branch_id], 'label' => 'Branch: '.($branch ? $branch->code.' - '.$branch->name : '#'.$user->branch_id)];
        }

        // No branch / region posting and no "view all aksic cases" permission.
        return ['level' => self::NONE, 'branch_ids' => [], 'label' => 'No office set (needs the "view all aksic cases" permission)'];
    }
}
