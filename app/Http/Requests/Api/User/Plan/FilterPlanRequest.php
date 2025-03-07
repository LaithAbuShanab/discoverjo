<?php

namespace App\Http\Requests\Api\User\Plan;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class FilterPlanRequest extends FormRequest
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
            'region_id'=>['nullable','exists:regions,id'],
            'number_of_days'=>'nullable',
        ];
    }

    public function messages()
    {
        return [
            'region_id.integer' => __('validation.api.region-id-integer'),
            'region_id.exists' => __('validation.api.region-id-exists'),
            'number_of_days.integer' => __('validation.api.number-of-days-integer'),
            'number_of_days.min' => __('validation.api.number-of-days-min'),
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        throw new HttpResponseException(
            ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors)
        );
    }
}
