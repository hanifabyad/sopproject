<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocializationAttendanceParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'name',
        'department',
        'status',
        'quiz_score',
        'quiz_status',
        'quiz_answers',
        'quiz_attempted_at',
        'attended_at',
    ];

    protected $casts = [
        'attended_at'       => 'datetime',
        'quiz_attempted_at' => 'datetime',
        'quiz_answers'      => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(SocializationAttendanceSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
