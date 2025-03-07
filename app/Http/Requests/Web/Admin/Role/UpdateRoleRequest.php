<?php

namespace App\Http\Requests\Web\Admin\Role;

use App\Validation\CheckRoleNameAndGuardExistRule;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentRoleId = request()->id;
        return [
            'name_en' => ['required', 'min:3', new CheckRoleNameAndGuardExistRule($currentRoleId)],
            'name_ar' => ['required', 'min:3'],
            'guard' => ['required', 'min:3'],
            'permissions.*' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name_en.required' => 'English name is required.',
            'name_en.min' => 'English name must be at least :min characters.',
            'name_ar.required' => 'Arabic name is required.',
            'name_ar.min' => 'Arabic name must be at least :min characters.',
            'guard.required' => 'Guard is required.',
            'guard.min' => 'Guard must be at least :min characters.',
            'permissions.required' => 'you should at least to choose one permission',
        ];
    }

    public function attributes()
    {
        return [
            'name_en' => 'English Name',
            'name_ar' => 'Arabic Name',
            'guard' => 'Guard',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        foreach ($errors as $error) {
            Toastr::error($error, 'Error');
        }
        throw new HttpResponseException(
            redirect()->back()->withInput()->withErrors($validator)
        );
    }
}
