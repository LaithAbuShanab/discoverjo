<?php

namespace App\Http\Requests\Api\User\Plan;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfPlanBelongsToUser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class UpdatePlanApiRequest extends FormRequest
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
        $rules = [
            'plan_id'=>['required','exists:plans,id',new CheckIfPlanBelongsToUser()],
            'name' => 'required',
            'description' => 'required',
            'days' => 'required|array',
        ];

        foreach ($this->json('days') as $dayIndex => $day) {
            foreach ($day['activities'] as $activityIndex => $activity) {
                $activityRule = "required|date_format:H:i";
                if ($activityIndex > 0) {
                    $previousEndTime = $this->json("days.$dayIndex.activities." . ($activityIndex - 1) . ".end_time");
                    $activityRule .= "|after:$previousEndTime";
                }
                $rules["days.$dayIndex.activities.$activityIndex.name"] = "required";
                $rules["days.$dayIndex.activities.$activityIndex.start_time"] = $activityRule;
                $rules["days.$dayIndex.activities.$activityIndex.end_time"] = $activityRule . "|after:days.$dayIndex.activities.$activityIndex.start_time";
                $rules["days.$dayIndex.activities.$activityIndex.place_id"] = "required|exists:places,id";
                $rules["days.$dayIndex.activities.$activityIndex.note"] = "max:255";
            }
        }

        return $rules;
    }


    public function messages()
    {
        return [
            'plan_id.required' => __('validation.api.plan-id-required'),
            'plan_id.exists' => __('validation.api.plan-id-exists'),
            'name.required' => __('validation.api.name-required'),
            'name.string' => __('validation.api.name-string'),
            'name.max' => __('validation.api.name-max'),
            'description.required' => __('validation.api.description-required'),
            'description.string' => __('validation.api.description-string'),
            'days.required' => __('validation.api.days-required'),
            'days.array' => __('validation.api.days-array'),
            'days.*.activities.required' => __('validation.api.activities-required'),
            'days.*.activities.array' => __('validation.api.activities-array'),
            'days.*.activities.*.name.required' => __('validation.api.activity-name-required'),
            'days.*.activities.*.start_time.required' => __('validation.api.activity-start-time-required'),
            'days.*.activities.*.start_time.date_format' => __('validation.api.activity-start-time-format'),
            'days.*.activities.*.end_time.required' => __('validation.api.activity-end-time-required'),
            'days.*.activities.*.end_time.date_format' => __('validation.api.activity-end-time-format'),
            'days.*.activities.*.end_time.after' => __('validation.api.activity-end-time-after'),
            'days.*.activities.*.place_id.required' => __('validation.api.activity-place-id-required'),
            'days.*.activities.*.place_id.exists' => __('validation.api.activity-place-id-exists'),
            'days.*.activities.*.note.max' => __('validation.api.activity-note-max'),
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
        $errors = [];

        foreach ($validator->errors()->messages() as $field => $messages) {
            //dd($field);
            //list($dayIndex, $activityIndex, $attribute) = explode('.', $field, 3);
            $errors[] = "$field: $messages[0]";
        }

        throw new HttpResponseException(
            ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors)
        );
    }
}
