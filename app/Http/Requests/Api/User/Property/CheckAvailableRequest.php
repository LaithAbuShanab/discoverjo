<?php

namespace App\Http\Requests\Api\User\Property;

use App\Rules\CheckIfPeriodExistsInPropertyRule;
use App\Rules\CheckIfPeriodMonthYearExistsInPropertyRule;
use App\Rules\CheckIfPropertyActiveRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckAvailableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'property_slug' => $this->route('property_slug'),
            'period_type' => $this->route('period_type'),
        ]);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_slug'=>['required','string','exists:properties,slug',new CheckIfPropertyActiveRule()],
            'period_type'=>['required','string',Rule::in(['morning', 'evening','day']), new CheckIfPeriodExistsInPropertyRule()],
        ];
    }
}
