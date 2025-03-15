<?php

namespace App\Rules;

use App\Models\Conversation;
use App\Models\GroupMember;
use App\Models\Trip;
use App\Models\User;
use App\Models\UsersTrip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckUserTripStatus implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = User::where('slug', request()->user_slug)->value('id');
        $tripId = Trip::where('slug', $value)->value('id');
        $userTrip = UsersTrip::where('user_id', $userId ?? Auth::guard('api')->user()->id)->where('trip_id', $tripId)->first();

        if ($userTrip) {
            if ($userTrip->status == '1') {
                $fail(__('validation.api.this-user-has-already-joined-this-trip'));
            }

            if ($userTrip->status == '2') {
                $fail(__('validation.api.this-user-has-been-rejected-from-this-trip'));
            }

            if ($userTrip->status == '3') {
                $fail(__('validation.api.this-user-has-left-this-trip'));
            }
        }

        // Check If User Are The Member In Conversation Group
        if($userId){
            $member = GroupMember::where('conversation_id', Trip::find($tripId)->conversation->id)->where('user_id', $userId ?? Auth::guard('api')->user()->id)->where('left_datetime', null)->exists();
            if ($member) {
                $fail(__('validation.api.this-user-has-already-joined-this-trip'));
            }
        }
    }
}
