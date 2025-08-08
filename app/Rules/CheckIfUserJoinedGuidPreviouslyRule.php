<?php

namespace App\Rules;

use App\Models\GuideTripUser;
use App\Models\RatingGuide;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfUserJoinedGuidPreviouslyRule implements DataAwareRule,  ValidationRule
{

    public $data;

    public function setData($data)
    {
        $this->data = $data;
        return $data;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $guideSlug = $this->data['guide_slug'];
        $guide = User::findBySlug($guideSlug);

        if (!$guide) return;
        $guideId = $guide->id;

        $hasParticipated = GuideTripUser::where('user_id', $userId)
            ->whereHas('guideTrip', function ($query) use ($guideId) {
                $query->where('guide_id', $guideId);
            })
            ->exists();

        if (!$hasParticipated) {
            $fail(__('validation.api.you_did_not_participate_in_any_of_guide_trip'));
            return;
        }

        if (RatingGuide::where('guide_id', $guideId)->where('user_id', $userId)->exists()) {
            $fail(__('validation.api.you_already_create_rating_for_this_guide'));
        }

        $hasParticipatedConfirmedAndFinished = GuideTripUser::query()
            ->where('user_id', $userId)
            ->where('status', 1)
            ->whereHas('guideTrip', function ($q) use ($guide) {
                $q->where('guide_id', $guide->id)
                    ->where('end_datetime', '<=', Carbon::now());
            })->exists();

        if (! $hasParticipatedConfirmedAndFinished) {
            $fail(__('validation.api.you_must_finish_a_confirmed_trip'));
            return;
        }
    }
}
