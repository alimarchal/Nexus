@php($ui = \App\Support\Ui::class)
<div>
    <label for="name" class="{{ $ui::LABEL }}">Permission name <span class="text-red-600">*</span></label>
    <input id="name" type="text" name="name" value="{{ old('name', $permission->name ?? '') }}" required maxlength="255" autofocus
        data-review="Permission" class="{{ $ui::CONTROL }}" placeholder="e.g. view aksic claims" autocomplete="off">
    <p class="{{ $ui::HINT }}">
        Use <b>action + module</b> in lower case: <code>view</code>, <code>create</code>, <code>edit</code>, <code>delete</code>,
        <code>approve</code> … followed by the module, e.g. <code>approve aksic</code>. The module part groups it on the user and role screens.
    </p>
    @error('name') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
</div>
<p class="mt-4 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-900">
    Guard is always <b>web</b>. A new permission does nothing until a route/controller checks it and it is given to a role or user.
</p>
