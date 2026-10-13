<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\PaperForm\PaperFormSchema;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Saving the old BAJK paper Account Opening Form. The rules come from PaperFormSchema, so only the fields
 * that apply to the chosen nature of account are checked.
 */
class UpdatePaperAccountOpeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit account openings') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return PaperFormSchema::rules((array) $this->input('data', []));
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (PaperFormSchema::labels() as $name => $label) {
            $attributes['data.'.$name] = $label;
        }

        return $attributes;
    }
}
