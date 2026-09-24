<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div class="ak-head-text">
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('product.index') }}">Product</a><span aria-hidden="true">›</span>
                    <a href="{{ route('aksic.index') }}">AKSIC</a><span aria-hidden="true">›</span><span>New case</span>
                </nav>
                <h1 class="ak-title">New AKSIC case</h1>
                <p class="ak-sub">Saved as <b>Pending</b>. The repayment schedule is generated when the case is approved.</p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ session('aksic.list_url', route('aksic.index')) }}" class="ak-btn ak-btn-outline"><span aria-hidden="true">←</span> Back</a>
            </div>
        </div>
    </x-slot>

    @include('aksics._ui-style')

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <x-validation-errors class="mb-4" />
                    <form method="POST" action="{{ route('aksic.store') }}">
                        @csrf
                        @include('aksics._form')
                        <div class="flex items-center justify-end gap-3 mt-6">
                            <a href="{{ route('aksic.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <x-button class="bg-blue-950 hover:bg-green-800">Create AKSIC</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
