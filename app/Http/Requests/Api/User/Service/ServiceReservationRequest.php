<?php

namespace App\Http\Requests\Api\User\Service;

use App\Rules\CheckIfAgePriceBelongToService;
use App\Rules\CheckIfValidReservationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceReservationRequest extends FormRequest
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
            'service_slug' => ['bail', 'required', 'string', 'exists:services,slug', new CheckIfValidReservationRule()],
            'date' => ['required', 'date', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'contact_info' => ['required', 'string', 'regex:/^\+?[0-9\s\-]{7,15}$/'],
            'reservations' => ['required', 'array', 'min:1'],
            'reservations.*.reservation_detail' => ['required', 'integer', Rule::in([1, 2])],
            'reservations.*.quantity' => ['required', 'integer', 'min:1'],
            'reservations.*.price_age_id' => [
                'nullable',
                'integer',
                'required_if:reservations.*.reservation_detail,2',
                'exists:service_price_ages,id',
                new CheckIfAgePriceBelongToService()

            ],
        ];
    }
}
