@php($ui = \App\Support\Ui::class)
<div>
    <label for="name" class="{{ $ui::LABEL }}">Region name <span class="text-red-600">*</span></label>
    <input id="name" type="text" name="name" value="{{ old('name', $region->name ?? '') }}" required maxlength="255" autofocus
        data-review="Region name" class="{{ $ui::CONTROL }}" placeholder="e.g. Muzaffarabad">
    <p class="{{ $ui::HINT }}">Regional office name. Districts and branches are linked to a region.</p>
    @error('name') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
</div>
