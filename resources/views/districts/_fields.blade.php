@php($ui = \App\Support\Ui::class)
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="region_id" class="{{ $ui::LABEL }}">Region <span class="text-red-600">*</span></label>
        <select id="region_id" name="region_id" required data-review="Region" class="{{ $ui::CONTROL }}">
            <option value="">Select region</option>
            @foreach ($regions as $region)
                <option value="{{ $region->id }}" @selected((string) old('region_id', $district->region_id ?? '') === (string) $region->id)>{{ $region->name }}</option>
            @endforeach
        </select>
        @error('region_id') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="name" class="{{ $ui::LABEL }}">District name <span class="text-red-600">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $district->name ?? '') }}" required maxlength="255"
            data-review="District name" class="{{ $ui::CONTROL }}" placeholder="e.g. Neelum">
        @error('name') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
</div>
