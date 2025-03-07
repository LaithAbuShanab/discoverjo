<?php

namespace App\Http\Requests\Web\Admin\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules;

class StoreAdminRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|unique:admins|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => 'required',
            'image' => ['nullable', 'max:1024'],
            'role' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.msg.english-name-required'),
            'email.required' => __('validation.msg.arabic-name-required'),
            'email.unique' => __('validation.msg.arabic-name-min-characters', ['min' => ':min']),
            'email.email' => __('validation.msg.priority-required'),
            'password_confirmation.required' => __('validation.msg.priority-min-characters', ['min' => ':min']),
            'role.required' => __('validation.msg.image-required'),
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
