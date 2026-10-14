<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Arranges the flat permission list ("view file management systems",
 * "approve account openings" ...) into product modules so an administrator can
 * pick a module and see only that module's permissions.
 *
 * A permission name is "<action> <subject>"; the subject decides the module.
 * Subjects that are not mapped yet land in "Other" so nothing is ever hidden.
 */
final class PermissionCatalog
{
    /**
     * Ordered module definitions: key => label, hint and the subject prefixes
     * that belong to it. The first matching prefix wins, so more specific
     * prefixes (e.g. "account opening") must not be shadowed by shorter ones.
     *
     * @var array<string, array{label: string, hint: string, subjects: array<int, string>}>
     */
    public const MODULES = [
        'access' => [
            'label' => 'Users & access',
            'hint' => 'Who can sign in, their roles and what they can open.',
            'subjects' => ['users', 'roles', 'permissions', 'managers'],
        ],
        'account-opening' => [
            'label' => 'Account opening (AOF)',
            'hint' => 'Account opening requests, checking and reference data.',
            'subjects' => ['account opening'],
        ],
        'aksic' => [
            'label' => 'AKSIC',
            'hint' => 'AKSIC cases, claims, rules and the markup budget.',
            'subjects' => ['aksic', 'all aksic'],
        ],
        'file-management' => [
            'label' => 'File management',
            'hint' => 'Scanned records, transfers, archive boxes and file categories.',
            'subjects' => ['file management', 'file categories', 'boxes'],
        ],
        'complaints' => [
            'label' => 'Complaints',
            'hint' => 'Complaint handling, assignment and escalation.',
            'subjects' => ['complaints'],
        ],
        'organisation' => [
            'label' => 'Organisation',
            'hint' => 'Branches, regions, divisions and categories.',
            'subjects' => ['branches', 'regions', 'divisions', 'categories'],
        ],
        'general' => [
            'label' => 'Dashboard, reports & settings',
            'hint' => 'Dashboard, analytics, reports and system settings.',
            'subjects' => ['dashboard', 'analytics', 'reports', 'settings'],
        ],
    ];

    public const OTHER = 'other';

    /** The part of the name after the first word: "view file categories" -> "file categories". */
    public static function subject(string $name): string
    {
        return trim((string) Str::of($name)->after(' '));
    }

    /** The module key a permission belongs to ("other" when unmapped). */
    public static function moduleKey(string $name): string
    {
        $subject = self::subject($name);

        foreach (self::MODULES as $key => $module) {
            foreach ($module['subjects'] as $prefix) {
                if ($subject === $prefix || str_starts_with($subject, $prefix.' ') || $subject === Str::plural($prefix)) {
                    return $key;
                }
            }
        }

        return self::OTHER;
    }

    /**
     * Group permissions by module, then by subject ("Aksic Claims").
     *
     * @param  Collection<int, Permission>  $permissions
     * @return array<int, array{key: string, label: string, hint: string, count: int, groups: array<int, array{label: string, permissions: Collection<int, Permission>}>}>
     */
    public static function build(Collection $permissions): array
    {
        $byModule = $permissions->sortBy('name')->groupBy(fn ($permission) => self::moduleKey($permission->name));

        $definitions = self::MODULES + [self::OTHER => ['label' => 'Other', 'hint' => 'Permissions not placed in a module yet.', 'subjects' => []]];

        $modules = [];
        foreach ($definitions as $key => $definition) {
            $items = $byModule->get($key);
            if (! $items || $items->isEmpty()) {
                continue;
            }

            $modules[] = [
                'key' => $key,
                'label' => $definition['label'],
                'hint' => $definition['hint'],
                'count' => $items->count(),
                'groups' => $items
                    ->groupBy(fn ($permission) => Ui::permissionModule($permission->name))
                    ->sortKeys()
                    ->map(fn ($group, $label) => ['label' => $label, 'permissions' => $group->values()])
                    ->values()
                    ->all(),
            ];
        }

        return $modules;
    }
}
