@php
    $ui = \App\Support\Ui::class;
    $districtOptions = $districts->map(fn ($d) => ['id' => (string) $d->id, 'name' => $d->name, 'region' => (string) $d->region_id])->values();
@endphp
<div class="grid grid-cols-1 gap-4 md:grid-cols-2"
    x-data="{
        region: @js((string) old('region_id', $branch->region_id ?? '')),
        district: @js((string) old('district_id', $branch->district_id ?? '')),
        districts: @js($districtOptions),
        get list() { return this.districts.filter(d => !this.region || d.region === this.region); },
        regionChanged() { if (!this.list.some(d => d.id === this.district)) this.district = ''; }
    }">
    <div>
        <label for="code" class="{{ $ui::LABEL }}">Branch code <span class="text-red-600">*</span></label>
        <input id="code" type="text" name="code" value="{{ old('code', $branch->code ?? '') }}" required maxlength="20" autofocus
            data-review="Code" class="{{ $ui::CONTROL }}" placeholder="e.g. 0087">
        <p class="{{ $ui::HINT }}">Unique code used in the core banking system.</p>
        @error('code') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="name" class="{{ $ui::LABEL }}">Branch name <span class="text-red-600">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $branch->name ?? '') }}" required maxlength="255"
            data-review="Name" class="{{ $ui::CONTROL }}" placeholder="e.g. Chechian Branch">
        @error('name') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="region_id" class="{{ $ui::LABEL }}">Region <span class="text-red-600">*</span></label>
        <select id="region_id" name="region_id" required x-model="region" @change="regionChanged()" data-review="Region" class="{{ $ui::CONTROL }}">
            <option value="">Select region</option>
            @foreach ($regions as $region)
                <option value="{{ $region->id }}" @selected((string) old('region_id', $branch->region_id ?? '') === (string) $region->id)>{{ $region->name }}</option>
            @endforeach
        </select>
        @error('region_id') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="district_id" class="{{ $ui::LABEL }}">District <span class="text-red-600">*</span></label>
        <select id="district_id" name="district_id" required x-model="district" data-review="District" class="{{ $ui::CONTROL }}">
            <option value="">Select district</option>
            <template x-for="d in list" :key="d.id">
                <option :value="d.id" x-text="d.name" :selected="d.id === district"></option>
            </template>
        </select>
        <p class="{{ $ui::HINT }}">Only districts of the selected region are listed.</p>
        @error('district_id') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="address" class="{{ $ui::LABEL }}">Address <span class="text-red-600">*</span></label>
        <input id="address" type="text" name="address" value="{{ old('address', $branch->address ?? '') }}" required maxlength="255"
            data-review="Address" class="{{ $ui::CONTROL }}">
        @error('address') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
</div>
