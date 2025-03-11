<?php

namespace App\Rules;

use App\Models\Place;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckIfExistsInFavoratblesRule implements ValidationRule
{
    protected $favarable_type;

    public function __construct($favarable_type)
    {
        $this->favarable_type = $favarable_type;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $place = Place::findBySlug($value);

        $exists = DB::table('favorables')
            ->where('user_id', Auth::guard('api')->user()->id)
            ->where('favorable_type', $this->favarable_type)
            ->where('favorable_id', $place?->id)
            ->exists();

        if ($exists) {
            $fail(__('validation.api.you-already-make-this-as-favorite'));
        }
    }
}
