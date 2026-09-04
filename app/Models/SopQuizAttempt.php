<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopQuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'sop_quiz_id',
        'document_id',
        'user_id',
        'score',
        'status',
        'answers',
        'attempt_date',
        'feedback',
    ];

    protected $casts = [
        'answers' => 'array',
        'attempt_date' => 'datetime',
    ];

    public function quiz()
    {
        return $this->belongsTo(SopQuiz::class, 'sop_quiz_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
