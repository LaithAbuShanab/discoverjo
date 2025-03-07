<?php

namespace App\Http\Requests\Api\User\Post;

use App\Helpers\ApiResponse;
use App\Models\Event;
use App\Models\GuideTrip;
use App\Models\Place;
use App\Models\Plan;
use App\Models\Trip;
use App\Models\Volunteering;
use App\Rules\CheckPostBelongToUser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UpdatePostApiRequest extends FormRequest
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
            'post_id' => [
                'required',
                'exists:posts,id',
                new CheckPostBelongToUser()
            ],

            'visitable_type' => [
                'required',
                Rule::in(['place', 'plan', 'trip', 'event', 'volunteering', 'guide_trip']),
            ],

            'visitable_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $type = $this->input('visitable_type');
                    $models = [
                        'place' => Place::class,
                        'plan' => Plan::class,
                        'trip' => Trip::class,
                        'event' => Event::class,
                        'volunteering' => Volunteering::class,
                        'guide_trip' => GuideTrip::class,
                    ];

                    if (!isset($models[$type]) || !$models[$type]::where('id', $value)->exists()) {
                        $fail(__('validation.api.selected-' . $type . '-does-not-exist'));
                    }
                }
            ],

            'content' => ['required', 'string'],

            'privacy' => ['required', Rule::in([0, 1, 2])],  // Ensuring it's an integer

            'media' => ['nullable', 'max:10000'] // Added file validation for better security
        ];
    }


    public function messages()
    {
        return [
            'post_id.required' => __('validation.api.post-id-required'),
            'post_id.exists' => __('validation.api.post-id-exists'),
            'post_id.custom' => __('validation.api.post-id-custom'),
            'visitable_type.required' => __('validation.api.visitable-type-required'),
            'visitable_type.in' => __('validation.api.visitable-type-in'),
            'visitable_id.required' => __('validation.api.visitable-id-required'),
            'visitable_id.custom' => __('validation.api.visitable-id-custom'),
            'content.required' => __('validation.api.content-required'),
            'content.string' => __('validation.api.content-string'),
            'privacy.required' => __('validation.api.privacy-required'),
            'privacy.in' => __('validation.api.privacy-in'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(
            ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors)
        );
    }
}
