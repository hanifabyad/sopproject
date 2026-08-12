<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentApproval extends Model
{
    use HasFactory;

    protected $fillable = [
    'document_id',
    'user_id',
    'sequence',
    'status',
    'notes',
    'processed_at'
    ];

    // Relasi balik ke dokumen
   public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    // Relasi ke user (siapa yang meninjau)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}