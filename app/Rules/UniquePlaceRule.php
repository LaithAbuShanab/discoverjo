<?php

namespace App\Rules;

use App\Models\Place;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePlaceRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if(request()->id){
            $place = Place::where("name->en", request()->name_en)->where('longitude', request()->longitude)->where('latitude',request()->latitude)->where('id', '!=', request()->id)->exists();

        }else{
            $place = Place::where("name->en", request()->name_en)->where('longitude', request()->longitude)->where('latitude',request()->latitude)->exists();

        }
       if($place){
           $fail(__('validation.api.this-name-en-and-location-exists'));
       }
    }
}
