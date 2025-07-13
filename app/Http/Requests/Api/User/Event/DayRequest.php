<?php

namespace App\Http\Requests\Api\User\Event;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfHasInjectionBasedTimeRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class DayRequest extends FormRequest
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
            'date' => ['bail', 'required', 'date_format:Y-m-d'
//                , new CheckIfHasInjectionBasedTimeRule()
            ],
        ];
    }
    public function messages()
    {
        return [
            'date.required' => __('validation.api.date-is-required'),
            'date.date' => __('validation.api.date-invalid-format'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        throw new HttpResponseException(
            ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $errors)
        );
    }
}
