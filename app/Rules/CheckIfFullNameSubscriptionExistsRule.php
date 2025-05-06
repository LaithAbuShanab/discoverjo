<?php

namespace App\Rules;

use App\Models\GuideTripUser;
use App\Models\Trip;
use App\Models\UsersTrip;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfFullNameSubscriptionExistsRule implements ValidationRule, DataAwareRule
{
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;

        $firstName = $this->data['first_name'] ?? '';
        $lastName = $this->data['last_name'] ?? '';
        $excludeId = request()->route('subscription_id') ?? null;

        $exists = GuideTripUser::where('user_id', $userId)
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->where('id', '!=', $excludeId)
            ->exists();

        if ($exists) {
            $fail('A subscription with this full name already exists.');
        }
    }

}
