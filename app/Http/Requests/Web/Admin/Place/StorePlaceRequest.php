<?php


namespace App\Http\Requests\Web\Admin\Place;

use App\Rules\OpeningHoursRule;
use App\Rules\UniquePlaceRule;
use App\Validation\CheckPriceRule;
use App\Validation\CheckRankRule;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePlaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_en' => ['required', 'string', 'min:3', new UniquePlaceRule()],
            'name_ar' => 'required|string|min:3',
            'description_en' => 'required|string|min:3',
            'description_ar' => 'required|string|min:3',
            'address_en' => 'required|string|min:3',
            'address_ar' => 'required|string|min:3',
            'google_map_url' => 'required|url',
            'phone_number' => 'nullable|string',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'price_level' => 'nullable|numeric',
            'website' => 'nullable|url',
            'rating' => 'required|numeric',
            'total_user_rating' => 'required|numeric',
            'sub_category_id' => 'required|exists:categories,id',
            'region_id' => 'required|exists:regions,id',
            'business_status' => ['nullable',Rule::in([0,1,2,3])],
            'tags_id' => 'required|array',
            'tags_id.*' => 'exists:tags,id',
            'main_image' => 'image|mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico',
            'day_of_week' => 'nullable|array',
            'opening_hours' => ['nullable', new OpeningHoursRule()],
            'closing_hours' => ['nullable', new OpeningHoursRule()],
            'feature_id' => 'nullable',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
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
            'google_map_url.required' => __('validation.msg.google-map-url-required'),
            'google_map_url.url' => __('validation.msg.invalid-url'),
            'phone_number.required' => __('validation.msg.phone-number-required'),
            'longitude.required' => __('validation.msg.longitude-required'),
            'longitude.numeric' => __('validation.msg.invalid-longitude'),
            'latitude.required' => __('validation.msg.latitude-required'),
            'latitude.numeric' => __('validation.msg.invalid-latitude'),
            'price_level.required' => __('validation.msg.price-level-required'),
            'website.required' => __('validation.msg.website-required'),
            'website.url' => __('validation.msg.invalid-website-url'),
            'rating.required' => __('validation.msg.rating-required'),
            'rating.numeric' => __('validation.msg.invalid-rating'),
            'total_user_rating.required' => __('validation.msg.total-user-rating-required'),
            'total_user_rating.numeric' => __('validation.msg.invalid-total-user-rating'),
            'sub_category_id.required' => __('validation.msg.subcategory-required'),
            'sub_category_id.exists' => __('validation.msg.invalid-subcategory'),
            'region_id.required' => __('validation.msg.region-required'),
            'region_id.exists' => __('validation.msg.invalid-region'),
            'business_status.required' => __('validation.msg.business-status-required'),
            'tags.required' => __('validation.msg.tags-required'),
            'tags.array' => __('validation.msg.invalid-tags'),
            'tags.*.exists' => __('validation.msg.invalid-tag'),
            'main_image.required' => __('validation.msg.main-image-required'),
            'main_image.image' => __('validation.msg.invalid-image'),
            // 'main_image.mimes' => __('validation.msg.invalid-image-format'),
            'gallery_images.*.image' => __('validation.msg.invalid-gallery-image'),
            // 'gallery_images.*.mimes' => __('validation.msg.invalid-gallery-image-format'),
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
            'google_map_url' => __('validation.attributes.google-map-url'),
            'phone_number' => __('validation.attributes.phone-number'),
            'longitude' => __('validation.attributes.longitude'),
            'latitude' => __('validation.attributes.latitude'),
            'price_level' => __('validation.attributes.price-level'),
            'website' => __('validation.attributes.website'),
            'rating' => __('validation.attributes.rating'),
            'total_user_rating' => __('validation.attributes.total-user-rating'),
            'sub_category_id' => __('validation.attributes.sub-category-id'),
            'region_id' => __('validation.attributes.region-id'),
            'business_status' => __('validation.attributes.business-status'),
            'tags_id' => __('validation.attributes.tags-id'),
            'main_image' => __('validation.attributes.main-image'),
            'gallery_images' => __('validation.attributes.gallery-images'),
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
