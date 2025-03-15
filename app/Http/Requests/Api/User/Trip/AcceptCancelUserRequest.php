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
            'user_slug' => ['bail', 'required', 'string', 'exists:users,slug', new CheckIfTheUserIsTripAnotherTrip],
            'trip_slug' => ['bail', 'required', 'string', 'exists:trips,slug', new CheckUserTripStatus]
        ];
    }

    public function messages()
    {
        return [
            'user_slug.required' => __('validation.api.user_slug_required'),
            'user_slug.string' => __('validation.api.user_slug_string'),
            'user_slug.exists' => __('validation.api.user_slug_exists'),
            'trip_slug.required' => __('validation.api.trip_slug_required'),
            'trip_slug.string' => __('validation.api.trip_slug_string'),
            'trip_slug.exists' => __('validation.api.trip_slug_exists'),
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
