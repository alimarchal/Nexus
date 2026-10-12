@props(['name', 'id' => null, 'value' => null, 'min' => 0])

{{-- Manual money entry: digits and one decimal point, at most 2 decimals, no spinner list. --}}
<div x-data="{
        amount: @js($value === null || $value === '' ? '' : (string) $value),
        clean() {
            let v = this.amount.replace(/[^0-9.]/g, '');
            const dot = v.indexOf('.');
            if (dot !== -1) {
                v = v.slice(0, dot + 1) + v.slice(dot + 1).replace(/\./g, '').slice(0, 2);
            }
            this.amount = v;
        },
    }" x-init="clean()">
    <input {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500']) }}
        type="text" inputmode="decimal" autocomplete="off" name="{{ $name }}" @if ($id) id="{{ $id }}" @endif
        placeholder="e.g. {{ number_format((float) $min, 2, '.', '') }}" x-model="amount" x-on:input="clean()">
    <p class="mt-1 text-xs text-gray-600">Type the amount (minimum {{ number_format((float) $min, 2) }}, up to 2 decimals).</p>
</div>
