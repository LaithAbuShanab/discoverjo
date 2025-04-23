<?php

namespace App\Http\Requests\Api\User\Plan;

use App\Helpers\ApiResponse;
use Illuminate\Support\Str;
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

    protected function prepareForValidation()
    {
        // If request is not JSON or body cannot be parsed, throw an error early
        if (!$this->isJson() || is_null(json_decode($this->getContent(), true))) {
            throw new HttpResponseException(
                ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, ['Invalid JSON format.'])
            );
        }
        $this->merge([
            'plan_slug' => request('plan_slug'),
        ]);
    }


    // public function rules(): array
    // {
    //     $rules = [
    //         'name' => 'required',
    //         'description' => 'required',
    //         'days' => 'required|array',
    //     ];

    //     foreach ($this->json('days') as $dayIndex => $day) {
    //         foreach ($day['activities'] as $activityIndex => $activity) {
    //             $activityRule = "required|date_format:H:i";
    //             if ($activityIndex > 0) {
    //                 $previousEndTime = $this->json("days.$dayIndex.activities." . ($activityIndex - 1) . ".end_time");
    //                 $activityRule .= "|after:$previousEndTime";
    //             }
    //             $rules["days.$dayIndex.activities.$activityIndex.name"] = "required";
    //             $rules["days.$dayIndex.activities.$activityIndex.start_time"] = $activityRule;
    //             $rules["days.$dayIndex.activities.$activityIndex.end_time"] = $activityRule . "|after:days.$dayIndex.activities.$activityIndex.start_time";
    //             $rules["days.$dayIndex.activities.$activityIndex.place_id"] = "required|exists:places,id";
    //             $rules["days.$dayIndex.activities.$activityIndex.note"] = "max:255";
    //         }
    //     }

    //     return $rules;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan_slug' => ['required', 'exists:plans,slug'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'days' => ['required', 'array', 'min:1'],
            'days.*.activities' => ['required', 'array', 'min:1'],
            'days.*.activities.*.name' => ['required', 'string', 'max:255'],
            'days.*.activities.*.start_time' => ['required', 'date_format:H:i'],
            'days.*.activities.*.end_time' => ['required', 'date_format:H:i'],
            'days.*.activities.*.place_slug' => ['required', 'string', 'exists:places,slug'],
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
                            "days.$dayIndex.activities.$activityIndex.end_time_custom",
                            ''
                        );
                    }

                    // Validate sequential activities times
                    if ($previousEndTime && strtotime($startTime) < strtotime($previousEndTime)) {
                        $validator->errors()->add(
                            "days.$dayIndex.activities.$activityIndex.start_time_custom",
                            ''
                        );
                    }

                    $previousEndTime = $endTime;
                }
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = collect($validator->errors()->messages())->map(function ($messages, $field) {
            if (preg_match('/\.(\w+)$/', $field, $matches)) {
                $attributeName = $matches[1];
            } else {
                $attributeName = $field;
            }

            preg_match_all('/\d+/', $field, $indexes);
            $day = $indexes[0][0] ?? null;
            $activity = $indexes[0][1] ?? null;

            if (Str::contains($field, 'days')) {
                $messageKey = "validation.api.{$attributeName}_plan_error";
                $messageData = [
                    'day' => $day + 1,
                    'activity' => $activity + 1,
                ];
            } else {
                $messageKey = "validation.api.{$attributeName}_plan_error_main";
                $messageData = [];
            }

            return __($messageKey, $messageData);
        })->values();

        throw new HttpResponseException(
            ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors->toArray())
        );
    }
}
