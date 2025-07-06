<?php

namespace App\Http\Requests\Api\User\Property;

use App\Rules\CheckIfPeriodExistsInPropertyRule;
use App\Rules\CheckIfPeriodMonthYearExistsInPropertyRule;
use App\Rules\CheckIfPropertyActiveRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckAvailableMonthRequest extends FormRequest
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
            'month' => $this->route('month'),
            'year' => $this->route('year'),
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
            'property_slug' => ['bail', 'required', 'string', 'exists:properties,slug', new CheckIfPropertyActiveRule()],
            'month' => ['bail', 'required', new CheckIfPeriodMonthYearExistsInPropertyRule()],
            'year' => ['required'],
        ];
    }
}
