<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSocialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'socialization_date',
        'notes',
        'attendance_file',
        'photos',
        'status',
    ];

    protected $casts = [
        'socialization_date' => 'date',
        'photos' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
