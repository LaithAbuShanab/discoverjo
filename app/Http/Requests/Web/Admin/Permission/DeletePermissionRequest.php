<?php

namespace App\Http\Requests\Web\Admin\Permission;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DeletePermissionRequest extends FormRequest
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
            'id' => 'required|integer|exists:permissions,id'
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'ID For Permission Not Exists.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        dd($validator->errors()->all());
        $this->errors = $validator->errors();

    }
}
