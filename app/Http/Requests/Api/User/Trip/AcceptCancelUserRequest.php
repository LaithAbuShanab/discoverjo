<?php

namespace App\Http\Requests\Api\User\Trip;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfTheUserIsTripAnotherTrip;
use App\Rules\CheckUserTripStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class AcceptCancelUserRequest extends FormRequest
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
            'user_id' => ['required','integer','exists:users_trips,user_id', new CheckIfTheUserIsTripAnotherTrip],
            'trip_id' => ['required', 'integer', 'exists:trips,id', new CheckUserTripStatus]
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => __('validation.api.user_id-required'),
            'user_id.integer' => __('validation.api.user_id-integer'),
            'user_id.exists' => __('validation.api.user_id-exists'),
            'trip_id.required' => __('validation.api.trip_id-required'),
            'trip_id.integer' => __('validation.api.trip_id-integer'),
            'trip_id.exists' => __('validation.api.trip_id-exists'),
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
