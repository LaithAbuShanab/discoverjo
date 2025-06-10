<?php

namespace App\Http\Requests\Api\User\GuideTrip;

use App\Rules\CheckIfGuideActiveRule;
use App\Rules\CheckIfTheIdIsGuideRule;
use App\Rules\CheckIfUserActiveRule;
use Illuminate\Foundation\Http\FormRequest;

class FilterGuideTripRequest extends FormRequest
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
            'region' => 'bail|nullable|string|exists:regions,slug',
            'guide_slug'=>['bail','nullable','string','exists:users,slug',new CheckIfTheIdIsGuideRule(),new CheckIfUserActiveRule()],
        ];
    }
}
