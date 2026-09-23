@php
    $roles = [
        'primary' => 'Primary account holder',
        'joint' => 'Joint account holder',
        'sole_proprietor' => 'Sole proprietor',
        'minor' => 'Minor (account in minor name)',
        'guardian' => 'Guardian operating for a minor',
        'authorized_signatory' => 'Authorized signatory',
        'mandate_holder' => 'Mandate holder',
        'agent' => 'Agent (Power of Attorney)',
        'executor' => 'Executor / Administrator',
        'trustee' => 'Trustee',
        'office_bearer' => 'Office bearer',
    ];

    $existing = $account?->holders->sortBy('applicant_number')->values() ?? collect();
    $maxApplicants = $accountOpeningRequest->aof_form_type === \App\Models\AccountOpeningRequest::FORM_ENTITY ? 12 : 4;
    $rowCount = max($existing->count() + 1, 2);
    $rowCount = min($rowCount, $maxApplicants);
@endphp

<x-aof-step :accountOpeningRequest="$accountOpeningRequest" :step="$step" :progress="$progress"
    title="Applicants & Signatures" enctype="multipart/form-data">

    @if (! $account)
        <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800">
            Complete the <a class="font-semibold underline"
                href="{{ route('account-openings.steps.edit', [$accountOpeningRequest, 'account']) }}">Account</a>
            step first &mdash; applicants are attached to the account.
        </div>
    @else
        <x-aof-section title="Applicants / Signatories"
            subtitle="The printed form allows up to {{ $maxApplicants }} applicants. Each applicant needs their own CIF."
            reference="AOF page 1 (Client/Relationship ID) & signature pages">

            @for ($index = 0; $index < $rowCount; $index++)
                @php $holder = $existing[$index] ?? null; @endphp
                <div class="mb-4 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                    <p class="mb-2 text-xs font-bold uppercase text-gray-500">Applicant ({{ $index + 1 }})</p>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                        <div class="md:col-span-2">
                            <x-label value="Customer (CIF)" />
                            <select name="holders[{{ $index }}][customer_id]"
                                class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-</option>
                                <option value="{{ $customer->id }}"
                                    @selected(old("holders.$index.customer_id", $holder?->customer_id ?? ($index === 0 ? $customer->id : null)) === $customer->id)>
                                    {{ $customer->cif_number }} - {{ $customer->displayName() }}
                                </option>
                                @foreach ($otherCustomers as $other)
                                    <option value="{{ $other->id }}"
                                        @selected(old("holders.$index.customer_id", $holder?->customer_id) === $other->id)>
                                        {{ $other->cif_number }} - {{ $other->displayName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-label value="Role" />
                            <select name="holders[{{ $index }}][holder_role]"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}"
                                        @selected(old("holders.$index.holder_role", $holder?->holder_role ?? ($index === 0 ? 'primary' : 'joint')) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-label value="Relationship with primary" />
                            <select name="holders[{{ $index }}][relationship_id]"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-</option>
                                @foreach ($relationships as $relationship)
                                    <option value="{{ $relationship->id }}"
                                        @selected(old("holders.$index.relationship_id", $holder?->relationship_id) === $relationship->id)>
                                        {{ $relationship->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-label value="Designation" />
                            <x-input name="holders[{{ $index }}][designation]" type="text" class="mt-1 block w-full"
                                :value="old('holders.'.$index.'.designation', $holder?->designation)" />
                        </div>

                        <div>
                            <x-label value="Signing order" />
                            <x-input name="holders[{{ $index }}][signing_order]" type="number" min="1" max="20"
                                class="mt-1 block w-full" :value="old('holders.'.$index.'.signing_order', $holder?->signing_order)" />
                        </div>

                        <div>
                            <x-label value="SS Card No." />
                            <x-input name="holders[{{ $index }}][ss_card_number]" type="text" class="mt-1 block w-full"
                                :value="old('holders.'.$index.'.ss_card_number', $holder?->specimenSignatures->first()?->ss_card_number)" />
                        </div>

                        <div class="flex items-end">
                            <label class="inline-flex items-center text-sm">
                                <input type="hidden" name="holders[{{ $index }}][is_signatory]" value="0">
                                <input type="checkbox" name="holders[{{ $index }}][is_signatory]" value="1"
                                    @checked(old("holders.$index.is_signatory", $holder?->is_signatory ?? true))
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2">Authorised to sign</span>
                            </label>
                        </div>

                        <div>
                            <x-label value="Specimen signature (image)" />
                            <input type="file" name="holders[{{ $index }}][signature_image]" accept="image/*"
                                class="mt-1 block w-full text-xs">
                            @if ($holder?->specimenSignatures->first()?->signature_image_path)
                                <a class="text-xs text-blue-600 underline"
                                    href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($holder->specimenSignatures->first()->signature_image_path) }}"
                                    target="_blank">View current</a>
                            @endif
                        </div>

                        <div>
                            <x-label value="Thumb impression (image)" />
                            <input type="file" name="holders[{{ $index }}][thumb_image]" accept="image/*"
                                class="mt-1 block w-full text-xs">
                        </div>

                        <div>
                            <x-label value="Company's / Organisation's Stamp" />
                            <input type="file" name="holders[{{ $index }}][stamp_image]" accept="image/*"
                                class="mt-1 block w-full text-xs">
                            @if ($holder?->specimenSignatures->first()?->organization_stamp_path)
                                <a class="text-xs text-blue-600 underline" target="_blank"
                                    href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($holder->specimenSignatures->first()->organization_stamp_path) }}">View current</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endfor

            <p class="text-xs text-gray-500">
                Leave a row's customer blank to skip it. Saving replaces the applicant list with what is submitted here.
            </p>
        </x-aof-section>
    @endif
</x-aof-step>
