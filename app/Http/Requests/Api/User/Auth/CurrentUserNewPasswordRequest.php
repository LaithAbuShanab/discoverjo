<?php

namespace App\Http\Requests\Api\User\Auth;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfOldPasswordCorrectRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules;


class CurrentUserNewPasswordRequest extends FormRequest
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
            'old_password'=>['required',new CheckIfOldPasswordCorrectRule()],
            'password' => ['required', 'confirmed', Rules\Password::default(),'different:old_password'],
        ];
    }


    public function messages()
    {
        return [
            // Token
            'token.required' => __('validation.api.token-is-required'),

            // Email
            'email.required' => __('validation.api.email-is-required'),
            'email.email' => __('validation.api.email-must-be-valid'),

            // Password
            'password.required' => __('validation.api.password-is-required'),
            'password.confirmed' => __('validation.api.password-confirmation-mismatch'),
            'password.rules' => __('validation.api.password-must-comply-with-rules'),
            'old_password.required' => __('validation.api.old_password-is-required'),
        ];
    }


    public function attributes()
    {
        return [
            'token' => __('validation.attributes.token'),
            'email' => __('validation.attributes.email'),
            'password' => __('validation.attributes.password'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(
            ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors)
        );
    }
}
