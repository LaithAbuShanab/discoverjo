<?php

namespace App\Http\Requests\Api\User\GuideTrip;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use App\Helpers\ApiResponse;

class CreateGuideTripUserRequest extends FormRequest
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
    public function rules()
    {
        return [
            'subscribers' => 'required|array',
            'subscribers.*.first_name' => 'required|string|max:255',
            'subscribers.*.last_name' => 'required|string|max:255',
            'subscribers.*.age' => 'required|integer|min:0',
            'subscribers.*.phone_number' => ['required', 'string', 'max:20'],
        ];
    }


    public function messages()
    {
        return [
            'subscribers.required' => __('validation.api.subscribers-required'),
            'subscribers.array' => __('validation.api.subscribers-array'),

            'subscribers.*.first_name.required' => __('validation.api.subscriber-first-name-required'),
            'subscribers.*.first_name.string' => __('validation.api.subscriber-first-name-string'),
            'subscribers.*.first_name.max' => __('validation.api.subscriber-first-name-max'),

            'subscribers.*.last_name.required' => __('validation.api.subscriber-last-name-required'),
            'subscribers.*.last_name.string' => __('validation.api.subscriber-last-name-string'),
            'subscribers.*.last_name.max' => __('validation.api.subscriber-last-name-max'),

            'subscribers.*.age.required' => __('validation.api.subscriber-age-required'),
            'subscribers.*.age.integer' => __('validation.api.subscriber-age-integer'),
            'subscribers.*.age.min' => __('validation.api.subscriber-age-min'),

            'subscribers.*.phone_number.required' => __('validation.api.subscriber-phone-number-required'),
            'subscribers.*.phone_number.string' => __('validation.api.subscriber-phone-number-string'),
            'subscribers.*.phone_number.max' => __('validation.api.subscriber-phone-number-max'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors));
    }
}
