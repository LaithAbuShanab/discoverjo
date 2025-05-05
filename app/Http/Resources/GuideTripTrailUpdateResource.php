<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuideTripTrailUpdateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {

        return [
            'min_duration_in_minute' => $this->min_duration_in_minute,
            'max_duration_in_minute' => $this->max_duration_in_minute,
            'distance_in_meter' => $this->distance_in_meter,
            'difficulty' => $this->getDifficultyTranslations($this->difficulty),

        ];
    }

    protected function getDifficultyTranslations($difficulty)
    {
        $translations = [
            'en' => [
                0 => 'easy',
                1 => 'moderate',
                2 => 'hard',
                3 => 'very hard'
            ],
            'ar' => [
                0 => 'سهل',
                1 => 'متوسط',
                2 => 'صعب',
                3 => 'صعب جدًا'
            ]
        ];

        return [
            'en' => $translations['en'][$difficulty] ?? $difficulty,
            'ar' => $translations['ar'][$difficulty] ?? $difficulty,
        ];
    }






}
