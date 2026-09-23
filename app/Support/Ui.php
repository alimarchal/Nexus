<?php

namespace App\Support;

/**
 * Shared Tailwind class strings for the Nexus ERP screens, so every settings
 * page (users, roles, permissions, branches, regions, districts, managers)
 * uses the same card, form control and label styling.
 *
 * Only utilities that already exist in the compiled CSS are used here, so a
 * change in this file never needs `npm run build`.
 */
final class Ui
{
    /** Bordered card with a medium drop shadow. */
    public const CARD = 'overflow-hidden rounded-lg border border-gray-400 bg-white shadow-md dark:border-gray-600 dark:bg-gray-800';

    /** Grey header strip on top of a card. */
    public const CARD_HEAD = 'border-b border-gray-400 bg-gray-100 px-5 py-3 dark:border-gray-600 dark:bg-gray-900/40';

    public const CARD_TITLE = 'text-sm font-bold uppercase tracking-wide text-green-800 dark:text-green-400';

    public const CARD_SUBTITLE = 'text-xs text-gray-700 dark:text-gray-300';

    public const CONTROL = 'mt-1 block w-full rounded-md border-gray-400 text-black shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';

    public const LABEL = 'block text-sm font-semibold text-black dark:text-gray-100';

    public const HINT = 'mt-1 text-xs text-gray-700 dark:text-gray-300';

    public const ERROR = 'mt-1 text-sm text-red-600';

    /** Sticky save bar at the bottom of long forms. */
    public const ACTION_BAR = 'sticky bottom-0 z-10 mt-5 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-400 bg-white/95 px-5 py-3 shadow-lg backdrop-blur dark:border-gray-700 dark:bg-gray-800/95';

    public const BTN_PRIMARY = 'inline-flex items-center rounded-md bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-800';

    public const BTN_SECONDARY = 'inline-flex items-center rounded-md bg-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-800 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-200';

    public const PILL_GREEN = 'inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800';

    public const PILL_BLUE = 'inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800';

    public const PILL_GREY = 'inline-flex rounded-full bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-800';

    /** Icon button used in table "Actions" columns. */
    public const ICON_EDIT = 'inline-flex h-8 w-8 items-center justify-center rounded-md text-green-700 transition-colors duration-150 hover:bg-green-100 hover:text-green-900';

    public const ICON_DELETE = 'inline-flex h-8 w-8 items-center justify-center rounded-md text-red-600 transition-colors duration-150 hover:bg-red-100 hover:text-red-800';

    /** "view aksic claims" -> "Aksic Claims" (module name used to group permissions). */
    public static function permissionModule(string $name): string
    {
        $module = trim((string) \Illuminate\Support\Str::of($name)->after(' ')->title());

        return $module !== '' && $module !== \Illuminate\Support\Str::title($name) ? $module : 'General';
    }
}
