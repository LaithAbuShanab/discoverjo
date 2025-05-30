<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfPastEventOrVolunteering implements ValidationRule
{
    protected $reviewable_type;

    public function __construct($reviewable_type)
    {
        $this->reviewable_type = $reviewable_type;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $review = $this->reviewable_type::find($value);
        if(!$review) return;
        $date=$review->start_datetime;
        $now = now()->setTimezone('Asia/Riyadh');
        if ($date > $now) {
            $fail(__('validation.api.you_cannot_make_review_for_upcoming_event'));
        }
    }
}
