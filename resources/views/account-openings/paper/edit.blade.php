@php
    // Alpine needs every field present so x-model and the conditions never meet an undefined value.
    $state = [];
    foreach (\App\Support\PaperForm\PaperFormSchema::sections() as $section) {
        foreach ($section['fields'] as $field) {
            if (in_array($field['type'], ['heading', 'terms'], true)) {
                continue;
            }

            $default = match ($field['type']) {
                'checks' => [],
                'checkbox' => false,
                default => '',
            };
            $state[$field['name']] = $values[$field['name']] ?? $default;
            if (isset($field['other'])) {
                $state[$field['name'].'_other'] = $values[$field['name'].'_other'] ?? '';
            }
        }
    }
    $sections = \App\Support\PaperForm\PaperFormSchema::sections();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="inline-block text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Old Account Opening Form (BAJK paper form)</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $accountOpeningRequest->request_number }} &middot; {{ $accountOpeningRequest->branch?->code }} {{ $accountOpeningRequest->branch?->name }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('account-openings.show', $accountOpeningRequest) }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-800">Summary</a>
                <a href="{{ route('account-openings.index') }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-800">Back to list</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-status-message />
            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('account-openings.paper.update', $accountOpeningRequest) }}" id="paper-form"
                x-data="{
                    d: @js($state),
                    holds(show) { return Object.entries(show).every(([name, expected]) => name === 'any' ? expected.some((alternative) => this.holds(alternative)) : [].concat(expected).some((value) => [].concat(this.d[name] ?? []).includes(value))); },
                    picked(name, keys) { return [].concat(keys).some((key) => [].concat(this.d[name] ?? []).includes(key)); },
                }">
                @csrf
                @method('PUT')

                <div class="space-y-6 overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    @foreach ($sections as $section)
                        {{-- A wrapper carries the x-show: Blade does not compile @js inside a component tag. --}}
                        <div data-section="{{ $section['key'] }}" x-show="holds(@js($section['show'] ?? []))" x-cloak>
                            <x-aof-section :title="$section['number'].'. '.$section['title']" :subtitle="$section['subtitle'] ?? null">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    @foreach ($section['fields'] as $field)
                                        @include('account-openings.paper._field', ['field' => $field, 'values' => $values])
                                    @endforeach
                                </div>
                            </x-aof-section>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <p class="text-xs text-gray-600">Fields marked <span class="font-bold text-red-600">*</span> are mandatory; they must be filled before the form can be submitted for approval.</p>
                    <button type="submit"
                        class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-800">Save</button>
                </div>
            </form>

            @include('account-openings.paper._upload_forms')
        </div>
    </div>
</x-app-layout>
