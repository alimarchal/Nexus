@php($ui = \App\Support\Ui::class)
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="name" class="{{ $ui::LABEL }}">Region name <span class="text-red-600">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $region->name ?? '') }}" required maxlength="255" autofocus
            data-review="Region name" class="{{ $ui::CONTROL }}" placeholder="e.g. Muzaffarabad">
        <p class="{{ $ui::HINT }}">Regional office name. Districts and branches are linked to a region.</p>
        @error('name') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
    <div class="rounded-md border border-gray-400 bg-gray-50 p-3 text-sm text-black dark:border-gray-600 dark:bg-gray-900/40 dark:text-gray-100">
        <div class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300">Linked records</div>
        @if (isset($region) && $region->exists)
            <dl class="mt-2 grid grid-cols-3 gap-2">
                <div><dt class="text-xs text-gray-700 dark:text-gray-300">Districts</dt><dd class="font-semibold">{{ $region->districts()->count() }}</dd></div>
                <div><dt class="text-xs text-gray-700 dark:text-gray-300">Branches</dt><dd class="font-semibold">{{ $region->branches()->count() }}</dd></div>
                <div><dt class="text-xs text-gray-700 dark:text-gray-300">Office users</dt><dd class="font-semibold">{{ $region->users()->count() }}</dd></div>
            </dl>
        @else
            <p class="mt-2 text-xs text-gray-700 dark:text-gray-300">After saving, add its districts under Districts, then link branches to them.</p>
        @endif
    </div>
</div>
