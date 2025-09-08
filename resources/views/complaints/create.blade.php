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
                            <div>
                               <label for="expected_resolution_date" class="block text-gray-700">Expected Resolution
                                    Date <span class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <input type="datetime-local" name="expected_resolution_date"
                                    id="expected_resolution_date" value="{{ old('expected_resolution_date') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
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
                            <!-- Alleged Abuser Details (optional) placed at top inside harassment section -->
                            <div class="mb-6 border rounded p-3 bg-white/60">
                                <h5 class="font-semibold text-gray-700 mb-3 text-sm">Alleged Abuser Details <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span></h5>
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                    <div>
                                        <label for="harassment_abuser_employee_number" class="block text-gray-700 text-xs">Employee No (Optional)</label>
                                        <input type="text" name="harassment_abuser_employee_number" id="harassment_abuser_employee_number"
                                            value="{{ old('harassment_abuser_employee_number') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"/>
                                        @error('harassment_abuser_employee_number')<span class="text-red-500 text-xs">{{
                                            $message }}</span>@enderror
                                    </div>
                                    <div>
                                        <div>
            <label for="harassment_abuser_name" class="block text-gray-700 text-xs">
             Name <span class="text-red-600">*</span>
                        </label>
                    <input type="text" 
                     name="harassment_abuser_name" 
                       id="harassment_abuser_name"
                      value="{{ old('harassment_abuser_name') }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                    required>
                    @error('harassment_abuser_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                   @enderror
                  </div>

                                    </div>
                                    <div>
                                            <label for="harassment_abuser_phone" class="block text-gray-700 text-xs ">
                                           Phone <span class="text-red-600">*</span>
                                               </label>
                                              <input type="text" 
                                               name="harassment_abuser_phone" 
                                                 id="harassment_abuser_phone"
                                                  value="{{ old('harassment_abuser_phone') }}"
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                                      required>
                                                      @error('harassment_abuser_phone')
                                                   <span class="text-red-500 text-sm">{{ $message }}</span>
                                                          @enderror
                                    </div>
                                    <div>
                                        <label for="harassment_abuser_email" class="block text-gray-700 text-xs">Email (Optional)</label>
                                        <input type="email" name="harassment_abuser_email" id="harassment_abuser_email"
                                            value="{{ old('harassment_abuser_email') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                        @error('harassment_abuser_email')<span class="text-red-500 text-xs">{{ $message
                                            }}</span>@enderror
                                    </div>
                                    <div>
                                        <label for="harassment_abuser_relationship" class="block text-gray-700 text-xs">
                             Relationship <span class="text-red-600">*</span>:
                           </label>
                            <input type="text" 
                             name="harassment_abuser_relationship" 
                            id="harassment_abuser_relationship"
                            value="{{ old('harassment_abuser_relationship') }}"
                          placeholder="e.g. Supervisor, Colleague"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                          required>
                           @error('harassment_abuser_relationship')
                          <span class="text-red-500 text-sm">{{ $message }}</span>
                           @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <label for="harassment_sub_category" class="block text-gray-700">Sub Category <span
                                            class="text-red-600">*</span>:</label>
                                    @php($harassmentSubCategories =
                                    ['Verbal','Physical','Sexual','Discriminatory','Bullying','Cyber','Retaliation','Other'])
                                    <select name="harassment_sub_category" id="harassment_sub_category" required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        <option value="">Select Sub Category</option>
                                        @foreach($harassmentSubCategories as $sub)
                                        <option value="{{ $sub }}" {{ old('harassment_sub_category')==$sub ? 'selected'
                                            : '' }}>{{ $sub }}</option>
                                        @endforeach
                                    </select>
                                    @error('harassment_sub_category')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="harassment_incident_date" class="block text-gray-700">Incident Date &
                                        Time <span class="text-red-600">*</span>:</label>
                                    <input type="datetime-local" name="harassment_incident_date"
                                        id="harassment_incident_date" value="{{ old('harassment_incident_date') }}"
                                        max="{{ now()->format('Y-m-d\TH:i') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        required>
                                    @error('harassment_incident_date')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="harassment_location" class="block text-gray-700">Location <span
                                            class="text-red-600">*</span>:</label>
                                    <input type="text" name="harassment_location" id="harassment_location"
                                        value="{{ old('harassment_location') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        required>
                                    @error('harassment_location')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                     <label for="harassment_employee_number" class="block text-gray-700 text-xs">
                              Employee No (Victim) <span class="text-red-600">*</span>:
                               </label>
                           <input type="text" 
                           name="harassment_employee_number" 
                            id="harassment_employee_number"
                            value="{{ old('harassment_employee_number') }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                             required>
                            @error('harassment_employee_number')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                              @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <div class="md:col-span-3">
                                    <label for="harassment_details" class="block text-gray-700">Incident Details /
                                        Evidence Summary <span class="text-red-600">*</span>:</label>
                                    <textarea name="harassment_details" id="harassment_details" rows="5"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        required>{{ old('harassment_details') }}</textarea>
                                    @error('harassment_details')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                       <label for="harassment_employee_phone" class="block text-gray-700 text-xs">
                                Victim Phone <span class="text-red-600">*</span>:
                           </label>
                          <input type="text" 
                             name="harassment_employee_phone" 
                           id="harassment_employee_phone"
                           value="{{ old('harassment_employee_phone') }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 mb-4"
                              required>
                       @error('harassment_employee_phone')
                         <span class="text-red-500 text-sm">{{ $message }}</span>
                             @enderror
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
                            <!-- Witnesses -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="font-semibold text-gray-700 flex items-center gap-2">Witnesses <span
                                            class="text-gray-500 text-xs font-normal">(<span id="witness-count">0</span>
                                            / 10)</span></h5>
                                    <button type="button" id="add-witness-btn"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Witness
                                    </button>
                                </div>
                                <div id="witnesses-wrapper" class="space-y-4"></div>
                                <template id="witness-template">
                                    <div class="witness-item border rounded bg-white shadow-sm">
                                        <div
                                            class="flex items-center justify-between px-3 py-2 border-b bg-gray-50 rounded-t">
                                            <span class="text-xs font-semibold text-gray-700">Witness <span
                                                    class="witness-number"></span></span>
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    class="toggle-witness text-gray-500 hover:text-gray-700 text-xs"
                                                    title="Collapse">−</button>
                                                <button type="button"
                                                    class="remove-witness text-red-500 hover:text-red-600 text-xs"
                                                    title="Remove">✕</button>
                                            </div>
                                        </div>
                                        <div class="witness-body p-3 grid grid-cols-1 md:grid-cols-5 gap-3">
                                            <div>
                                                <label
                                                    class="block text-gray-700 text-xxs uppercase tracking-wide text-[10px]">Employee
                                                    No</label>
                                                <input type="text" data-field="employee_number"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-gray-700 text-xxs uppercase tracking-wide text-[10px]">Name
                                                    <span class="text-red-600">*</span></label>
                                                <input type="text" data-field="name" required
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-gray-700 text-xxs uppercase tracking-wide text-[10px]">Phone</label>
                                                <input type="text" data-field="phone"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-gray-700 text-xxs uppercase tracking-wide text-[10px]">Email</label>
                                                <input type="email" data-field="email"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-gray-700 text-xxs uppercase tracking-wide text-[10px]">Statement</label>
                                                <textarea rows="1" data-field="statement"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <!-- Grievance supplemental fields (hidden unless Grievance selected) -->
                        <div id="grievance-section" class="hidden border rounded p-4 bg-amber-50 mb-6">
                            <h4 class="font-semibold text-amber-700 mb-2">Grievance Details (Required)</h4>
                            <p class="text-xs text-amber-700 mb-4">Provide clear, factual information regarding the
                                workplace grievance. Include any prior informal resolution attempts and desired outcome.
                            </p>
                            <!-- Employee Information -->
                            <div class="mb-6 border rounded p-3 bg-white/60">
                                <h5 class="font-semibold text-gray-700 mb-3 text-sm">Employee Information</h5>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label for="grievance_employee_id" class="block text-gray-700 text-xs">Employee ID <span
                                                class="text-red-600">*</span></label>
                                        <input type="text" name="grievance_employee_id" id="grievance_employee_id"
                                            value="{{ old('grievance_employee_id') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                        @error('grievance_employee_id')<span class="text-red-500 text-xs">{{ $message
                                            }}</span>@enderror
                                    </div>
                                    <div>
                                       <label for="grievance_department_position" class="block text-gray-700 text-xs">
                          Department / Position <span class="text-red-600">*</span>
                           </label>
                     <input type="text" 
                       name="grievance_department_position" 
                         id="grievance_department_position"
                          value="{{ old('grievance_department_position') }}"
                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                         required>
                        @error('grievance_department_position')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                         @enderror
                                    </div>
                                    <div>
                                        <label for="grievance_supervisor_name" class="block text-gray-700 text-xs">Supervisor Name</label>
                                        <input type="text" name="grievance_supervisor_name" id="grievance_supervisor_name"
                                            value="{{ old('grievance_supervisor_name') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                        @error('grievance_supervisor_name')<span class="text-red-500 text-xs">{{
                                            $message }}</span>@enderror
                                    </div>
                                    <div>
                                         <label for="grievance_employment_start_date" class="block text-gray-700 text-xs">
                           Employment Start Date <span class="text-red-600">*</span>
                    </label>
                    <input type="date" 
                     name="grievance_employment_start_date" 
                      id="grievance_employment_start_date"
                     value="{{ old('grievance_employment_start_date') }}"
                     max="{{ now()->format('Y-m-d') }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                      required>
                         @error('grievance_employment_start_date')
                      <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Grievance Details -->
                            <div class="mb-6 border rounded p-3 bg-white/60">
                                <h5 class="font-semibold text-gray-700 mb-3 text-sm">Grievance Details</h5>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <label for="grievance_type" class="block text-gray-700 text-xs">Grievance Type <span class="text-red-600">*</span></label>
                                        @php($grievanceTypes = ['Policy Violation','Pay/Benefits','Working
                                        Conditions','Discrimination','Performance Management','Safety','Other'])
                                        <select name="grievance_type" id="grievance_type"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                                            <option value="">Select Type</option>
                                            @foreach($grievanceTypes as $gt)
                                            <option value="{{ $gt }}" {{ old('grievance_type')===$gt ? 'selected' : ''
                                                }}>{{ $gt }}</option>
                                            @endforeach
                                        </select>
                                        @error('grievance_type')<span class="text-red-500 text-xs">{{ $message
                                            }}</span>@enderror
                                    </div>
                                    <div class="md:col-span-3">
                                       <label for="grievance_policy_violated" class="block text-gray-700 text-xs">
                           Policy / Procedure Allegedly Violated <span class="text-red-600">*</span>
                         </label>
                       <input type="text" 
                         name="grievance_policy_violated" 
                          id="grievance_policy_violated"
                           value="{{ old('grievance_policy_violated') }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                             required>
                          @error('grievance_policy_violated')
                          <span class="text-red-500 text-xs">{{ $message }}</span>
                             @enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <label class="block text-gray-700 text-xs">Previous Informal Attempts?</label>
                                        <div class="flex gap-4 mt-1 text-xs">
                                            <label class="flex items-center gap-1"><input type="radio"
                                                    name="grievance_previous_attempts" value="Yes" {{
                                                    old('grievance_previous_attempts')==='Yes' ? 'checked' : '' }}
                                                    class="text-indigo-600"> Yes</label>
                                            <label class="flex items-center gap-1"><input type="radio"
                                                    name="grievance_previous_attempts" value="No" {{
                                                    old('grievance_previous_attempts')==='No' ? 'checked' : '' }}
                                                    class="text-indigo-600"> No</label>
                                        </div>
                                        @error('grievance_previous_attempts')<span class="text-red-500 text-xs">{{
                                            $message }}</span>@enderror
                                    </div>
                                    <div class="md:col-span-3">
                                        <label for="grievance_previous_attempts_details" class="block text-gray-700 text-xs">If Yes, Provide Details</label>
                                        <textarea name="grievance_previous_attempts_details" id="grievance_previous_attempts_details"
                                            id="grievance_previous_attempts_details" rows="2"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('grievance_previous_attempts_details') }}</textarea>
                                        @error('grievance_previous_attempts_details')<span
                                            class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div>
                                    <label for="grievance_desired_outcome" class="block text-gray-700 text-xs">
                              Desired Outcome / Resolution <span class="text-red-600">*</span>
                           </label>
                               <textarea 
                             name="grievance_desired_outcome" 
                             id="grievance_desired_outcome" 
                              rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                       required>{{ old('grievance_desired_outcome') }}</textarea>
                           @error('grievance_desired_outcome')
                          <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                                </div>
                            </div>
                            <!-- Subject / Respondent Information -->
                            <div class="mb-6 border rounded p-3 bg-white/60">
                                <h5 class="font-semibold text-gray-700 mb-3 text-sm">Subject / Respondent Information
                                </h5>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="grievance_subject_name" class="block text-gray-700 text-xs">
                               Name (Person / Department) <span class="text-red-600">*</span>
                              </label>
                              <input type="text" 
                                name="grievance_subject_name" 
                                 id="grievance_subject_name"
                                 value="{{ old('grievance_subject_name') }}"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                  required>
                                    @error('grievance_subject_name')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                      @enderror
                                    </div>
                                    <div>
                                       <label for="grievance_subject_position" class="block text-gray-700 text-xs">
                                       Position / Role <span class="text-red-600">*</span>
                                      </label>
                                  <input type="text" 
                                   name="grievance_subject_position" 
                               id="grievance_subject_position"
                               value="{{ old('grievance_subject_position') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                  required>
                                     @error('grievance_subject_position')
                                       <span class="text-red-500 text-xs">{{ $message }}</span>
                                   @enderror
                                    </div>
                                    <div>
                                         <label for="grievance_subject_relationship" class="block text-gray-700 text-xs">
                                               Relationship to Complainant <span class="text-red-600">*</span>
                                                  </label>
                                                 <input type="text" 
                                                  name="grievance_subject_relationship" 
                                                 id="grievance_subject_relationship"
                                                 value="{{ old('grievance_subject_relationship') }}"
                                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                                     required>
                                                  @error('grievance_subject_relationship')
                                              <span class="text-red-500 text-xs">{{ $message }}</span>
                                               @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Process Fields -->
                            <div class="mb-6 border rounded p-3 bg-white/60">
                                <h5 class="font-semibold text-gray-700 mb-3 text-sm">Process</h5>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                                    <label class="flex items-center gap-2"><input type="checkbox"
                                            name="grievance_union_representation" value="1" {{
                                            old('grievance_union_representation') ? 'checked' : '' }}
                                            class="text-indigo-600"> Union Representation Requested</label>
                                    <label class="flex items-center gap-2"><input type="checkbox"
                                            name="grievance_anonymous" value="1" {{ old('grievance_anonymous')
                                            ? 'checked' : '' }} class="text-indigo-600"> Request Anonymous
                                        Handling</label>
                                    <label class="flex items-center gap-2"><input type="checkbox"
                                            name="grievance_acknowledgment" id="grievance_acknowledgment" value="1" {{
                                            old('grievance_acknowledgment') ? 'checked' : '' }} class="text-indigo-600">
                                        I acknowledge the grievance policy <span class="text-red-600">*</span></label>
                                </div>
                                @error('grievance_acknowledgment')<span class="text-red-500 text-xs">{{ $message
                                    }}</span>@enderror
                            </div>
                            <!-- Timeline -->
                            <div class="mb-6 border rounded p-3 bg-white/60">
                                <h5 class="font-semibold text-gray-700 mb-3 text-sm">Timeline</h5>
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                    <div>
                                        <label for="grievance_first_occurred_date" class="block text-gray-700 text-xs">
                                        Date First Occurred <span class="text-red-600">*</span>
                                              </label>
                                               <input type="date" 
                                                  name="grievance_first_occurred_date" 
                                             id="grievance_first_occurred_date"
                                              value="{{ old('grievance_first_occurred_date') }}"
                                            max="{{ now()->format('Y-m-d') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                             required>
                                             @error('grievance_first_occurred_date')
                                              <span class="text-red-500 text-xs">{{ $message }}</span>
                                               @enderror
                                    </div>
                                    <div>
                                        <label for="grievance_most_recent_date" class="block text-gray-700 text-xs">Most Recent Incident</label>
                                        <input type="date" name="grievance_most_recent_date" id="grievance_most_recent_date"
                                            value="{{ old('grievance_most_recent_date') }}"
                                            max="{{ now()->format('Y-m-d') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                        @error('grievance_most_recent_date')<span class="text-red-500 text-xs">{{
                                            $message }}</span>@enderror
                                    </div>
                                    <div>
                                       <label for="grievance_pattern_frequency" class="block text-gray-700 text-xs">
                                        Pattern Frequency <span class="text-red-600">*</span>
                                         </label>
                                           @php($patternFreq = ['One-time','Ongoing','Recurring'])
                                          <select name="grievance_pattern_frequency" 
                                           id="grievance_pattern_frequency"
                                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                           required>
                                           <option value="">Select</option>
                                         @foreach($patternFreq as $pf)
                                          <option value="{{ $pf }}" {{ old('grievance_pattern_frequency') === $pf ? 'selected' : '' }}>
                                               {{ $pf }}
                                                  </option>
                                                @endforeach
                                                   </select>
                                             @error('grievance_pattern_frequency')
                                           <span class="text-red-500 text-xs">{{ $message }}</span>
                                     @enderror
                                    </div>
                                    <div>
                                        <label for="grievance_performance_effect" class="block text-gray-700 text-xs">Effect on Job Performance<span class="text-red-600">*</span> </label>
                                        @php($effects = ['None','Minor','Moderate','Severe'])
                                        <select name="grievance_performance_effect" id="grievance_performance_effect"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                                            <option value="">Select</option>
                                            @foreach($effects as $ef)
                                            <option value="{{ $ef }}" {{ old('grievance_performance_effect')===$ef
                                                ? 'selected' : '' }}>{{ $ef }}</option>
                                            @endforeach
                                        </select>
                                        @error('grievance_performance_effect')<span class="text-red-500 text-xs">{{
                                            $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Witnesses (reuse logic) -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="font-semibold text-gray-700 flex items-center gap-2">Witnesses <span
                                            class="text-gray-500 text-xs font-normal">(<span
                                                id="grievance-witness-count">0</span> / 10)</span></h5>
                                    <button type="button" id="grievance-add-witness-btn"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white text-xs rounded shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Witness
                                    </button>
                                </div>
                                <div id="grievance-witnesses-wrapper" class="space-y-4"></div>
                                <template id="grievance-witness-template">
                                    <div class="witness-item border rounded bg-white shadow-sm">
                                        <div
                                            class="flex items-center justify-between px-3 py-2 border-b bg-gray-50 rounded-t">
                                            <span class="text-xs font-semibold text-gray-700">Witness <span
                                                    class="witness-number"></span></span>
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    class="toggle-witness text-gray-500 hover:text-gray-700 text-xs"
                                                    title="Collapse">−</button>
                                                <button type="button"
                                                    class="remove-witness text-red-500 hover:text-red-600 text-xs"
                                                    title="Remove">✕</button>
                                            </div>
                                        </div>
                                        <div class="witness-body p-3 grid grid-cols-1 md:grid-cols-5 gap-3">
                                            <div>
                                                <label
                                                    class="block text-gray-700 text-xxs uppercase tracking-wide text-[10px]">Employee
                                                    No</label>
                                                <input type="text" data-field="employee_number"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-gray-700 text-xxs uppercase tracking-wide text-[10px]">Name
                                                    <span class="text-red-600">*</span></label>
                                                <input type="text" data-field="name" required
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-gray-700 text-xxs uppercase tracking-wide text-[10px]">Phone</label>
                                                <input type="text" data-field="phone"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-gray-700 text-xxs uppercase tracking-wide text-[10px]">Email</label>
                                                <input type="email" data-field="email"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-gray-700 text-xxs uppercase tracking-wide text-[10px]">Statement</label>
                                                <textarea rows="1" data-field="statement"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </template>
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
                                    @error('complainant_name')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="complainant_email" class="block text-gray-700">Email <span
                                            class="text-red-600">*</span>:</label>
                                    <input type="email" name="complainant_email" id="complainant_email"
                                        value="{{ old('complainant_email', auth()->user()->email ?? '') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        required>
                                    @error('complainant_email')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="complainant_phone" class="block text-gray-700">Phone <span
                                            class="text-red-600">*</span>:</label>
                                    <input type="tel" name="complainant_phone" id="complainant_phone"
                                        value="{{ old('complainant_phone') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        required>
                                    @error('complainant_phone')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="complainant_account_number" class="block text-gray-700">Account Number
                                        <span class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <input type="text" name="complainant_account_number" id="complainant_account_number"
                                        value="{{ old('complainant_account_number') }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @error('complainant_account_number')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <!-- Assignment & Files Section -->
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
                                            '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="branch_id" class="block text-gray-700">Branch <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <select name="branch_id" id="branch_id"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        <option value="">Not applicable</option>
                                        @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id')==$branch->id ? 'selected'
                                            : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="region_id" class="block text-gray-700">Region <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <select name="region_id" id="region_id"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        <option value="">Not applicable</option>
                                        @foreach ($regions as $region)
                                        <option value="{{ $region->id }}" {{ old('region_id')==$region->id ? 'selected'
                                            : '' }}>{{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('region_id')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label for="division_id" class="block text-gray-700">Division <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <select name="division_id" id="division_id"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        <option value="">Not applicable</option>
                                        @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}" {{ old('division_id')==$division->id ?
                                            'selected' : '' }}>{{ $division->short_name ?? $division->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('division_id')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
                                </div>
                                <div>
                                    <label for="watchers" class="block text-gray-700">Watchers <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <select name="watchers[]" id="watchers" multiple size="5"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ in_array($user->id, old('watchers', [])) ?
                                            'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-gray-600">Hold Ctrl/Cmd to select multiple users</small>
                                    @error('watchers')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="attachments" class="block text-gray-700">File Attachments <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <input type="file" name="attachments[]" id="attachments" multiple
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt,.zip,.rar"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <small class="text-gray-600">Allowed: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG,
                                    PNG, TXT, ZIP, RAR (10MB each, max 10 files)</small>
                                @error('attachments')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                                @error('attachments.*')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                            </div>
                            <div class="mb-4">
                                <label for="comments" class="block text-gray-700">Initial Comment <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <textarea name="comments" id="comments" rows="3"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('comments') }}</textarea>
                                @error('comments')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="comment_type" class="block text-gray-700">Comment Type <span
                                            class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                    <select name="comment_type" id="comment_type"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        <option value="Internal" {{ old('comment_type')=='Internal' ? 'selected' : ''
                                            }}>Internal</option>
                                        <option value="Customer" {{ old('comment_type')=='Customer' ? 'selected' : ''
                                            }}>Customer</option>
                                        <option value="System" {{ old('comment_type')=='System' ? 'selected' : '' }}>
                                            System</option>
                                    </select>
                                    @error('comment_type')<span class="text-red-500 text-sm">{{ $message
                                        }}</span>@enderror
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
            const harassmentRequiredFields = ['harassment_sub_category','harassment_incident_date','harassment_location','harassment_details','harassment_employee_phone','harassment_employee_number',
            'harassment_abuser_relationship','harassment_abuser_phone','harassment_abuser_name'];
            const grievanceSection = document.getElementById('grievance-section');
            const grievanceRequiredFields = ['grievance_employee_id','grievance_acknowledgment','grievance_performance_effect',
            'grievance_pattern_frequency','grievance_first_occurred_date','grievance_subject_relationship','grievance_subject_position',
            'grievance_subject_name','grievance_desired_outcome','grievance_policy_violated','grievance_employment_start_date','grievance_department_position','grievance_type'];
            const incidentDateField = document.getElementById('harassment_incident_date');
            const witnessWrapper = document.getElementById('witnesses-wrapper');
            const witnessTemplate = document.getElementById('witness-template');
            const addWitnessBtn = document.getElementById('add-witness-btn');
            let witnessCount = 0;
            const witnessCountEl = document.getElementById('witness-count');
            // Grievance witness elements
            const grievanceWitnessWrapper = document.getElementById('grievance-witnesses-wrapper');
            const grievanceWitnessTemplate = document.getElementById('grievance-witness-template');
            const grievanceAddWitnessBtn = document.getElementById('grievance-add-witness-btn');
            let grievanceWitnessCount = 0;
            const grievanceWitnessCountEl = document.getElementById('grievance-witness-count');

            function updateWitnessNumbers(){
                const items = witnessWrapper ? witnessWrapper.querySelectorAll('.witness-item') : [];
                items.forEach((item, idx) => {
                    const numSpan = item.querySelector('.witness-number');
                    if(numSpan) numSpan.textContent = idx+1;
                    item.querySelectorAll('[data-field]').forEach(el => {
                        const field = el.getAttribute('data-field');
                        el.name = `witnesses[${idx}][${field}]`;
                    });
                });
                witnessCount = items.length;
                if(witnessCountEl) witnessCountEl.textContent = witnessCount;
            }

            function addWitness(data={}){
                if(!witnessWrapper || !witnessTemplate) return;
                if(witnessCount >= 10) return;
                const clone = witnessTemplate.content.cloneNode(true);
                const container = clone.querySelector('.witness-item');
                container.querySelectorAll('[data-field]').forEach(el => { const field = el.getAttribute('data-field'); if(data[field]) el.value = data[field]; });
                const removeBtn = container.querySelector('.remove-witness');
                removeBtn && removeBtn.addEventListener('click', () => { container.remove(); updateWitnessNumbers(); });
                const toggleBtn = container.querySelector('.toggle-witness');
                const body = container.querySelector('.witness-body');
                toggleBtn && toggleBtn.addEventListener('click', () => {
                    if(body.classList.contains('hidden')){ body.classList.remove('hidden'); toggleBtn.textContent='−'; }
                    else { body.classList.add('hidden'); toggleBtn.textContent='+'; }
                });
                witnessWrapper.appendChild(clone);
                updateWitnessNumbers();
            }

            function updateGrievanceWitnessNumbers(){
                const items = grievanceWitnessWrapper ? grievanceWitnessWrapper.querySelectorAll('.witness-item') : [];
                items.forEach((item, idx) => {
                    const numSpan = item.querySelector('.witness-number');
                    if(numSpan) numSpan.textContent = idx+1;
                    item.querySelectorAll('[data-field]').forEach(el => {
                        const field = el.getAttribute('data-field');
                        el.name = `grievance_witnesses[${idx}][${field}]`;
                    });
                });
                grievanceWitnessCount = items.length;
                if(grievanceWitnessCountEl) grievanceWitnessCountEl.textContent = grievanceWitnessCount;
            }

            function addGrievanceWitness(data={}){
                if(!grievanceWitnessWrapper || !grievanceWitnessTemplate) return;
                if(grievanceWitnessCount >= 10) return;
                const clone = grievanceWitnessTemplate.content.cloneNode(true);
                const container = clone.querySelector('.witness-item');
                container.querySelectorAll('[data-field]').forEach(el => { const field = el.getAttribute('data-field'); if(data[field]) el.value = data[field]; });
                const removeBtn = container.querySelector('.remove-witness');
                removeBtn && removeBtn.addEventListener('click', () => { container.remove(); updateGrievanceWitnessNumbers(); });
                const toggleBtn = container.querySelector('.toggle-witness');
                const body = container.querySelector('.witness-body');
                toggleBtn && toggleBtn.addEventListener('click', () => {
                    if(body.classList.contains('hidden')){ body.classList.remove('hidden'); toggleBtn.textContent='−'; }
                    else { body.classList.add('hidden'); toggleBtn.textContent='+'; }
                });
                grievanceWitnessWrapper.appendChild(clone);
                updateGrievanceWitnessNumbers();
            }

            if(addWitnessBtn){
                addWitnessBtn.addEventListener('click', () => addWitness());
            }
            if(grievanceAddWitnessBtn){
                grievanceAddWitnessBtn.addEventListener('click', () => addGrievanceWitness());
            }

            // Rehydrate old witness inputs if validation failed
            @if(is_array(old('witnesses')))
                @foreach(old('witnesses') as $idx => $w)
                    addWitness(@json($w));
                @endforeach
            @endif
            @if(is_array(old('grievance_witnesses')))
                @foreach(old('grievance_witnesses') as $idx => $w)
                    addGrievanceWitness(@json($w));
                @endforeach
            @endif

            function toggleHarassmentSection(){
                if(!categoryField) return;
                const opt = categoryField.options[categoryField.selectedIndex];
                const name = opt ? (opt.text || '').toLowerCase() : '';
                const isHarassment = name.startsWith('harassment');
                const isGrievance = name.startsWith('grievance');
                
                // First, remove required attributes from ALL conditional fields to prevent validation issues
                harassmentRequiredFields.forEach(id=>{ 
                    const el=document.getElementById(id); 
                    if(el){ 
                        el.removeAttribute('required'); 
                        // Also clear custom validity to prevent stale validation messages
                        el.setCustomValidity('');
                    }
                });
                grievanceRequiredFields.forEach(id=>{ 
                    const el=document.getElementById(id); 
                    if(el){ 
                        el.removeAttribute('required'); 
                        el.setCustomValidity('');
                    }
                });
                
                // Hide all conditional sections first
                if(harassmentSection) harassmentSection.classList.add('hidden');
                if(grievanceSection) grievanceSection.classList.add('hidden');
                
                // Then show and enable the appropriate section
                if(isHarassment){
                    harassmentSection.classList.remove('hidden');
                    harassmentRequiredFields.forEach(id=>{ 
                        const el=document.getElementById(id); 
                        if(el){ 
                            el.setAttribute('required','required'); 
                        }
                    });
                } else if(isGrievance){
                    grievanceSection.classList.remove('hidden');
                    grievanceRequiredFields.forEach(id=>{ 
                        const el=document.getElementById(id); 
                        if(el){ 
                            el.setAttribute('required','required'); 
                        }
                    });
                }
            }

            // Add form submission validation to prevent issues with hidden required fields
            const form = document.querySelector('form[method="POST"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Before submitting, ensure no hidden required fields cause validation issues
                    const hiddenRequiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
                    hiddenRequiredFields.forEach(field => {
                        const fieldContainer = field.closest('#harassment-section, #grievance-section');
                        if (fieldContainer && fieldContainer.classList.contains('hidden')) {
                            // Temporarily remove required attribute from hidden fields during submission
                            field.removeAttribute('required');
                            field.setCustomValidity('');
                        }
                    });
                });
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
                    // Immediately toggle sections to prevent validation issues
                    toggleHarassmentSection();
                    maybeAdoptDefaultPriority();
                    // Do NOT auto-change expected date here; only priority drives it now.
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

            // Enforce no-future incident date (defensive client-side)
            if(incidentDateField){
                function clampIncident(){
                    const max = new Date();
                    const val = incidentDateField.value ? new Date(incidentDateField.value) : null;
                    if(val && val.getTime() > max.getTime()){
                        // Set to current local datetime (truncate seconds)
                        const now = new Date();
                        now.setSeconds(0,0);
                        const local = new Date(now.getTime()-now.getTimezoneOffset()*60000).toISOString().slice(0,16);
                        incidentDateField.value = local;
                    }
                }
                incidentDateField.addEventListener('change', clampIncident);
                incidentDateField.addEventListener('blur', clampIncident);
            }
        })();
    </script>
    @endpush
</x-app-layout>