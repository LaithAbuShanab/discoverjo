<?php

namespace App\Http\Requests\Api\User\Property;

use App\Rules\CheckIfDateExistsInPropertyAndAvailableRule;
use App\Rules\CheckIfPeriodExistsInPropertyRule;
use App\Rules\CheckIfPropertyActiveRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MakeReservationRequest extends FormRequest
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
            'property_slug' => ['required', 'string', 'exists:properties,slug', new CheckIfPropertyActiveRule()],
            'period_type' => ['required', 'string', Rule::in(['morning', 'evening', 'day']), new CheckIfPeriodExistsInPropertyRule()],
            'check_in' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:check_out', new CheckIfDateExistsInPropertyAndAvailableRule()],
            'check_out' => ['required', 'date', 'after_or_equal:check_in'],
            'contact_info' => ['required', 'string', 'regex:/^\+?[0-9\s\-]{7,15}$/'],
        ];
    }
}
