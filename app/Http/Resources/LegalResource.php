<?php

namespace App\Http\Resources;

use App\Models\LegalDocument;
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
        $lastLegal = LegalDocument::latest('updated_at')->first()?->updated_at;
        $terms = [];
        foreach ( $this->terms as $term) {
            $terms[] = $term->content;
        }
        return [
            'last_update'=>$lastLegal,
            'title' => $this->title,
            'content' =>  $this->content,
            'terms'=>$terms
        ];
    }
}
