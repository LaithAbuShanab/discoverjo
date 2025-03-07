<?php

namespace App\Http\Requests\Api\User\Trip;

use App\Helpers\ApiResponse;
use App\Rules\CheckUserTripStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class AcceptCancelInvitationsRequest extends FormRequest
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
            'trip_id' => [
                'bail', // Stops validation if any rule before it fails
                'required',
                'integer',
                'exists:trips,id',
                new CheckUserTripStatus, // Only runs if 'exists:trips,id' passes
            ],
        ];
    }


    public function messages()
    {
        return [
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
