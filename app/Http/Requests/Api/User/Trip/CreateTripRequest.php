<?php

namespace App\Http\Requests\Api\User\Trip;

use App\Helpers\ApiResponse;
use App\Rules\ActivePlaceRule;
use App\Rules\CheckIfCanMakeTripRule;
use App\Rules\CheckIfFollowersExistenceRule;
use App\Rules\CheckTagExistsRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CreateTripRequest extends FormRequest
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
            'trip_type' => ['required', 'string', Rule::in(['0', '1', '2'])],
            'place_slug' => ['required', 'integer', 'exists:places,id',new ActivePlaceRule()],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'cost' => ['required', 'numeric', 'min:0'],
            'age_min' => [
                'required_if:trip_type,0,1',
                'integer',
                'nullable'
            ],
            'age_max' => [
                'required_if:trip_type,0,1',
                'integer',
                'nullable'
            ],
            'gender' => ['required'],
            'date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->isPast()) {
                        $fail(__('app.trip-date-cannot-be-in-the-past', ['date' => $value]));
                    }
                },
            ],
            'time' => [
                'required', 'date_format:H:i:s', new CheckIfCanMakeTripRule,
            ],
            'attendance_number' => [
                'required_if:trip_type,0,1',
                'integer',
                'min:1',
                'nullable'
            ],
            'tags' => ['required', new CheckTagExistsRule()],
            'tags.*' => 'exists:tags,id',
            'users' => [
                'required_if:trip_type,2', new CheckIfFollowersExistenceRule()
            ]
        ];
    }


    public function messages()
    {
        return [
            'trip_type.required' => __('validation.api.trip_type-required'),
            'trip_type.string' => __('validation.api.trip_type-string'),
            'trip_type.in' => __('validation.api.trip_type-in'),
            'place_id.required' => __('validation.api.place_id-required'),
            'place_id.integer' => __('validation.api.place_id-integer'),
            'place_id.exists' => __('validation.api.place_id-exists'),
            'name.required' => __('validation.api.name-required'),
            'name.string' => __('validation.api.name-string'),
            'name.max' => __('validation.api.name-max'),
            'description.required' => __('validation.api.description-required'),
            'description.string' => __('validation.api.description-string'),
            'cost.required' => __('validation.api.cost-required'),
            'cost.numeric' => __('validation.api.cost-numeric'),
            'cost.min' => __('validation.api.cost-min'),
            'age_min.required_if' => __('validation.api.age_min-required_if'),
            'age_min.integer' => __('validation.api.age_min-integer'),
            'age_max.required_if' => __('validation.api.age_max-required_if'),
            'age_max.integer' => __('validation.api.age_max-integer'),
            'gender.required' => __('validation.api.gender-required'),
            'date.required' => __('validation.api.date-required'),
            'date.date' => __('validation.api.date-date'),
            'date.custom' => __('validation.api.date-custom'),
            'time.required' => __('validation.api.time-required'),
            'time.date_format' => __('validation.api.time-date_format'),
            'attendance_number.required_if' => __('validation.api.attendance_number-required_if'),
            'attendance_number.integer' => __('validation.api.attendance_number-integer'),
            'attendance_number.min' => __('validation.api.attendance_number-min'),
            'tags.required' => __('validation.api.tags-required'),
            'tags.*.exists' => __('validation.api.tags-exists'),
            'users.required_if' => __('validation.api.users-required_if'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors));
    }
}
