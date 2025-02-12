<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Question extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['question'];

    protected $fillable = ['question', 'is_first_question'];

    protected $table = 'questions';

    public function questionChain()
    {
        return $this->hasMany(QuestionChain::class);
    }
}