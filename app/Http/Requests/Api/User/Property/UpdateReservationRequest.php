<?php

namespace App\Http\Requests\Api\User\Property;

use App\Rules\CheckIfDateExistsInPropertyAndAvailableEditRule;
use App\Rules\CheckIfDateExistsInPropertyAndAvailableRule;
use App\Rules\CheckIfPeriodExistsInPropertyEditRule;
use App\Rules\CheckIfPeriodExistsInPropertyRule;
use App\Rules\CheckIfPropertyActiveRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationRequest extends FormRequest
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
            'reservation_id' => $this->route('id'),
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

            'check_in' => ['bail', 'required', 'date', 'after_or_equal:today', 'before_or_equal:check_out', new CheckIfDateExistsInPropertyAndAvailableEditRule()],
            'check_out' => ['bail', 'required', 'date', 'after_or_equal:check_in'],
            'contact_info' => ['required', 'string', 'regex:/^\+?[0-9\s\-]{7,15}$/'],
        ];
    }
}
