<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
        'sequence',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
