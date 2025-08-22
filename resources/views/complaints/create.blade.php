<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Create New Complaint
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('complaints.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-status-message />

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if ($errors->any())
                    <div class="alert alert-danger mb-4 p-4 rounded bg-red-100 text-red-700">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Complaint Details Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Complaint Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <label for="title" class="block text-gray-700">Complaint Title <span
                                        class="text-red-600">*</span>:</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    required>
                                @error('title')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-gray-700">Description <span
                                    class="text-red-600">*</span>:</label>
                            <textarea name="description" id="description" rows="4"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                required>{{ old('description') }}</textarea>
                            @error('description')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label for="priority" class="block text-gray-700">Priority <span
                                        class="text-red-600">*</span>:</label>
                                <select name="priority" id="priority"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    required>
                                    <option value="">Select Priority</option>
                                    @php
                                    // Central mapping (hours)
                                    $priorityHours = [
                                    'Critical' => 24,
                                    'High' => 72,
                                    'Medium' => 168,
                                    'Low' => 336,
                                    ];
                                    @endphp
                                    @foreach($priorityHours as $pKey => $hrs)
                                    @php
                                    $display = ($hrs % 24 === 0 && $hrs >= 24) ? ($hrs/24).'d' : $hrs.'h';
                                    @endphp
                                    <option value="{{ $pKey }}" data-sla-hours="{{ $hrs }}" {{ old('priority')==$pKey
                                        ? 'selected' : '' }}>
                                        {{ $pKey }} ({{ $display }} SLA)
                                    </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="source" class="block text-gray-700">Source <span
                                        class="text-red-600">*</span>:</label>
                                <select name="source" id="source"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    required>
                                    <option value="">Select Source</option>
                                    <option value="Phone" {{ old('source')=='Phone' ? 'selected' : '' }}>Phone</option>
                                    <option value="Email" {{ old('source')=='Email' ? 'selected' : '' }}>Email</option>
                                    <option value="Portal" {{ old('source')=='Portal' ? 'selected' : '' }}>Portal
                                    </option>
                                    <option value="Walk-in" {{ old('source')=='Walk-in' ? 'selected' : '' }}>Walk-in
                                    </option>
                                    <option value="Other" {{ old('source')=='Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('source')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="category_id" class="block text-gray-700">Category <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <select name="category_id" id="category_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        data-default-priority="{{ $category->default_priority ?? '' }}" {{
                                        old('category_id')==$category->id ? 'selected' : '' }}>
                                        {{ $category->category_name
                                        }}@if(strtolower($category->category_name)==='harassment') (Escalates to senior
                                        management – provide evidence & factual details)@endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <!-- Harassment supplemental fields (hidden unless Harassment selected) -->
                            <div id="harassment-section" class="md:col-span-4 hidden border rounded p-4 bg-red-50">
                                <h4 class="font-semibold text-red-700 mb-2">Harassment Details (Required)</h4>
                                <p class="text-xs text-red-700 mb-4">This category escalates automatically to senior
                                    management. Provide factual, objective details and attach any supporting evidence
                                    (screenshots, emails, logs). Sensitive handling is enforced.</p>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <label for="harassment_sub_category" class="block text-gray-700">Sub Category
                                            <span class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                        <input type="text" name="harassment_sub_category" id="harassment_sub_category"
                                            value="{{ old('harassment_sub_category') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                            placeholder="e.g. Verbal, Physical, Online">
                                        @error('harassment_sub_category')<span class="text-red-500 text-sm">{{ $message
                                            }}</span>@enderror
                                    </div>
                                    <div>
                                        <label for="harassment_incident_date" class="block text-gray-700">Incident Date
                                            & Time <span class="text-red-600">*</span>:</label>
                                        <input type="datetime-local" name="harassment_incident_date"
                                            id="harassment_incident_date" value="{{ old('harassment_incident_date') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        @error('harassment_incident_date')<span class="text-red-500 text-sm">{{ $message
                                            }}</span>@enderror
                                    </div>
                                    <div>
                                        <label for="harassment_location" class="block text-gray-700">Location <span
                                                class="text-red-600">*</span>:</label>
                                        <input type="text" name="harassment_location" id="harassment_location"
                                            value="{{ old('harassment_location') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        @error('harassment_location')<span class="text-red-500 text-sm">{{ $message
                                            }}</span>@enderror
                                    </div>
                                    <div>
                                        <label for="harassment_employee_number" class="block text-gray-700">Employee No
                                            (Victim) <span
                                                class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                        <input type="text" name="harassment_employee_number"
                                            id="harassment_employee_number"
                                            value="{{ old('harassment_employee_number') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        @error('harassment_employee_number')<span class="text-red-500 text-sm">{{
                                            $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div class="md:col-span-3">
                                        <label for="harassment_details" class="block text-gray-700">Incident Details /
                                            Evidence Summary <span class="text-red-600">*</span>:</label>
                                        <textarea name="harassment_details" id="harassment_details" rows="5"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('harassment_details') }}</textarea>
                                        @error('harassment_details')<span class="text-red-500 text-sm">{{ $message
                                            }}</span>@enderror
                                    </div>
                                    <div>
                                        <label for="harassment_employee_phone" class="block text-gray-700">Victim Phone
                                            <span class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                        <input type="text" name="harassment_employee_phone"
                                            id="harassment_employee_phone"
                                            value="{{ old('harassment_employee_phone') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 mb-4">
                                        @error('harassment_employee_phone')<span class="text-red-500 text-sm">{{
                                            $message }}</span>@enderror
                                        <label for="harassment_reported_to" class="block text-gray-700">Reported To
                                            <span class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                        <input type="text" name="harassment_reported_to" id="harassment_reported_to"
                                            value="{{ old('harassment_reported_to') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        @error('harassment_reported_to')<span class="text-red-500 text-sm">{{ $message
                                            }}</span>@enderror
                                        <label class="flex items-center mt-4">
                                            <input type="checkbox" name="harassment_confidential" value="1" {{
                                                old('harassment_confidential') ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200">
                                            <span class="ml-2 text-gray-700 text-sm">Mark as Confidential</span>
                                        </label>
                                        @error('harassment_confidential')<span class="text-red-500 text-sm">{{ $message
                                            }}</span>@enderror
                                    </div>
                                </div>
                                <!-- Dynamic Witnesses -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h5 class="font-semibold text-gray-700">Witnesses <span
                                                class="text-gray-500 text-xs font-normal">(Up to 10)</span></h5>
                                        <button type="button" id="add-witness-btn"
                                            class="px-3 py-1 bg-indigo-600 text-white text-xs rounded">Add
                                            Witness</button>
                                    </div>
                                    <div id="witnesses-wrapper" class="space-y-4">
                                        <!-- Witness templates inserted here -->
                                    </div>
                                    <template id="witness-template">
                                        <div class="witness-item border rounded p-3 bg-white relative">
                                            <button type="button"
                                                class="remove-witness absolute top-1 right-1 text-red-500 text-xs">✕</button>
                                            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                                                <div>
                                                    <label class="block text-gray-700 text-xs">Employee No
                                                        (Optional)</label>
                                                    <input type="text" data-field="employee_number"
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                                </div>
                                                <div>
                                                    <label class="block text-gray-700 text-xs">Name <span
                                                            class="text-red-600">*</span></label>
                                                    <input type="text" data-field="name" required
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                                </div>
                                                <div>
                                                    <label class="block text-gray-700 text-xs">Phone (Optional)</label>
                                                    <input type="text" data-field="phone"
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                                </div>
                                                <div>
                                                    <label class="block text-gray-700 text-xs">Email (Optional)</label>
                                                    <input type="email" data-field="email"
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                                </div>
                                                <div>
                                                    <label class="block text-gray-700 text-xs">Statement
                                                        (Optional)</label>
                                                    <textarea rows="1" data-field="statement"
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <label for="expected_resolution_date" class="block text-gray-700">Expected Resolution
                                    Date <span class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <input type="datetime-local" name="expected_resolution_date"
                                    id="expected_resolution_date" value="{{ old('expected_resolution_date') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <small class="text-gray-500">Auto-filled from priority. Change priority to recalc; you
                                    can also manually adjust.</small>
                                @error('expected_resolution_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <!-- Harassment supplemental fields (hidden unless Harassment selected) moved below grid to avoid layout shift -->
                        <div id="harassment-section" class="hidden border rounded p-4 bg-red-50 mb-6">
                            <h4 class="font-semibold text-red-700 mb-2">Harassment Details (Required)</h4>
                            <p class="text-xs text-red-700 mb-4">This category escalates automatically to senior
                                management. Provide factual, objective details and attach any supporting evidence
                                (screenshots, emails, logs). Sensitive handling is enforced.</p>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <label for="harassment_sub_category" class="block text-gray-700">Sub Category <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <input type="text" name="harassment_sub_category" id="harassment_sub_category"
                                        value="{{ old('harassment_sub_category') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        placeholder="e.g. Verbal, Physical, Online">
                                    @error('harassment_sub_category')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="harassment_incident_date" class="block text-gray-700">Incident Date &
                                        Time <span class="text-red-600">*</span>:</label>
                                    <input type="datetime-local" name="harassment_incident_date"
                                        id="harassment_incident_date" value="{{ old('harassment_incident_date') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @error('harassment_incident_date')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="harassment_location" class="block text-gray-700">Location <span
                                            class="text-red-600">*</span>:</label>
                                    <input type="text" name="harassment_location" id="harassment_location"
                                        value="{{ old('harassment_location') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @error('harassment_location')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="harassment_employee_number" class="block text-gray-700">Employee No
                                        (Victim) <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <input type="text" name="harassment_employee_number" id="harassment_employee_number"
                                        value="{{ old('harassment_employee_number') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @error('harassment_employee_number')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <div class="md:col-span-3">
                                    <label for="harassment_details" class="block text-gray-700">Incident Details /
                                        Evidence Summary <span class="text-red-600">*</span>:</label>
                                    <textarea name="harassment_details" id="harassment_details" rows="5"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('harassment_details') }}</textarea>
                                    @error('harassment_details')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="harassment_employee_phone" class="block text-gray-700">Victim Phone
                                        <span class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <input type="text" name="harassment_employee_phone" id="harassment_employee_phone"
                                        value="{{ old('harassment_employee_phone') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 mb-4">
                                    @error('harassment_employee_phone')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                    <label for="harassment_reported_to" class="block text-gray-700">Reported To <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <input type="text" name="harassment_reported_to" id="harassment_reported_to"
                                        value="{{ old('harassment_reported_to') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @error('harassment_reported_to')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                    <label class="flex items-center mt-4">
                                        <input type="checkbox" name="harassment_confidential" value="1" {{
                                            old('harassment_confidential') ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200">
                                        <span class="ml-2 text-gray-700 text-sm">Mark as Confidential</span>
                                    </label>
                                    @error('harassment_confidential')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                            </div>
                            <!-- Dynamic Witnesses -->
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="font-semibold text-gray-700">Witnesses <span
                                            class="text-gray-500 text-xs font-normal">(Up to 10)</span></h5>
                                    <button type="button" id="add-witness-btn"
                                        class="px-3 py-1 bg-indigo-600 text-white text-xs rounded">Add Witness</button>
                                </div>
                                <div id="witnesses-wrapper" class="space-y-4"></div>
                                <template id="witness-template">
                                    <div class="witness-item border rounded p-3 bg-white relative">
                                        <button type="button"
                                            class="remove-witness absolute top-1 right-1 text-red-500 text-xs">✕</button>
                                        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                                            <div>
                                                <label class="block text-gray-700 text-xs">Employee No
                                                    (Optional)</label>
                                                <input type="text" data-field="employee_number"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 text-xs">Name <span
                                                        class="text-red-600">*</span></label>
                                                <input type="text" data-field="name" required
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 text-xs">Phone (Optional)</label>
                                                <input type="text" data-field="phone"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 text-xs">Email (Optional)</label>
                                                <input type="email" data-field="email"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 text-xs">Statement (Optional)</label>
                                                <textarea rows="1" data-field="statement"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="branch_id" class="block text-gray-700">Branch <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <select name="branch_id" id="branch_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">Not applicable</option>
                                    @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id')==$branch->id ? 'selected' : ''
                                        }}>
                                        {{ $branch->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="region_id" class="block text-gray-700">Region <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <select name="region_id" id="region_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">Not applicable</option>
                                    @foreach ($regions as $region)
                                    <option value="{{ $region->id }}" {{ old('region_id')==$region->id ? 'selected' : ''
                                        }}>
                                        {{ $region->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('region_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="division_id" class="block text-gray-700">Division <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <select name="division_id" id="division_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">Not applicable</option>
                                    @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" {{ old('division_id')==$division->id ?
                                        'selected' : '' }}>
                                        {{ $division->short_name ?? $division->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('division_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <!-- Complainant Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Complainant Information
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="complainant_name" class="block text-gray-700">Complainant Name <span
                                            class="text-red-600">*</span>:</label>
                                    <input type="text" name="complainant_name" id="complainant_name"
                                        value="{{ old('complainant_name', auth()->user()->name ?? '') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        required>
                                    @error('complainant_name')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label for="complainant_email" class="block text-gray-700">Email <span
                                            class="text-red-600">*</span>:</label>
                                    <input type="email" name="complainant_email" id="complainant_email"
                                        value="{{ old('complainant_email', auth()->user()->email ?? '') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        required>
                                    @error('complainant_email')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label for="complainant_phone" class="block text-gray-700">Phone <span
                                            class="text-red-600">*</span>:</label>
                                    <input type="tel" name="complainant_phone" id="complainant_phone"
                                        value="{{ old('complainant_phone') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        required>
                                    @error('complainant_phone')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label for="complainant_account_number" class="block text-gray-700">Account
                                        Number <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <input type="text" name="complainant_account_number" id="complainant_account_number"
                                        value="{{ old('complainant_account_number') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @error('complainant_account_number')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Assignment and Files Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Assignment & Files</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="assigned_to" class="block text-gray-700">Assign To <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <select name="assigned_to" id="assigned_to"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        <option value="">Select User</option>
                                        @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to')==$user->id ? 'selected' :
                                            ''
                                            }}>
                                            {{ $user->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>


                            </div>

                            <div class="mb-4">
                                <label for="watchers" class="block text-gray-700">Watchers <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <select name="watchers[]" id="watchers" multiple size="5"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, old('watchers', [])) ?
                                        'selected'
                                        : '' }}>
                                        {{ $user->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-gray-600">Hold Ctrl/Cmd to select multiple users</small>
                                @error('watchers')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="attachments" class="block text-gray-700">File Attachments <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <input type="file" name="attachments[]" id="attachments" multiple
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt,.zip,.rar"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <small class="text-gray-600">Allowed: PDF, DOC, DOCX, JPG, PNG, TXT, ZIP (max 10MB each,
                                    max
                                    10 files)</small>
                                @error('attachments')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                                @error('attachments.*')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="comments" class="block text-gray-700">Initial Comment <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <textarea name="comments" id="comments" rows="3"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('comments') }}</textarea>
                                @error('comments')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="comment_type" class="block text-gray-700">Comment Type <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <select name="comment_type" id="comment_type"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        <option value="Internal" {{ old('comment_type')=='Internal' ? 'selected' : ''
                                            }}>
                                            Internal</option>
                                        <option value="Customer" {{ old('comment_type')=='Customer' ? 'selected' : ''
                                            }}>
                                            Customer</option>
                                        <option value="System" {{ old('comment_type')=='System' ? 'selected' : '' }}>
                                            System
                                        </option>
                                    </select>
                                    @error('comment_type')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="flex items-center">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_private" value="1" {{ old('is_private')
                                            ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200">
                                        <span class="ml-2 text-gray-700">Private Comment</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-blue-800 text-white rounded-md">Create
                                Complaint</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            const priorityField = document.getElementById('priority');
            const expectedField = document.getElementById('expected_resolution_date');
            const categoryField = document.getElementById('category_id');
            const harassmentSection = document.getElementById('harassment-section');
            const harassmentRequiredFields = ['harassment_incident_date','harassment_location','harassment_details'];
            const witnessWrapper = document.getElementById('witnesses-wrapper');
            const witnessTemplate = document.getElementById('witness-template');
            const addWitnessBtn = document.getElementById('add-witness-btn');
            let witnessCount = 0;

            function addWitness(data={}){
                if(!witnessWrapper || !witnessTemplate) return;
                if(witnessCount >= 10) return;
                const clone = witnessTemplate.content.cloneNode(true);
                const container = clone.querySelector('.witness-item');
                container.querySelectorAll('[data-field]').forEach(el => {
                    const field = el.getAttribute('data-field');
                    const name = `witnesses[${witnessCount}][${field}]`;
                    el.name = name;
                    if(data[field]) el.value = data[field];
                });
                container.querySelector('.remove-witness').addEventListener('click', () => {
                    container.remove();
                });
                witnessWrapper.appendChild(clone);
                witnessCount++;
            }

            if(addWitnessBtn){
                addWitnessBtn.addEventListener('click', () => addWitness());
            }

            // Rehydrate old witness inputs if validation failed
            @if(is_array(old('witnesses')))
                @foreach(old('witnesses') as $idx => $w)
                    addWitness(@json($w));
                @endforeach
            @endif

            function toggleHarassmentSection(){
                if(!categoryField) return;
                const opt = categoryField.options[categoryField.selectedIndex];
                const name = opt ? (opt.text || '').toLowerCase() : '';
                const isHarassment = name.startsWith('harassment');
                if(isHarassment){
                    harassmentSection.classList.remove('hidden');
                    harassmentRequiredFields.forEach(id=>{ const el=document.getElementById(id); if(el){ el.setAttribute('required','required'); }});
                } else {
                    harassmentSection.classList.add('hidden');
                    harassmentRequiredFields.forEach(id=>{ const el=document.getElementById(id); if(el){ el.removeAttribute('required'); }});
                }
            }

            // Utility: format a future datetime-local value from hours offset
            function futureDateTimeLocal(hoursAhead){
                const base = new Date();
                base.setHours(base.getHours() + hoursAhead);
                // Adjust for local timezone for datetime-local input (expects local time w/o TZ)
                const local = new Date(base.getTime() - base.getTimezoneOffset()*60000);
                return local.toISOString().slice(0,16);
            }

            function applyExpectedFromHours(hours, force=false){
                if(!expectedField) return;
                if(!hours || isNaN(hours)) return;
                const canOverwrite = force || !expectedField.value || expectedField.dataset.autofill === 'true';
                if(!canOverwrite) return;
                expectedField.value = futureDateTimeLocal(Number(hours));
                expectedField.dataset.autofill = 'true';
            }

            function recalcFromPriority(force=false){
                if(!priorityField) return;
                const opt = priorityField.selectedIndex >= 0 ? priorityField.options[priorityField.selectedIndex] : null;
                if(!opt) return;
                const hrs = opt.getAttribute('data-sla-hours');
                applyExpectedFromHours(Number(hrs), force);
            }

            function maybeAdoptDefaultPriority(){
                if(!categoryField) return;
                const opt = categoryField.selectedIndex >= 0 ? categoryField.options[categoryField.selectedIndex] : null;
                if(!opt) return;
                const defPri = opt.getAttribute('data-default-priority');
                if(!defPri || !priorityField) return;
                if(!priorityField.dataset.userSet && !priorityField.value){
                    priorityField.value = defPri;
                }
            }

            // Event wiring
            if(priorityField){
                priorityField.addEventListener('change', () => {
                    priorityField.dataset.userSet = 'true';
                    // Recalculate whenever user explicitly changes priority
                    expectedField && (expectedField.dataset.autofill = 'true');
                    recalcFromPriority(true);
                });
            }

            if(categoryField){
                categoryField.addEventListener('change', () => {
                    maybeAdoptDefaultPriority();
                    // Do NOT auto-change expected date here; only priority drives it now.
                    toggleHarassmentSection();
                });
            }

            if(expectedField){
                // Mark as manual if user edits value
                expectedField.addEventListener('input', () => { expectedField.dataset.autofill = 'false'; });
            }

            // Initialisation
            if(categoryField && categoryField.value){
                maybeAdoptDefaultPriority();
            }
            toggleHarassmentSection();
            if(priorityField && priorityField.value){
                recalcFromPriority(false); // initial fill if empty / allowed
            }
        })();
    </script>
    @endpush
</x-app-layout>