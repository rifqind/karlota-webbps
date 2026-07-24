<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePdrbRequest extends FormRequest
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
            'id' => ['sometimes', 'required', 'integer'],
            'dataContents' => ['required', 'array'],
            'dataContents.*.id' => ['required', 'integer', 'exists:pdrbs,id'],
            'dataContents.*.dataset_id' => ['required', 'integer'],
            'dataContents.*.year' => ['required', 'integer'],
            'dataContents.*.quarter' => ['required', 'integer'],
            'dataContents.*.subsector_id' => ['required', 'integer'],
            'dataContents.*.adhb' => ['sometimes', 'numeric', 'nullable'],
            'dataContents.*.adhk' => ['sometimes', 'numeric', 'nullable'],
        ];
    }
}
