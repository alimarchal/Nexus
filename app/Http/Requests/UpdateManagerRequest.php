<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManagerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'division_id' => 'required|exists:divisions,id',
            'manager_user_id' => 'required|exists:users,id',
            'title' => 'nullable|string',
        ];
    }
}