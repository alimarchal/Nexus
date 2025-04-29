<?php

namespace App\Http\Requests;

use App\Models\StationeryTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStationeryTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'printed_stationery_id' => ['required', 'exists:printed_stationeries,id'],
            'type' => ['required', Rule::in(['opening_balance', 'in', 'out'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];

        // Add conditional rules for stock out destinations
        if ($this->input('type') === 'out') {
            $rules['stock_out_to'] = ['required', Rule::in(['Branch', 'Region', 'Division'])];

            // Add conditional rules based on stock_out_to selection
            if ($this->input('stock_out_to') === 'Branch') {
                $rules['branch_id'] = ['required', 'exists:branches,id'];
            } elseif ($this->input('stock_out_to') === 'Region') {
                $rules['region_id'] = ['required', 'exists:regions,id'];
            } elseif ($this->input('stock_out_to') === 'Division') {
                $rules['division_id'] = ['required', 'exists:divisions,id'];
            }
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if trying to create an opening balance when one already exists
            if ($this->input('type') === 'opening_balance' && $this->input('printed_stationery_id')) {
                $existingOpeningBalance = StationeryTransaction::where('printed_stationery_id', $this->input('printed_stationery_id'))
                    ->where('type', 'opening_balance')
                    ->exists();

                if ($existingOpeningBalance) {
                    $validator->errors()->add('type', 'An opening balance transaction already exists for this stationery item. Please use "Stock In" instead.');
                }
            }

            // Check if trying to stock out more than available
            if ($this->input('type') === 'out' && $this->input('printed_stationery_id') && $this->input('quantity')) {
                $lastTransaction = StationeryTransaction::where('printed_stationery_id', $this->input('printed_stationery_id'))
                    ->orderBy('created_at', 'desc')
                    ->first();

                $currentBalance = $lastTransaction ? $lastTransaction->balance_after_transaction : 0;

                if ($currentBalance < $this->input('quantity')) {
                    $validator->errors()->add('quantity', "Insufficient stock. Current balance is {$currentBalance}.");
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'printed_stationery_id' => 'stationery item',
            'stock_out_to' => 'stock out destination',
            'branch_id' => 'branch',
            'region_id' => 'region',
            'division_id' => 'division',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'printed_stationery_id.required' => 'Please select a stationery item.',
            'quantity.min' => 'Quantity must be at least 1.',
            'stock_out_to.required' => 'Please select where the stock is being sent to.',
        ];
    }
}
