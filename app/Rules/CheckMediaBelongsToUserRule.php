<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CheckMediaBelongsToUserRule implements ValidationRule
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
        if (!$media || $media->model->user_id !== Auth::guard('api')->user()->id) {
            $fail(__('validation.api.you_are_not_authorized_to_delete_this_media'));
        }
    }
}
