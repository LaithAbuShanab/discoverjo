<?php

namespace App\Http\Requests\Web\Admin\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
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
        $adminId = request()->id;

        return [
            'name' => 'required',
            'email' => ['required','email',Rule::unique('admins', 'email')->ignore($adminId)],
            'image' => ['nullable', 'max:1024'],
            'role' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.msg.name-required'),
            'email.required' => __('validation.msg.email-required'),
            'email.unique' => __('validation.msg.email-already-exists'),
            'email.email' => __('validation.msg.email-should-be-email-format'),
            'password_confirmation.required' => __('validation.msg.password-confirmation-required'),
            'role.required' => __('validation.msg.role-required'),
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('validation.attributes.name'),
            'email' => __('validation.attributes.email'),
            'password' => __('validation.attributes.password'),
            'image' => __('validation.attributes.image'),
            'password_confirmation' => __('validation.attributes.password_confirmation'),
            'role' => __('validation.attributes.role'),
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
