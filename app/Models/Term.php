<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Term extends Model
{
    use HasFactory,HasTranslations;
    public $translatable = ['content'];
    protected $guarded =[];

    public function legalDocumentation()
    {
        return $this->belongsTo(LegalDocument::class);
    }

}
