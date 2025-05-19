<?php

namespace App\Http\Requests\Api\User\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class LoginApiUserRequest extends FormRequest
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
            'usernameOrEmail' => ['required', 'string', 'max:255'],
            'password' => ['required',  Rules\Password::defaults()],
            "device_token" => ['nullable', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            // Username or Email
            'usernameOrEmail.required' => __('validation.api.username-or-email-is-required'),
            'usernameOrEmail.string' => __('validation.api.username-or-email-must-be-string'),
            'usernameOrEmail.max' => __('validation.api.username-or-email-max', ['max' => 255]),

            // Password
            'password.required' => __('validation.api.password-is-required'),
            'password.rules' => __('validation.api.password-must-comply-with-rules'),

            // Device Token
            'device_token.required' => __('validation.api.device-token-is-required'),
            'device_token.max' => __('validation.api.device-token-max', ['max' => 255]),
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
