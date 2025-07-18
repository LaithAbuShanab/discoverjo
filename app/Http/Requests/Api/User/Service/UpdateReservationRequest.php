<?php

namespace App\Http\Requests\Api\User\Service;

use App\Models\ServiceReservation;
use App\Rules\CheckIfAgePriceBelongToService;
use App\Rules\CheckIfServiceReservationInThePast;
use App\Rules\CheckIfValidDateReservationUpdateRule;
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
        $reservationId = $this->route('id'); // or $this->route('reservation') if route name differs

        $reservation = ServiceReservation::find($reservationId);

        if ($reservation) {
            $this->merge([
                'date' => $reservation->date, // Now date is coming from DB
                'reservation_id' => $reservationId, // Optional if needed elsewhere
                'start_time' => \Carbon\Carbon::createFromFormat('H:i:s', $reservation->start_time)->format('H:i'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['bail', 'required', 'date', 'date_format:Y-m-d', new CheckIfValidDateReservationUpdateRule(),new CheckIfServiceReservationInThePast()],
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
