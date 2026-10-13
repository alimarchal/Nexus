@php
    $steps = ['paper' => 'Old paper form'];
    $allComplete = $missing === [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('product.index') }}">Product</a><span aria-hidden="true">›</span>
                    <a href="{{ route('account-openings.index') }}">Account opening</a><span aria-hidden="true">›</span>
                    <span>{{ $accountOpeningRequest->request_number }}</span>
                </nav>
                <h1 class="ak-title">{{ $accountOpeningRequest->paperForm?->display_name ?: 'Old account opening form' }} <span class="ak-muted" style="font-size:16px; font-weight:600">{{ $accountOpeningRequest->request_number }}</span></h1>
                <p class="ak-sub">
                    {{ $accountOpeningRequest->aof_version }} &middot; <strong data-form-edition>{{ $accountOpeningRequest->editionLabel() }} form</strong> &middot; {{ $accountOpeningRequest->branch?->code }} {{ $accountOpeningRequest->branch?->name }}
                </p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ route('account-openings.index') }}" class="ak-btn ak-btn-outline"><span aria-hidden="true">←</span> Back to list</a>
                <a href="{{ route('account-openings.print', $accountOpeningRequest) }}" target="_blank" class="ak-btn ak-btn-outline">Print form</a>
                @can('edit account openings')
                    @if ($accountOpeningRequest->isEditable())
                        <a href="{{ route('account-openings.paper.edit', $accountOpeningRequest) }}" class="ak-btn ak-btn-primary">{{ $allComplete ? 'Review the form' : 'Continue filling the form' }}</a>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    @include('settings.partials.ui-style')

    <div class="ak-page">
        <x-status-message />

        <div class="space-y-4">
            @include('account-openings.partials.workflow')

            @if (! $allComplete)
                <div class="rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" data-missing>
                    <b>Mandatory fields still empty:</b> {{ implode(', ', $missing) }}
                </div>
            @endif

            <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                @include('account-openings.paper._values')
            </div>

            @include('account-openings.partials.history')
        </div>
    </div>
</x-app-layout>
