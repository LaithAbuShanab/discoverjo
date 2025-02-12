<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class LegalDocument extends Model
{
    use HasFactory,HasTranslations;
    public $translatable = ['title','content'];

    protected $guarded =[];

    public function terms()
    {
        return $this->hasMany(Term::class);
    }
}
