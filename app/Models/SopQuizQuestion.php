<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopQuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'sop_quiz_id',
        'type',
        'question',
        'options',
        'correct_answer',
        'points',
        'sequence',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function quiz()
    {
        return $this->belongsTo(SopQuiz::class, 'sop_quiz_id');
    }
}
