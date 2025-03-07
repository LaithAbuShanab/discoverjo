<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.api.name-required'),
            'name.string' => __('validation.api.name-string'),
            'name.max' => __('validation.api.name-max'),

            'email.required' => __('validation.api.email-required'),
            'email.string' => __('validation.api.email-string'),
            'email.lowercase' => __('validation.api.email-lowercase'),
            'email.email' => __('validation.api.email-email'),
            'email.max' => __('validation.api.email-max'),
            'email.unique' => __('validation.api.email-unique'),
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
