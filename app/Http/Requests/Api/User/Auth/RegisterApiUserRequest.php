<?php

namespace App\Http\Requests\Api\User\Auth;

use App\Models\User;
use App\Rules\CheckUserInBlackListRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use App\Helpers\ApiResponse;
use App\Rules\IfUserCanMakeCommentInPostRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class RegisterApiUserRequest extends FormRequest
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
            'username' => ['bail','required', 'string', 'alpha_dash', 'min:4', 'max:20', 'regex:/^[a-zA-Z][a-zA-Z0-9_-]*$/', 'not_regex:/\s/', 'unique:' . User::class],
            'email' => ['bail','required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class, new CheckUserInBlackListRule()],
            'password' => ['bail','required', 'confirmed', Rules\Password::defaults()],
            "device_token" => ['max:255', 'nullable'],
            'referral_code'=>['nullable','string'],
        ];
    }

    public function messages()
    {
        return [
            // Username
            'username.required' => __('validation.api.username-is-required'),
            'username.string' => __('validation.api.username-must-be-string'),
            'username.alpha_dash' => __('validation.api.username-must-be-alpha-dash'),
            'username.min' => __('validation.api.username-min', ['min' => 4]),
            'username.max' => __('validation.api.username-max', ['max' => 20]),
            'username.regex' => __('validation.api.username-regex'),
            'username.not_regex' => __('validation.api.username-no-whitespace'),
            'username.unique' => __('validation.api.username-unique'),

            // Email
            'email.required' => __('validation.api.email-is-required'),
            'email.string' => __('validation.api.email-must-be-string'),
            'email.lowercase' => __('validation.api.email-must-be-lowercase'),
            'email.email' => __('validation.api.email-must-be-valid'),
            'email.max' => __('validation.api.email-max', ['max' => 255]),
            'email.unique' => __('validation.api.email-unique'),
            'email.blacklist' => __('validation.api.email-in-blacklist'),

            // Password
            'password.required' => __('validation.api.password-is-required'),
            'password.confirmed' => __('validation.api.password-confirmation-mismatch'),
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
