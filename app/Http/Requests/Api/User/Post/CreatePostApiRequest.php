<?php

namespace App\Http\Requests\Api\User\Post;

use App\Helpers\ApiResponse;
use App\Models\Event;
use App\Models\GuideTrip;
use App\Models\Place;
use App\Models\Plan;
use App\Models\Trip;
use App\Models\Volunteering;
use App\Rules\ActivePlaceRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CreatePostApiRequest extends FormRequest
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
            'visitable_type' => [
                'required',
                Rule::in(['place', 'plan', 'event', 'volunteering'])
            ],
            'visitable_slug' => [
                'required',
                function ($attribute, $value, $fail) {
                    $type = $this->input('visitable_type');
                    $models = [
                        'place' => Place::class,
                        'plan' => Plan::class,
                        'event' => Event::class,
                        'volunteering' => Volunteering::class,
                    ];

                    if (isset($models[$type]) && !$models[$type]::findBySlug( $value)) {
                        $fail(__('validation.api.selected-' . $type . '-does-not-exist'));
                        return;
                    }
                    if($type == 'place'){
                        if(!$models[$type]::findBySlug( $value)->status){
                            $fail(__('validation.api.the-selected-place-is-not-active'));
                        }
                    }
                }
            ],
            'content' => ['required', 'string'],
            'privacy' => ['required', Rule::in([0, 1, 2])],  // Ensure it's an integer
            'media' => ['nullable'],
            'media.*'=>['file','mimetypes:image/jpeg,image/png,image/jpg,image/gif,audio/mpeg,audio/wav,video/mp4,video/quicktime,video/x-msvideo','max:10240']
        ];
    }

    public function messages()
    {
        return [
            'visitable_type.required' => __('validation.api.visitable-type-required'),
            'visitable_type.in' => __('validation.api.visitable-type-in'),
            'visitable_id.required' => __('validation.api.visitable-id-required'),
            'visitable_id.exists' => __('validation.api.visitable-id-exists'),
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
