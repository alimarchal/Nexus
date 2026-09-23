@php($ui = \App\Support\Ui::class)
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="name" class="{{ $ui::LABEL }}">Permission name <span class="text-red-600">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $permission->name ?? '') }}" required maxlength="255" autofocus
            data-review="Permission" class="{{ $ui::CONTROL }}" placeholder="e.g. view aksic claims" autocomplete="off">
        <p class="{{ $ui::HINT }}">
            Use <b>action + module</b> in lower case, e.g. <code>approve aksic</code>. The module part groups it on the user and role screens.
        </p>
        @error('name') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
    <div class="rounded-md border border-gray-400 bg-gray-50 p-3 text-sm text-black dark:border-gray-600 dark:bg-gray-900/40 dark:text-gray-100">
        <div class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300">How it works</div>
        <ul class="mt-2 list-inside list-disc space-y-1 text-xs text-gray-800 dark:text-gray-200">
            <li>Guard is always <b>web</b>.</li>
            <li>Common actions: <code>view</code>, <code>create</code>, <code>edit</code>, <code>delete</code>, <code>approve</code>.</li>
            <li>A new permission does nothing until a route/controller checks it and it is given to a role or user.</li>
            @if (isset($permission) && $permission->exists)
                <li>Currently in <b>{{ $permission->roles()->count() }}</b> role(s).</li>
            @endif
        </ul>
    </div>
</div>
