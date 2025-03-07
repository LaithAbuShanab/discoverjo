<?php

namespace App\Http\Requests\Api\User\Follow;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfFollowerFollowingExistsRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CreateFollowRequest extends FormRequest
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
            'following_id' => ['required', 'exists:users,id', new CheckIfFollowerFollowingExistsRule()],
        ];
    }

    public function messages()
    {
        return [
            'following_id.required' => __('validation.api.following-id-is-required'),
            'following_id.exists' => __('validation.api.following-id-not-exists'),

            // Custom Rule CheckIfFollowerFollowingExistsRule
            'following_id.CheckIfFollowerFollowingExistsRule' => __('validation.api.follower-following-already-exists'),
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
