<?php

namespace App\Http\Requests\Api\User\Warning;

use App\Rules\CheckIfUserActiveRule;
use App\Rules\CheckIfUserSendWarningRule;
use App\Rules\CheckIfUserSendWarningToHimselfRule;
use Illuminate\Foundation\Http\FormRequest;

class WarningRequest extends FormRequest
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
            'user_slug' => ['required', 'string', 'exists:users,slug', new CheckIfUserActiveRule(), new CheckIfUserSendWarningRule(), new CheckIfUserSendWarningToHimselfRule()],
            'reason' => ['required', 'string'],
            'images' => ['nullable'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico,svgz|max:10000'],
        ];
    }
}
