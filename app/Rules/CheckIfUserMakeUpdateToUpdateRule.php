<?php

namespace App\Rules;

use App\Models\RatingGuide;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfUserMakeUpdateToUpdateRule implements ValidationRule, DataAwareRule
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
        if (!RatingGuide::where('guide_id', $guide->id)->where('user_id', $userId)->exists()) {
            $fail(__('validation.api.you_did_not_make_review_for_this_guide_to_update'));
        }
    }
}
