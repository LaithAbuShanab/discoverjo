<?php

namespace App\Http\Requests\Api\Guide;

use App\Helpers\ApiResponse;
use App\Models\User;
use App\Rules\CheckTagExistsRule;
use App\Rules\CheckUserInBlackListRule;
use App\Rules\MinAgeRule;
use App\Validation\JsonArray;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;


class RegisterGuideApiRequest extends FormRequest
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
            'first_name'=>['required','string'],
            'last_name'=>['required','string'],
            'username' => ['nullable', 'string', 'alpha_dash', 'min:3', 'max:20', 'regex:/^[a-zA-Z][a-zA-Z0-9_-]*$/', 'not_regex:/\s/', Rule::unique('users', 'username')],
            'birthday'=>['required', new MinAgeRule()],
            'gender'=>['required',Rule::in(1,2)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class, new CheckUserInBlackListRule()],
            'phone_number'=>['required','string'],
            'description'=>['required','string'],
            'tags' => ['required', new CheckTagExistsRule()],
            'tags.*' => ['exists:tags,slug'],
            'image'=>['required','image'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            "device_token" => ['max:255', 'required'],
            'professional_file'=>['required','mimes:pdf,jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico,svgz,docx']

        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => __('validation.api.first-name-is-required'),
            'first_name.string' => __('validation.api.first-name-must-be-string'),

            'last_name.required' => __('validation.api.last-name-is-required'),
            'last_name.string' => __('validation.api.last-name-must-be-string'),

            'username.string' => __('validation.api.username-must-be-string'),
            'username.alpha_dash' => __('validation.api.username-must-be-alpha-dash'),
            'username.min' => __('validation.api.username-min-length', ['min' => 3]),
            'username.max' => __('validation.api.username-max-length', ['max' => 20]),
            'username.regex' => __('validation.api.username-must-match-regex'),
            'username.not_regex' => __('validation.api.username-must-not-contain-spaces'),
            'username.unique' => __('validation.api.username-unique'),

            'birthday.required' => __('validation.api.birthday-is-required'),
            'birthday.min_age' => __('validation.api.birthday-invalid-age', ['min_age' => 18]),

            'gender.required' => __('validation.api.gender-is-required'),
            'gender.in' => __('validation.api.gender-must-be-valid'),

            'email.required' => __('validation.api.email-is-required'),
            'email.string' => __('validation.api.email-must-be-string'),
            'email.lowercase' => __('validation.api.email-must-be-lowercase'),
            'email.email' => __('validation.api.email-invalid-format'),
            'email.max' => __('validation.api.email-max-length', ['max' => 255]),
            'email.unique' => __('validation.api.email-unique'),

            'phone_number.required' => __('validation.api.phone-number-is-required'),
            'phone_number.string' => __('validation.api.phone-number-must-be-string'),

            'description.required' => __('validation.api.description-is-required'),
            'description.string' => __('validation.api.description-must-be-string'),

            'tags_id.required' => __('validation.api.tags-id-is-required'),
            'tags_id.exists' => __('validation.api.tags-id-must-exist'),

            'image.required' => __('validation.api.image-is-required'),
            'image.image' => __('validation.api.image-must-be-an-image'),

            'password.required' => __('validation.api.password-is-required'),
            'password.confirmed' => __('validation.api.password-confirmed'),

            'device_token.max' => __('validation.api.device-token-max-length', ['max' => 255]),
            'device_token.required' => __('validation.api.device-token-is-required'),

            'professional_file.required' => __('validation.api.professional-file-is-required'),
            'professional_file.mimes' => __('validation.api.professional-file-invalid-format'),
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
