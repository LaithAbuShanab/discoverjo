<?php

namespace App\Http\Requests\Api\User\Plan;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class CreatePlanApiRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'days' => ['required', 'array', 'min:1'],
            'days.*.activities' => ['required', 'array', 'min:1'],
            'days.*.activities.*.name' => ['required', 'string', 'max:255'],
            'days.*.activities.*.start_time' => ['required', 'date_format:H:i'],
            'days.*.activities.*.end_time' => ['required', 'date_format:H:i'],
            'days.*.activities.*.place_id' => ['required', 'integer', 'exists:places,id'],
            'days.*.activities.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Additional validation for nested time logic (end_time after start_time and sequential activities).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $days = $this->input('days', []);

            foreach ($days as $dayIndex => $day) {
                $previousEndTime = null;

                foreach ($day['activities'] as $activityIndex => $activity) {
                    $startTime = $activity['start_time'];
                    $endTime = $activity['end_time'];

                    // Validate that end_time is after start_time
                    if (strtotime($endTime) <= strtotime($startTime)) {
                        $validator->errors()->add(
                            "days.$dayIndex.activities.$activityIndex.end_time",
                            __('validation.api.activity-end-time-after-start')
                        );
                    }

                    // Validate sequential activities times
                    if ($previousEndTime && strtotime($startTime) < strtotime($previousEndTime)) {
                        $validator->errors()->add(
                            "days.$dayIndex.activities.$activityIndex.start_time",
                            __('validation.api.activity-start-time-sequence')
                        );
                    }

                    $previousEndTime = $endTime;
                }
            }
        });
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.api.name-required'),
            'name.string' => __('validation.api.name-string'),
            'name.max' => __('validation.api.name-max'),
            'description.required' => __('validation.api.description-required'),
            'description.string' => __('validation.api.description-string'),
            'description.max' => __('validation.api.description-max'),
            'days.required' => __('validation.api.days-required'),
            'days.array' => __('validation.api.days-array'),
            'days.*.activities.required' => __('validation.api.activities-required'),
            'days.*.activities.array' => __('validation.api.activities-array'),
            'days.*.activities.*.name.required' => __('validation.api.activity-name-required'),
            'days.*.activities.*.name.string' => __('validation.api.activity-name-string'),
            'days.*.activities.*.name.max' => __('validation.api.activity-name-max'),
            'days.*.activities.*.start_time.required' => __('validation.api.activity-start-time-required'),
            'days.*.activities.*.start_time.date_format' => __('validation.api.activity-start-time-format'),
            'days.*.activities.*.end_time.required' => __('validation.api.activity-end-time-required'),
            'days.*.activities.*.end_time.date_format' => __('validation.api.activity-end-time-format'),
            'days.*.activities.*.place_id.required' => __('validation.api.activity-place-id-required'),
            'days.*.activities.*.place_id.exists' => __('validation.api.activity-place-id-exists'),
            'days.*.activities.*.note.max' => __('validation.api.activity-note-max'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = collect($validator->errors()->messages())->map(function ($messages, $field) {
            return "$field: " . $messages[0];
        })->values();

        throw new HttpResponseException(
            ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors->toArray())
        );
    }
}
