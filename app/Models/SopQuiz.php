<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'title',
        'passing_score',
        'is_active',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function questions()
    {
        return $this->hasMany(SopQuizQuestion::class, 'sop_quiz_id')->orderBy('sequence');
    }

    public function attempts()
    {
        return $this->hasMany(SopQuizAttempt::class, 'sop_quiz_id')->latest();
    }
}
