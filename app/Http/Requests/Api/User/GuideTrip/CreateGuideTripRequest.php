<?php

namespace App\Http\Requests\Api\User\GuideTrip;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfGuideTripReflectRule;
use App\Rules\CheckIfGuideTripReflictRule;
use App\Rules\CheckIsGuideRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

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
            'name_en' => ['required', 'string', 'max:255',new CheckIsGuideRule()],
            'name_ar' => ['required', 'string', 'max:255'],
            'description_en' => ['required', 'string'],
            'description_ar' => ['required', 'string'],
            'main_price' => ['required', 'numeric', 'min:0'],
            'start_datetime' => [
                'required',
                'date',
                'date_format:Y-m-d H:i:s',
                function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->isPast()) {
                        $fail('The ' . $attribute . ' must be a date and time in the future.');
                    }
                },
                new CheckIfGuideTripReflectRule(request()->start_datetime, request()->end_datetime)],
            'end_datetime' => [
                'required',
                'date',
                'date_format:Y-m-d H:i:s',
                function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->isPast()) {
                        $fail('The ' . $attribute . ' must be a date and time in the future.');
                    }
                },
                function ($attribute, $value, $fail) {
                    if (request()->start_datetime && Carbon::parse($value)->lessThanOrEqualTo(Carbon::parse(request()->start_datetime))) {
                        $fail('The ' . $attribute . ' must be a date and time after the start date and time.');
                    }
                },],
            'max_attendance' => ['required', 'integer', 'min:1'],
            'gallery'=>['required'],
            'gallery.*' => ['file','mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico,svgz,mp4,mov,avi,mkv,flv,wmv','max:10000'],
            'activities' => ['required', 'string'],
            'price_include' => ['required', 'string'],
            'price_age' => ['nullable', 'string'],
            'assembly' => ['required', 'string'],
            'required_items' => ['nullable', 'string'],
            'is_trail' => ['nullable', 'boolean'],
            'trail' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            // Name
            'name_en.required' => 'The English name is required.',
            'name_en.string' => 'The English name must be a string.',
            'name_en.max' => 'The English name may not be greater than 255 characters.',
            'name_en.CheckIsGuideRule' => 'The English name must pass the guide check.',

            'name_ar.required' => 'The Arabic name is required.',
            'name_ar.string' => 'The Arabic name must be a string.',
            'name_ar.max' => 'The Arabic name may not be greater than 255 characters.',

            // Description
            'description_en.required' => 'The English description is required.',
            'description_en.string' => 'The English description must be a string.',

            'description_ar.required' => 'The Arabic description is required.',
            'description_ar.string' => 'The Arabic description must be a string.',

            // Main Price
            'main_price.required' => 'The main price is required.',
            'main_price.numeric' => 'The main price must be a number.',
            'main_price.min' => 'The main price must be at least 0.',

            // Date and Time
            'start_datetime.required' => 'The start date and time is required.',
            'start_datetime.date' => 'The start date and time must be a valid date.',
            'start_datetime.date_format' => 'The start date and time must be in the format Y-m-d H:i:s.',
            'start_datetime.future' => 'The start date and time must be a date and time in the future.',

            'end_datetime.required' => 'The end date and time is required.',
            'end_datetime.date' => 'The end date and time must be a valid date.',
            'end_datetime.date_format' => 'The end date and time must be in the format Y-m-d H:i:s.',
            'end_datetime.future' => 'The end date and time must be a date and time in the future.',
            'end_datetime.after' => 'The end date and time must be after the start date and time.',

            // Max Attendance
            'max_attendance.required' => 'The maximum attendance is required.',
            'max_attendance.integer' => 'The maximum attendance must be an integer.',
            'max_attendance.min' => 'The maximum attendance must be at least 1.',

            // Gallery
            'gallery.*.file' => 'Each gallery item must be a file.',
            'gallery.*.mimes' => 'Each gallery item must be a file of type: jpeg, png, jpg, gif, svg, webp, bmp, tiff, ico, svgz, mp4, mov, avi, mkv, flv, wmv.',

            // Activities
            'activities.required' => 'Activities are required.',
            'activities.string' => 'Activities must be a string.',

            // Price Include
            'price_include.required' => 'Price include is required.',
            'price_include.string' => 'Price include must be a string.',

            // Price Age
            'price_age.nullable' => 'Price age must be a string if present.',

            // Assembly
            'assembly.required' => 'Assembly is required.',
            'assembly.string' => 'Assembly must be a string.',

            // Required Items
            'required_items.nullable' => 'Required items must be a string if present.',

            // Is Trail
            'is_trail.nullable' => 'Is trail must be a boolean if present.',

            // Trail
            'trail.nullable' => 'Trail must be a string if present.',
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

            if ($this->input('is_trail')) {
                $this->validateJsonObject('trail', $validator, [
                    'min_duration_in_minute' => ['required', 'integer', 'min:0'],
                    'max_duration_in_minute' => ['required', 'integer', 'gt:min_duration_in_minute'],
                    'distance_in_meter' => ['required', 'numeric', 'min:0'],
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

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], \Illuminate\Http\Response::HTTP_BAD_REQUEST));
    }
}
