<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAdjustmentRequest extends FormRequest
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
        return [
            'type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'quarter' => ['required', 'integer'],
            'description' => ['required', 'integer'],
            'dataBefore' => ['sometimes', 'integer', 'nullable'],
            'subsectors' => ['required', 'string'],
        ];
    }
}
