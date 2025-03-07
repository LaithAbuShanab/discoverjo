<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LegalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $terms = [];
        foreach ( $this->terms as $term) {
            $terms[] = $term->content;
        }
        return [
            'title' => $this->title,
            'content' =>  $this->content,
            'terms'=>$terms
        ];
    }
}
