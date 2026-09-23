@props([
    'accountOpeningRequest',
    'step',
    'progress' => [],
    'title' => '',
    'enctype' => null,
])

@php
    $steps = \App\Http\Controllers\AccountOpeningController::STEPS;
    $stepKeys = array_keys($steps);
    $currentIndex = array_search($step, $stepKeys, true);
    $isLastStep = $currentIndex === count($stepKeys) - 1;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="inline-block text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ $title ?: $steps[$step] }}
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $accountOpeningRequest->request_number }} &middot;
                    {{ $accountOpeningRequest->aof_form_type === \App\Models\AccountOpeningRequest::FORM_ENTITY
                        ? 'Government / Partnership / Company / NGO form'
                        : 'Individual / Joint / Sole Proprietor form' }}
                </p>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('account-openings.show', $accountOpeningRequest) }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-800">
                    Summary
                </a>
                <a href="{{ route('account-openings.index') }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-800">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-status-message />
            <x-validation-errors class="mb-4" />

            {{-- Wizard navigation: the printed form's own section order --}}
            <ol class="mb-4 grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-6">
                @foreach ($steps as $key => $label)
                    @php
                        $done = $progress[$key]['complete'] ?? false;
                        $isCurrent = $key === $step;
                    @endphp
                    <li>
                        <a href="{{ route('account-openings.steps.edit', [$accountOpeningRequest, $key]) }}"
                            class="block rounded-lg border px-3 py-2 text-xs transition
                                {{ $isCurrent
                                    ? 'border-green-800 bg-green-800 text-white'
                                    : ($done
                                        ? 'border-green-200 bg-green-50 text-green-900 hover:border-green-400'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-gray-400 dark:bg-gray-800 dark:text-gray-300') }}">
                            <span class="block font-bold">
                                Step {{ $loop->iteration }}
                                @if ($done && ! $isCurrent)
                                    &#10003;
                                @endif
                            </span>
                            <span class="block leading-tight">{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>

            <form method="POST" action="{{ route('account-openings.steps.update', [$accountOpeningRequest, $step]) }}"
                @if ($enctype) enctype="{{ $enctype }}" @endif>
                @csrf
                @method('PUT')

                <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    {{ $slot }}
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div>
                        @if ($currentIndex > 0)
                            <a href="{{ route('account-openings.steps.edit', [$accountOpeningRequest, $stepKeys[$currentIndex - 1]]) }}"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                                Previous
                            </a>
                        @endif
                    </div>

                    <div class="flex space-x-3">
                        <button type="submit" name="save_and_exit" value="1"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                            Save &amp; Exit
                        </button>
                        <button type="submit"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-6 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-800">
                            {{ $isLastStep ? 'Save & Finish' : 'Save & Continue' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
