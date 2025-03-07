<?php

namespace App\Http\Requests\Api\User\GuideRating;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfTheIdIsGuideRule;
use App\Rules\CheckIfUserJoinedGuidPreviouslyRule;
use App\Rules\CheckIfUserMakeUpdateToUpdateRule;
use App\Rules\CheckIfUserNotGuideForRatingRule;
use App\Rules\IfUserCanMakeCommentInPostRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class UpdateGuideRatingRequest extends FormRequest
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
            'guide_id'=>['required','exists:users,id',new CheckIfTheIdIsGuideRule(),new CheckIfUserNotGuideForRatingRule(),new CheckIfUserMakeUpdateToUpdateRule()],
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
        ];
    }

    public function messages()
    {
        return [
            // Guide ID
            'guide_id.required' => __('validation.api.guide-id-is-required'),
            'guide_id.exists' => __('validation.api.guide-id-not-exists'),
            'guide_id.CheckIfUserMakeUpdateToUpdateRule' => __('validation.api.guide-update-not-allowed'),

            // Rating
            'rating.required' => __('validation.api.rating-is-required'),
            'rating.numeric' => __('validation.api.rating-must-be-numeric'),
            'rating.min' => __('validation.api.rating-min-value'),
            'rating.max' => __('validation.api.rating-max-value'),
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
