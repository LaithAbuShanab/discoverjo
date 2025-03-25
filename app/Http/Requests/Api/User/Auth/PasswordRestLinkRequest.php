<?php

namespace App\Http\Requests\Api\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;


class PasswordRestLinkRequest extends FormRequest
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
            'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('validation.api.email-is-required'),
            'email.email' => __('validation.api.email-valid'),

        ];
    }

    public function attributes()
    {
        return [

            'email' => __('validation.attributes.email'),

        ];
    }
}
