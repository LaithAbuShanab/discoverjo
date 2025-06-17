<?php

namespace App\Http\Requests\Api\User\Service;

use App\Rules\CheckIfDateAcceptableForService;
use App\Rules\CheckIfServiceActiveRuel;
use Illuminate\Foundation\Http\FormRequest;

class BookingDateRequest extends FormRequest
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
            'service_slug' => $this->route('service_slug'),
            'date' => $this->route('date'),
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
            'service_slug' => ['bail', 'required', 'string', 'exists:services,slug', new CheckIfServiceActiveRuel()],
            'date' => ['bail', 'required', 'date_format:Y-m-d', 'after_or_equal:today', new CheckIfDateAcceptableForService()],
        ];
    }
}
