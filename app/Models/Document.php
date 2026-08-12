<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'department', 'reviewer_id', 'file_cover', 
        'file_lp', 'file_isi', 'file_lampiran', 'status', 
        'file_final', 'file_preview'
    ];

    // RELASI INI YANG PALING PENTING!
    // Kita sebutkan modelnya secara lengkap dan kolom foreign key-nya
    public function approvals()
    {
        return $this->hasMany(\App\Models\DocumentApproval::class, 'document_id', 'id')
                    ->orderBy('sequence', 'asc');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function logs()
    {
        return $this->hasMany(DocumentLog::class, 'document_id')->oldest();
    }
}