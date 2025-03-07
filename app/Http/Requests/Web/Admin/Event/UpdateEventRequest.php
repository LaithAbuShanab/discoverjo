<?php

namespace App\Http\Requests\Web\Admin\Event;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEventRequest extends FormRequest
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
            'name_en' => 'required|string|min:3',
            'name_ar' => 'required|string|min:3',
            'description_en' => 'required|string|min:3',
            'description_ar' => 'required|string|min:3',
            'address_en' => 'required|string|min:3',
            'address_ar' => 'required|string|min:3',
            'link' => 'required|url',
            'price' => 'nullable|numeric',
            'region_id' => 'required|exists:regions,id',
            'status' => 'required|string',
            'organizers_id' => 'required|array',
            'organizers_id.*' => 'exists:organizers,id',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico,svgz',
            'start_datetime' => 'required|date|after_or_equal:today',
            'end_datetime' => 'required|date|after:start_datetime',
            'attendance_number'=>'nullable|numeric'
        ];
    }

    public function messages()
    {
        return [
            'name_en.required' => __('validation.msg.english-name-required'),
            'name_en.min' => __('validation.msg.english-name-min-characters'),
            'name_ar.required' => __('validation.msg.arabic-name-required'),
            'name_ar.min' => __('validation.msg.arabic-name-min-characters'),
            'description_en.required' => __('validation.msg.english-description-required'),
            'description_en.min' => __('validation.msg.english-description-min-characters'),
            'description_ar.required' => __('validation.msg.arabic-description-required'),
            'description_ar.min' => __('validation.msg.arabic-description-min-characters'),
            'address_en.required' => __('validation.msg.english-address-required'),
            'address_en.min' => __('validation.msg.english-address-min-characters'),
            'address_ar.required' => __('validation.msg.arabic-address-required'),
            'address_ar.min' => __('validation.msg.arabic-address-min-characters'),
            'link.required' => __('validation.msg.link-required'),
            'link.url' => __('validation.msg.invalid-url'),
            'region_id.required' => __('validation.msg.region-required'),
            'region_id.exists' => __('validation.msg.invalid-region'),
            'status.required' => __('validation.msg.status-required'),
            'start_datetime.required' => __('validation.msg.start_datetime-required'),
            'start_datetime.date' => __('validation.msg.start_datetime-date'),
            'start_datetime.after_or_equal' => __('validation.msg.start_datetime-after_or_equal'),
            'end_datetime.required' => __('validation.msg.end_datetime-required'),
            'end_datetime.date' => __('validation.msg.end_datetime-date'),
            'end_datetime.after' => __('validation.msg.end_datetime-after'),
            'image.required' => __('validation.msg.image-required'),
            'image.image' => __('validation.msg.invalid-image'),
            'image.mimes' => __('validation.msg.invalid-image-format'),
        ];
    }

    public function attributes()
    {
        return [
            'name_en' => __('validation.attributes.name-en'),
            'name_ar' => __('validation.attributes.name-ar'),
            'description_en' => __('validation.attributes.description-en'),
            'description_ar' => __('validation.attributes.description-ar'),
            'address_en' => __('validation.attributes.address-en'),
            'address_ar' => __('validation.attributes.address-ar'),
            'link' => __('validation.attributes.link'),
            'price' => __('validation.attributes.price'),
            'region_id' => __('validation.attributes.region-id'),
            'status' => __('validation.attributes.status'),
            'organizers_id' => __('validation.attributes.organizers_id'),
            'start_datetime' => __('validation.attributes.start_datetime'),
            'end_datetime' => __('validation.attributes.end_datetime'),
            'image' => __('validation.attributes.image'),
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
        $errors = $validator->errors()->all();
        foreach ($errors as $error) {
            Toastr::error($error, __('Error'));
        }
        throw new HttpResponseException(
            redirect()->back()->withInput()->withErrors($validator)
        );
    }
}
