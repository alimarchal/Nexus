@php($ui = \App\Support\Ui::class)
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="division_id" class="{{ $ui::LABEL }}">Division <span class="text-red-600">*</span></label>
        <select id="division_id" name="division_id" required data-review="Division" class="{{ $ui::CONTROL }}">
            <option value="">Select division</option>
            @foreach ($divisions->sortBy('name') as $division)
                <option value="{{ $division->id }}" @selected((string) old('division_id', $manager->division_id ?? '') === (string) $division->id)>
                    {{ $division->name }}{{ $division->short_name ? ' ('.$division->short_name.')' : '' }}
                </option>
            @endforeach
        </select>
        @error('division_id') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="manager_user_id" class="{{ $ui::LABEL }}">Manager (user) <span class="text-red-600">*</span></label>
        <select id="manager_user_id" name="manager_user_id" required data-review="Manager" class="{{ $ui::CONTROL }}">
            <option value="">Select user</option>
            @foreach ($users->sortBy('name') as $user)
                <option value="{{ $user->id }}" @selected((string) old('manager_user_id', $manager->manager_user_id ?? '') === (string) $user->id)>
                    {{ $user->name }} — {{ $user->email }}
                </option>
            @endforeach
        </select>
        <p class="{{ $ui::HINT }}">The person who heads / approves for this division.</p>
        @error('manager_user_id') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="title" class="{{ $ui::LABEL }}">Title (designation)</label>
        <input id="title" type="text" name="title" value="{{ old('title', $manager->title ?? '') }}" maxlength="255"
            data-review="Title" class="{{ $ui::CONTROL }}" placeholder="e.g. Divisional Head IT">
        @error('title') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
    </div>
</div>
