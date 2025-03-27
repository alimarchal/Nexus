<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            {{ isset($doc) ? 'Edit' : 'Create' }} Document
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('docs.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 text-white rounded-md hover:bg-green-800">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ isset($doc) ? route('docs.update', $doc) : route('docs.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    @if (isset($doc))
                        @method('PUT')
                    @endif

                    <div class="space-y-6">
                        <!-- Title -->
                        <div>
                            <x-label for="title" value="Document Title" />
                            <x-input id="title" class="block mt-1 w-full" type="text" name="title"
                                :value="old('title', $doc->title ?? '')" required autofocus />
                            <x-input-error for="title" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-label for="description" value="Description" />
                            <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
                                rows="3">{{ old('description', $doc->description ?? '') }}</textarea>
                            <x-input-error for="description" class="mt-2" />
                        </div>

                        <!-- Category Dropdown -->
                        <div>
                            <x-label for="category_id" value="Category" />
                            <select name="category_id" id="category_id"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $doc->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error for="category_id" class="mt-2" />
                        </div>

                        <!-- Division Dropdown -->
                        <div>
                            <x-label for="division_id" value="Division" />
                            <select name="division_id" id="division_id"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ old('division_id', $doc->division_id ?? '') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error for="division_id" class="mt-2" />
                        </div>

                        <!-- Document File Upload -->
                        <div>
                            <x-label for="document" value="Document File" />
                            <input type="file" name="document" id="document"
                                class="block mt-1 w-full text-sm text-gray-500 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                {{ !isset($doc) ? 'required' : '' }}>
                            <x-input-error for="document" class="mt-2" />
                            @if (isset($doc) && $doc->document)
                                <p class="mt-2 text-sm text-gray-500">
                                    Current file: <a href="{{ asset('storage/' . $doc->document) }}"
                                        class="text-blue-600 underline"
                                        target="_blank">{{ basename($doc->document) }}</a>
                                </p>
                            @endif
                        </div>

                        <div class="flex justify-end mt-6">
                            <x-button>
                                {{ isset($doc) ? 'Update Document' : 'Create Document' }}
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
