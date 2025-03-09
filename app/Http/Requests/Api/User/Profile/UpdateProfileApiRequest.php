<?php

namespace App\Http\Requests\Api\User\Profile;

use App\Helpers\ApiResponse;
use App\Rules\CheckTagExistsRule;
use App\Rules\MinAgeRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileApiRequest extends FormRequest
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
        $userId = Auth::guard('api')->user()->id;
        return [
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'gender' => ['required', Rule::in(1, 2)],
            'birthday' => ['required', new MinAgeRule()],
            'phone_number' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'tags_id' => ['required', new CheckTagExistsRule()],
            'tags_id.*' => 'exists:tags,id',
            'username' => ['nullable', 'string', 'alpha_dash', 'min:3', 'max:20', 'regex:/^[a-zA-Z][a-zA-Z0-9_-]*$/', 'not_regex:/\s/', Rule::unique('users', 'username')->ignore($userId)],
            'image' => ['nullable', 'image', 'max:2048'],

        ];
    }

    public function messages()
    {
        return [
            'gender.required' => __('validation.api.gender-required'),
            'gender.in' => __('validation.api.gender-in'),
            'birthday.required' => __('validation.api.birthday-required'),
            'birthday.min_age' => __('validation.api.birthday-min-age'),
            'tags_id.required' => __('validation.api.tags-id-required'),
            'tags_id.*.exists' => __('validation.api.tags-id-exists'),
            'username.alpha_dash' => __('validation.api.username-alpha_dash'),
            'username.min' => __('validation.api.username-min'),
            'username.max' => __('validation.api.username-max'),
            'username.regex' => __('validation.api.username-regex'),
            'username.not_regex' => __('validation.api.username-not-regex'),
            'username.unique' => __('validation.api.username-unique'),
            'image.image' => __('validation.api.image-image'),
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
