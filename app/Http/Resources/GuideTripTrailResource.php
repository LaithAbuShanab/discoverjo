<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuideTripTrailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $lang = $request->header('Content-Language') ? $request->header('Content-Language') : 'ar';

        return [
            'min_duration_in_minute' => $this->min_duration_in_minute,
            'max_duration_in_minute' => $this->max_duration_in_minute,
            'distance_in_meter' => $this->distance,
            'difficulty' => $this->translateDifficulty($this->difficulty, $lang)
        ];
    }

    protected function translateDifficulty($difficulty, $lang)
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

        return $translations[$lang][$difficulty] ?? $difficulty;
    }





}
