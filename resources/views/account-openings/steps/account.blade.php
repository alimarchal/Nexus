@php
    $chequeBook = $account?->chequeBooks->first();
    $debitCard = $account?->debitCards->first();

    // The printed forms offer different leaf counts:
    // Individual AOF page 5 = 10 / 25 / 50, Entity AOF page 5 = 25 / 50 / 100.
    $chequeLeafOptions = $formType === 'entity' ? [25, 50, 100] : [10, 25, 50];
@endphp

<x-aof-step :accountOpeningRequest="$accountOpeningRequest" :step="$step" :progress="$progress"
    title="Account, Products & Services">

    <x-aof-section title="Particulars of Account" reference="AOF page 1 & 4 — Title of Account, Profit Center">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <x-label for="title_of_account" value="Title of Account" />
                <x-input id="title_of_account" name="title_of_account" type="text" class="mt-1 block w-full"
                    :value="old('title_of_account', $account?->title_of_account ?? $customer->displayName())" required />
                <x-input-error for="title_of_account" class="mt-1" />
            </div>

            <div>
                <x-label for="opening_date" value="Date" />
                <x-input id="opening_date" name="opening_date" type="date" class="mt-1 block w-full"
                    :value="old('opening_date', $account?->opening_date?->toDateString() ?? now()->toDateString())" required />
            </div>

            <div>
                <x-label for="profit_center" value="Profit Center" />
                <x-input id="profit_center" name="profit_center" type="text" class="mt-1 block w-full"
                    :value="old('profit_center', $account?->profit_center ?? $accountOpeningRequest->branch?->profit_center)" />
            </div>

            <div>
                <x-label value="Account Number" />
                <x-input type="text" class="mt-1 block w-full bg-gray-100" disabled
                    :value="$account?->account_number ?? 'Generated when saved'" />
            </div>

            <div>
                <x-label value="IBAN" />
                <x-input type="text" class="mt-1 block w-full bg-gray-100" disabled
                    :value="$account?->iban ?? 'Generated when saved'" />
            </div>
        </div>
    </x-aof-section>

    <x-aof-section title="Type of Account" subtitle="Current and Saving products, local or foreign currency."
        reference="AOF page 4 (Individual) / page 5 (Entity)">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <x-label for="account_product_id" value="Product" />
                <select id="account_product_id" name="account_product_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select product</option>
                    @foreach ($accountProducts->groupBy('product_class') as $class => $products)
                        <optgroup label="{{ ucfirst($class) }} Products">
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('account_product_id', $account?->account_product_id) === $product->id)>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <x-input-error for="account_product_id" class="mt-1" />
            </div>

            <div>
                <x-label for="currency_id" value="Currency" />
                <select id="currency_id" name="currency_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency->id }}" @selected(old('currency_id', $account?->currency_id) === $currency->id)>
                            {{ $currency->code }} - {{ $currency->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error for="currency_id" class="mt-1" />
            </div>

            <div>
                <x-label for="product_other" value="Other product (please specify)" />
                <x-input id="product_other" name="product_other" type="text" class="mt-1 block w-full"
                    :value="old('product_other', $account?->product_other)" />
            </div>

            <div>
                <x-label for="currency_other" value="Other foreign currency (please specify)" />
                <x-input id="currency_other" name="currency_other" type="text" class="mt-1 block w-full"
                    :value="old('currency_other', $account?->currency_other)" />
            </div>
        </div>
    </x-aof-section>

    <x-aof-section title="Operating Instructions" reference="AOF page 4 / 5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <x-label for="operating_instruction_id" value="Instruction" />
                <select id="operating_instruction_id" name="operating_instruction_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select</option>
                    @foreach ($operatingInstructions as $instruction)
                        <option value="{{ $instruction->id }}" @selected(old('operating_instruction_id', $account?->operating_instruction_id) === $instruction->id)>
                            {{ $instruction->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error for="operating_instruction_id" class="mt-1" />
            </div>

            <div class="md:col-span-2">
                <x-label for="operating_instruction_other" value="Other (please specify)" />
                <x-input id="operating_instruction_other" name="operating_instruction_other" type="text"
                    class="mt-1 block w-full" :value="old('operating_instruction_other', $account?->operating_instruction_other)" />
            </div>

            <div class="md:col-span-3">
                <x-label for="special_instructions" value="Special / Standing Instructions" />
                <textarea id="special_instructions" name="special_instructions" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('special_instructions', $account?->special_instructions) }}</textarea>
            </div>
        </div>
    </x-aof-section>

    <x-aof-section title="Services" reference="AOF page 4 — SMS Alerts, BAJK Digital Channels/App, Statement">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="space-y-2 md:col-span-3">
                <label class="flex items-center text-sm">
                    <input type="hidden" name="sms_alerts_subscribed" value="0">
                    <input type="checkbox" name="sms_alerts_subscribed" value="1"
                        @checked(old('sms_alerts_subscribed', $account?->sms_alerts_subscribed))
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ml-2">Subscribe to paid SMS alerts for non-digital transactions</span>
                </label>

                <label class="flex items-center text-sm">
                    <input type="hidden" name="digital_channels_opted" value="0">
                    <input type="checkbox" name="digital_channels_opted" value="1"
                        @checked(old('digital_channels_opted', $account?->digital_channels_opted))
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ml-2">Avail BAJK Digital Channels / App financial transaction facility</span>
                </label>

                <label class="flex items-center text-sm">
                    <input type="hidden" name="digital_channels_biometric_verified" value="0">
                    <input type="checkbox" name="digital_channels_biometric_verified" value="1"
                        @checked(old('digital_channels_biometric_verified', $account?->digital_channels_biometric_verified))
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ml-2">Account biometric verification provided</span>
                </label>
            </div>

            <div>
                <x-label for="statement_delivery_mode_id" value="Statement Delivery" />
                <select id="statement_delivery_mode_id" name="statement_delivery_mode_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">-</option>
                    @foreach ($statementDeliveryModes as $mode)
                        <option value="{{ $mode->id }}" @selected(old('statement_delivery_mode_id', $account?->statement_delivery_mode_id) === $mode->id)>
                            {{ $mode->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="statement_frequency_id" value="E-Statement Frequency" />
                <select id="statement_frequency_id" name="statement_frequency_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">-</option>
                    @foreach ($statementFrequencies as $frequency)
                        <option value="{{ $frequency->id }}" @selected(old('statement_frequency_id', $account?->statement_frequency_id) === $frequency->id)>
                            {{ $frequency->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="initial_deposit" value="Initial Deposit" />
                <x-input id="initial_deposit" name="initial_deposit" type="number" step="0.01" min="0"
                    class="mt-1 block w-full" :value="old('initial_deposit', $account?->initial_deposit)" />
            </div>
        </div>
    </x-aof-section>

    <x-aof-section title="Cheque Book Requisition" reference="AOF page 5">
        <div x-data="{ required: {{ old('cheque_book_required', $chequeBook?->is_required) ? 'true' : 'false' }} }">
            <label class="inline-flex items-center text-sm">
                <input type="hidden" name="cheque_book_required" value="0">
                <input type="checkbox" name="cheque_book_required" value="1" x-model="required"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2">Cheque Book Required</span>
            </label>

            <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3" x-show="required" x-cloak>
                <div>
                    <x-label value="Quantity Required" />
                    <x-input name="cheque_book_quantity" type="number" min="1" class="mt-1 block w-full"
                        :value="old('cheque_book_quantity', $chequeBook?->quantity_required)" />
                    <x-input-error for="cheque_book_quantity" class="mt-1" />
                </div>
                <div>
                    <x-label value="No. of Leaves" />
                    <select name="cheque_book_leaves" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($chequeLeafOptions as $leaves)
                            <option value="{{ $leaves }}" @selected((int) old('cheque_book_leaves', $chequeBook?->leaves_count) === $leaves)>
                                {{ $leaves }} Leaves
                            </option>
                        @endforeach
                    </select>
                    <x-input-error for="cheque_book_leaves" class="mt-1" />
                </div>
                <div>
                    <x-label value="Others (please specify)" />
                    <x-input name="cheque_book_leaves_other" type="text" class="mt-1 block w-full"
                        :value="old('cheque_book_leaves_other', $chequeBook?->leaves_other)" />
                </div>
            </div>
        </div>
    </x-aof-section>

    @if ($cardTypes->isNotEmpty())
        <x-aof-section title="BAJK Debit Card"
            subtitle="For Individual and Sole Proprietor accounts only. Name on card is limited to 19 characters including spaces."
            reference="AOF-Individual page 5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <x-label for="card_type_id" value="Card Type" />
                    <select id="card_type_id" name="card_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">No card required</option>
                        @foreach ($cardTypes as $cardType)
                            <option value="{{ $cardType->id }}" @selected(old('card_type_id', $debitCard?->card_type_id) === $cardType->id)>
                                {{ $cardType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-label for="name_on_card" value="Name on Card" />
                    <x-input id="name_on_card" name="name_on_card" type="text" maxlength="19" class="mt-1 block w-full"
                        :value="old('name_on_card', $debitCard?->name_on_card)" />
                    <x-input-error for="name_on_card" class="mt-1" />
                </div>
            </div>
        </x-aof-section>
    @endif

    <x-aof-section title="Terms & Conditions and Indemnity"
        reference="AOF pages 5-13 (Individual) / 6-15 (Entity)">
        <div class="space-y-2 text-sm">
            <label class="flex items-start">
                <input type="hidden" name="terms_and_conditions_accepted" value="0">
                <input type="checkbox" name="terms_and_conditions_accepted" value="1"
                    @checked(old('terms_and_conditions_accepted', $accountOpeningRequest->terms_and_conditions_accepted))
                    class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2">Customer has read, understood and accepted the Terms &amp; Conditions of the account.</span>
            </label>

            <label class="flex items-start">
                <input type="hidden" name="indemnity_undertaking_accepted" value="0">
                <input type="checkbox" name="indemnity_undertaking_accepted" value="1"
                    @checked(old('indemnity_undertaking_accepted', $accountOpeningRequest->indemnity_undertaking_accepted))
                    class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2">Indemnity &amp; Undertaking signed by the customer.</span>
            </label>

            <label class="flex items-start">
                <input type="hidden" name="aof_copy_received_by_customer" value="0">
                <input type="checkbox" name="aof_copy_received_by_customer" value="1"
                    @checked(old('aof_copy_received_by_customer', $accountOpeningRequest->aof_copy_received_by_customer))
                    class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2">Customer has received a copy of the AOF along with the Terms &amp; Conditions.</span>
            </label>

            <label class="flex items-start">
                <input type="hidden" name="shariah_compliant_investment_authorized" value="0">
                <input type="checkbox" name="shariah_compliant_investment_authorized" value="1"
                    @checked(old('shariah_compliant_investment_authorized', $accountOpeningRequest->shariah_compliant_investment_authorized))
                    class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2">Customer authorizes the Bank to invest the deposit in a Shariah compliant manner.</span>
            </label>
        </div>
    </x-aof-section>
</x-aof-step>
