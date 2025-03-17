<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CheckIfImageBelongToGuideRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $media = Media::find($value);
        if(!$media)return;
        $belongToGuide = $media->model_type::find($media->model_id);
        if(!$belongToGuide) {
            $fail(__('validation.api.there is no trip belong to this image'));
            return;
        };
        if($belongToGuide->guide_id !== Auth::guard('api')->user()->id){
            $fail(__('validation.api.not_owner_of_image'));
        }
    }
}
