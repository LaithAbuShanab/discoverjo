<?php

namespace App\Http\Requests\Api\User\GuideTrip;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CheckIfFullNameSubscriptionExistsRule;

class UpdateSingleSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Adjust authorization logic as necessary
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
            'first_name'   => ['required', 'string', 'max:255', new CheckIfFullNameSubscriptionExistsRule()],
            'last_name'    => ['required', 'string', 'max:255'],
            'age'          => ['required', 'integer', 'min:0'],
            'phone_number' => ['required', 'string', 'max:20'],
        ];
    }
}
