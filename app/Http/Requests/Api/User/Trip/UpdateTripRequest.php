<?php

namespace App\Http\Requests\Api\User\Trip;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfCanMakeTripRule;
use App\Rules\CheckTagExistsRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateTripRequest extends FormRequest
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
            'place_id' => ['nullable', 'integer', 'exists:places,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'age_min' => [
                Rule::requiredIf(function () {
                    return request()->filled('age_max');
                }),
                'nullable',
                'integer',
            ],
            'age_max' => [
                Rule::requiredIf(function () {
                    return request()->filled('age_min');
                }),
                'nullable',
                'integer',
                'gte:age_min',
            ],
            'gender' => ['nullable'],
            'date' => [
                Rule::requiredIf(function () {
                    return request()->filled('time');
                }),
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    if ($value && Carbon::parse($value)->isPast()) {
                        $fail('The ' . $attribute . ' must be a date in the future.');
                    }
                },
            ],
            'time' => [
                'nullable',
                'date_format:H:i:s',
                Rule::requiredIf(function () {
                    return request()->filled('date');
                }),
                Rule::when(request()->filled('date'), [new CheckIfCanMakeTripRule]),
            ],

            'attendance_number' => ['nullable', 'integer', 'min:1'],
            'tags' => ['nullable',new CheckTagExistsRule()],
        ];
    }

    public function messages()
    {
        return [
            'place_id.integer' => __('validation.api.place_id-integer'),
            'place_id.exists' => __('validation.api.place_id-exists'),
            'name.string' => __('validation.api.name-string'),
            'name.max' => __('validation.api.name-max'),
            'description.string' => __('validation.api.description-string'),
            'cost.numeric' => __('validation.api.cost-numeric'),
            'cost.min' => __('validation.api.cost-min'),
            'age_min.integer' => __('validation.api.age_min-integer'),
            'age_max.integer' => __('validation.api.age_max-integer'),
            'age_max.gte' => __('validation.api.age_max-gte'),
            'gender.nullable' => __('validation.api.gender-nullable'),
            'date.date' => __('validation.api.date-date'),
            'date.required_if' => __('validation.api.date-required_if'),
            'date.custom' => __('validation.api.date-custom'),
            'time.date_format' => __('validation.api.time-date_format'),
            'time.required_if' => __('validation.api.time-required_if'),
            'attendance_number.integer' => __('validation.api.attendance_number-integer'),
            'attendance_number.min' => __('validation.api.attendance_number-min'),
            'tags.nullable' => __('validation.api.tags-nullable'),
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors));
    }

}
