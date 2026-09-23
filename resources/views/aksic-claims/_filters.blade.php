{{-- Shared District / Region / Branch / Gender selects. Expects $prefix ('' or 'filter'), $values (array). --}}
@php
    $name = fn (string $field) => $prefix ? "{$prefix}[{$field}]" : $field;
    $control = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $lbl = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
@endphp
<div>
    <label class="{{ $lbl }}" for="{{ $prefix }}district_id">District</label>
    <select id="{{ $prefix }}district_id" name="{{ $name('district_id') }}" class="{{ $control }}">
        <option value="">All districts</option>
        @foreach ($districts as $district)
            <option value="{{ $district->id }}" @selected((string) ($values['district_id'] ?? '') === (string) $district->id)>{{ $district->name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="{{ $lbl }}" for="{{ $prefix }}region_id">Region</label>
    <select id="{{ $prefix }}region_id" name="{{ $name('region_id') }}" class="{{ $control }}">
        <option value="">All regions</option>
        @foreach ($regions as $region)
            <option value="{{ $region->id }}" @selected((string) ($values['region_id'] ?? '') === (string) $region->id)>{{ $region->name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="{{ $lbl }}" for="{{ $prefix }}branch_id">Branch</label>
    <select id="{{ $prefix }}branch_id" name="{{ $name('branch_id') }}" class="{{ $control }}">
        <option value="">All branches</option>
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" @selected((string) ($values['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->code }} - {{ $branch->name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="{{ $lbl }}" for="{{ $prefix }}gender">Gender</label>
    <select id="{{ $prefix }}gender" name="{{ $name('gender') }}" class="{{ $control }}">
        <option value="">All genders</option>
        @foreach ($genders as $gender)
            <option value="{{ $gender }}" @selected(($values['gender'] ?? '') === $gender)>{{ $gender }}</option>
        @endforeach
    </select>
</div>
