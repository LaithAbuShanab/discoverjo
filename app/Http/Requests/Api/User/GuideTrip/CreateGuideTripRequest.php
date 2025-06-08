<?php

namespace App\Http\Requests\Api\User\GuideTrip;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfGuideTripReflectRule;
use App\Rules\CheckIsGuideRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class CreateGuideTripRequest extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'name_en' => ['bail', 'required', 'string', 'max:255', new CheckIsGuideRule()],
            'name_ar' => ['required', 'string', 'max:255'],
            'description_en' => ['required', 'string'],
            'description_ar' => ['required', 'string'],
            'main_price' => ['required', 'numeric', 'min:0'],
            'start_datetime' => [
                'bail',
                'required',
                'date',
                'date_format:Y-m-d H:i:s',
                function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->isPast()) {
                        $fail(__('validation.api.start_datetime_future'));
                    }
                },
                new CheckIfGuideTripReflectRule(request()->start_datetime, request()->end_datetime)
            ],
            'end_datetime' => [
                'bail',
                'required',
                'date',
                'date_format:Y-m-d H:i:s',
                function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->isPast()) {
                        $fail(__('validation.api.end_datetime_future'));
                    }
                },
                function ($attribute, $value, $fail) {
                    if (request()->start_datetime && Carbon::parse($value)->lessThanOrEqualTo(Carbon::parse(request()->start_datetime))) {
                        $fail(__('validation.api.end_datetime_after_start_datetime'));
                    }
                },
            ],
            'max_attendance' => ['required', 'integer', 'min:1'],
            'gallery' => ['required'],
            'gallery.*' => ['file', 'mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico,svgz,mp4,mov,avi,mkv,flv,wmv', 'max:50000'],
            'activities' => ['required'],
            'price_include' => ['required'],
            'price_age' => ['nullable'],
            'assembly' => ['required'],
            'required_items' => ['nullable'],
            'is_trail' => ['nullable', 'boolean'],
            'trail' => ['nullable'],
            'main_image' => ['required', 'image', 'max:2048'],
            'payment_method' => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            // Name
            'name_en.required' => __('validation.api.name_en_required'),
            'name_en.string' => __('validation.api.name_en_string'),
            'name_en.max' => __('validation.api.name_en_max'),

            'name_ar.required' => __('validation.api.name_ar_required'),
            'name_ar.string' => __('validation.api.name_ar_string'),
            'name_ar.max' => __('validation.api.name_ar_max'),

            // Description
            'description_en.required' => __('validation.api.description_en_required'),
            'description_en.string' => __('validation.api.description_en_string'),

            'description_ar.required' => __('validation.api.description_ar_required'),
            'description_ar.string' => __('validation.api.description_ar_string'),

            // Main Price
            'main_price.required' => __('validation.api.main_price_required'),
            'main_price.numeric' => __('validation.api.main_price_numeric'),
            'main_price.min' => __('validation.api.main_price_min'),

            // Date and Time
            'start_datetime.required' => __('validation.api.start_datetime_required'),
            'start_datetime.date' => __('validation.api.start_datetime_date'),
            'start_datetime.date_format' => __('validation.api.start_datetime_format'),
            'start_datetime.future' => __('validation.api.start_datetime_future'),

            'end_datetime.required' => __('validation.api.end_datetime_required'),
            'end_datetime.date' => __('validation.api.end_datetime_date'),
            'end_datetime.date_format' => __('validation.api.end_datetime_format'),
            'end_datetime.future' => __('validation.api.end_datetime_future'),
            'end_datetime.after' => __('validation.api.end_datetime_after'),

            // Max Attendance
            'max_attendance.required' => __('validation.api.max_attendance_required'),
            'max_attendance.integer' => __('validation.api.max_attendance_integer'),
            'max_attendance.min' => __('validation.api.max_attendance_min'),

            // Gallery
            'gallery.*.file' => __('validation.api.gallery_file'),
            'gallery.*.mimes' => __('validation.api.gallery_mimes'),

            // Activities
            'activities.required' => __('validation.api.activities_required'),
            'activities.string' => __('validation.api.activities_string'),

            // Price Include
            'price_include.required' => __('validation.api.price_include_required'),
            'price_include.string' => __('validation.api.price_include_string'),

            // Price Age
            'price_age.nullable' => __('validation.api.price_age_nullable'),

            // Assembly
            'assembly.required' => __('validation.api.assembly_required'),
            'assembly.string' => __('validation.api.assembly_string'),

            // Required Items
            'required_items.nullable' => __('validation.api.required_items_nullable'),

            // Is Trail
            'is_trail.nullable' => __('validation.api.is_trail_nullable'),

            // Trail
            'trail.nullable' => __('validation.api.trail_nullable'),
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateJsonArray('activities', $validator, [
                'en' => ['required', 'string'],
                'ar' => ['required', 'string'],
            ]);

            $this->validateJsonArray('price_include', $validator, [
                'en' => ['required', 'string'],
                'ar' => ['required', 'string'],
            ]);

            $this->validateJsonArray('price_age', $validator, [
                'min_age' => ['required', 'integer', 'min:0'],
                'max_age' => ['required', 'integer', 'gt:min_age'],
                'cost' => ['required', 'numeric', 'min:0'],
            ]);

            $this->validateJsonArray('assembly', $validator, [
                'time' => ['required', 'date_format:H:i'],
                'place_en' => ['required', 'string'],
                'place_ar' => ['required', 'string'],
            ]);

            $this->validateJsonArray('required_items', $validator, [
                'en' => ['required', 'string'],
                'ar' => ['required', 'string'],
            ]);

            $this->validateJsonArray('payment_method', $validator, [
                'en' => ['required', 'string'],
                'ar' => ['required', 'string'],
            ]);

            if ($this->input('is_trail')) {
                $this->validateJsonObject('trail', $validator, [
                    'min_duration_in_minute' => ['required', 'numeric', 'min:0', 'max:999.99'], // decimal(5,2)
                    'max_duration_in_minute' => ['required', 'numeric', 'gt:min_duration_in_minute', 'max:999.99'], // decimal(5,2)
                    'distance_in_meter' => ['required', 'numeric', 'min:0', 'max:9999.99'], // decimal(6,2)
                    'difficulty' => ['required', 'integer', 'in:0,1,2,3'],

                ]);
            }
        });
    }

    protected function validateJsonArray($field, $validator, $rules)
    {
        $input = $this->input($field);

        if ($input !== null) {
            $data = json_decode($input, true);

            if (!is_array($data)) {
                $validator->errors()->add($field, 'The ' . $field . ' field must be a valid JSON array.');
                return;
            }

            foreach ($data as $index => $item) {
                $itemValidator = FacadesValidator::make($item, $rules);

                if ($itemValidator->fails()) {
                    foreach ($itemValidator->errors()->all() as $message) {
                        $validator->errors()->add($field . '.' . $index, $message);
                    }
                }
            }
        }
    }

    protected function validateJsonObject($field, $validator, $rules)
    {
        $input = $this->input($field);

        if ($input !== null) {
            $data = json_decode($input, true);

            if (!is_array($data)) {
                $validator->errors()->add($field, 'The ' . $field . ' field must be a valid JSON object.');
                return;
            }

            $itemValidator = FacadesValidator::make($data, $rules);

            if ($itemValidator->fails()) {
                foreach ($itemValidator->errors()->all() as $message) {
                    $validator->errors()->add($field, $message);
                }
            }
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors));
    }
}
