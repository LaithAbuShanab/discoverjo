<?php

namespace App\Http\Requests\Api\User\Trip;

use App\Helpers\ApiResponse;
use App\Rules\ActivePlaceRule;
use App\Rules\CheckIfCanMakeTripRule;
use App\Rules\CheckIfFollowersExistenceRule;
use App\Rules\CheckTagExistsRule;
use App\Rules\CheckUserExistsRule;
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
            'trip_type' => ['required', 'integer', Rule::in(['0', '1', '2'])],
            'place_slug' => ['bail', 'required', 'string', 'exists:places,slug', new ActivePlaceRule()],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
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
            'gender' => ['required_if:trip_type,0,1'],
            'date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $now = Carbon::now('Asia/Riyadh');
                    $date = Carbon::createFromFormat('Y-m-d', $value, 'Asia/Riyadh');

                    if ($date->lt($now->copy()->startOfDay())) {
                        $fail(__('validation.api.date-cannot-be-in-the-past', ['date' => $value]));
                    }
                },
            ],
            'time' => [
                'required',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) {
                    $date = request()->date;
                    $time = $value;

                    if ($date) {
                        $now = Carbon::now('Asia/Riyadh');
                        $requestDate = Carbon::createFromFormat('Y-m-d', $date, 'Asia/Riyadh');

                        if ($requestDate->isSameDay($now)) {
                            $requestDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time, 'Asia/Riyadh');

                            if ($requestDateTime->lt($now)) {
                                $fail(__('validation.api.time-should-not-be-in-the-past'));
                            }
                        }
                    }
                },
                new CheckIfCanMakeTripRule,
            ],

            'attendance_number' => [
                'required_if:trip_type,0,1',
                'integer',
                'min:1',
                'nullable'
            ],
            'tags' => ['required', new CheckTagExistsRule()],
            'users' => [
                'required_if:trip_type,2',
                new CheckIfFollowersExistenceRule(),
                new CheckUserExistsRule(),
            ]
        ];
    }

    public function messages()
    {
        return [
            'trip_type.required' => __('validation.api.trip_type_required'),
            'trip_type.integer' => __('validation.api.trip_type_integer'),
            'trip_type.in' => __('validation.api.trip_type_in'),
            'place_slug.required' => __('validation.api.place_slug_required'),
            'place_slug.string' => __('validation.api.place_slug_string'),
            'place_slug.exists' => __('validation.api.place_slug_exists'),
            'name.required' => __('validation.api.name_required'),
            'name.string' => __('validation.api.name_string'),
            'name.max' => __('validation.api.name_max'),
            'description.required' => __('validation.api.description_required'),
            'description.string' => __('validation.api.description_string'),
            'cost.required' => __('validation.api.cost_required'),
            'cost.numeric' => __('validation.api.cost_numeric'),
            'cost.min' => __('validation.api.cost_min'),
            'age_min.required_if' => __('validation.api.age_min_required_if'),
            'age_min.integer' => __('validation.api.age_min_integer'),
            'age_max.required_if' => __('validation.api.age_max_required_if'),
            'age_max.integer' => __('validation.api.age_max_integer'),
            'gender.required' => __('validation.api.gender_required'),
            'date.required' => __('validation.api.date_required'),
            'date.date' => __('validation.api.date_date'),
            'date.custom' => __('validation.api.date_custom'),
            'time.required' => __('validation.api.time_required'),
            'time.date_format' => __('validation.api.time_date_format'),
            'attendance_number.required_if' => __('validation.api.attendance_number_required_if'),
            'attendance_number.integer' => __('validation.api.attendance_number_integer'),
            'attendance_number.min' => __('validation.api.attendance_number_min'),
            'tags.required' => __('validation.api.tags_required'),
            'tags.exists' => __('validation.api.tags_exists'),
            'users.required_if' => __('validation.api.users_required_if'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors));
    }
}
