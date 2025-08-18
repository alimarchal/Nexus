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
                                <label for="title" class="block text-gray-700">Complaint Title:</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    required>
                                @error('title')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-gray-700">Description:</label>
                            <textarea name="description" id="description" rows="4"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                required>{{ old('description') }}</textarea>
                            @error('description')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="priority" class="block text-gray-700">Priority:</label>
                                <select name="priority" id="priority"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    required>
                                    <option value="">Select Priority</option>
                                    <option value="Low" {{ old('priority')=='Low' ? 'selected' : '' }}>Low</option>
                                    <option value="Medium" {{ old('priority')=='Medium' ? 'selected' : '' }}>Medium
                                    </option>
                                    <option value="High" {{ old('priority')=='High' ? 'selected' : '' }}>High</option>
                                    <option value="Critical" {{ old('priority')=='Critical' ? 'selected' : '' }}>
                                        Critical</option>
                                </select>
                                @error('priority')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="source" class="block text-gray-700">Source:</label>
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
                                <label for="category" class="block text-gray-700">Category:</label>
                                <input type="text" name="category" id="category" value="{{ old('category') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    placeholder="e.g., Product Quality, Service Issue">
                                @error('category')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="expected_resolution_date" class="block text-gray-700">Expected Resolution
                                    Date:</label>
                                <input type="datetime-local" name="expected_resolution_date"
                                    id="expected_resolution_date" value="{{ old('expected_resolution_date') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                @error('expected_resolution_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="branch_id" class="block text-gray-700">Branch:</label>
                                <select name="branch_id" id="branch_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">Select Branch</option>
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
                        </div>
                    </div>

                    <!-- Complainant Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Complainant Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="complainant_name" class="block text-gray-700">Complainant Name:</label>
                                <input type="text" name="complainant_name" id="complainant_name"
                                    value="{{ old('complainant_name') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                @error('complainant_name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="complainant_email" class="block text-gray-700">Email:</label>
                                <input type="email" name="complainant_email" id="complainant_email"
                                    value="{{ old('complainant_email') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                @error('complainant_email')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="complainant_phone" class="block text-gray-700">Phone:</label>
                                <input type="tel" name="complainant_phone" id="complainant_phone"
                                    value="{{ old('complainant_phone') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                @error('complainant_phone')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="complainant_account_number" class="block text-gray-700">Account
                                    Number:</label>
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
                                <label for="assigned_to" class="block text-gray-700">Assign To:</label>
                                <select name="assigned_to" id="assigned_to"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to')==$user->id ? 'selected' : ''
                                        }}>
                                        {{ $user->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="category_id" class="block text-gray-700">Complaint Category:</label>
                                <select name="category_id" id="category_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id')==$category->id ?
                                        'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="watchers" class="block text-gray-700">Watchers:</label>
                            <select name="watchers[]" id="watchers" multiple size="5"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ in_array($user->id, old('watchers', [])) ? 'selected'
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
                            <label for="attachments" class="block text-gray-700">File Attachments:</label>
                            <input type="file" name="attachments[]" id="attachments" multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt,.zip,.rar"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <small class="text-gray-600">Allowed: PDF, DOC, DOCX, JPG, PNG, TXT, ZIP (max 10MB each, max
                                10 files)</small>
                            @error('attachments')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                            @error('attachments.*')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="comments" class="block text-gray-700">Initial Comment:</label>
                            <textarea name="comments" id="comments" rows="3"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('comments') }}</textarea>
                            @error('comments')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="comment_type" class="block text-gray-700">Comment Type:</label>
                                <select name="comment_type" id="comment_type"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="Internal" {{ old('comment_type')=='Internal' ? 'selected' : '' }}>
                                        Internal</option>
                                    <option value="Customer" {{ old('comment_type')=='Customer' ? 'selected' : '' }}>
                                        Customer</option>
                                    <option value="System" {{ old('comment_type')=='System' ? 'selected' : '' }}>System
                                    </option>
                                </select>
                                @error('comment_type')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex items-center">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_private" value="1" {{ old('is_private') ? 'checked'
                                        : '' }}
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
</x-app-layout>