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
        'file_final', 'file_preview',
        'doc_number', 'doc_revision', 'doc_date', 'company_header'
    ];

    // RELASI INI YANG PALING PENTING!
    // Kita sebutkan modelnya secara lengkap dan kolom foreign key-nya
    public function approvals()
    {
        return $this->hasMany(\App\Models\DocumentApproval::class, 'document_id', 'id')
                    ->orderBy('sequence', 'asc');
    }

    public function attachments()
    {
        return $this->hasMany(DocumentAttachment::class, 'document_id', 'id')
                    ->orderBy('sequence', 'asc');
    }

    public function getAllAttachmentsAttribute()
    {
        if ($this->relationLoaded('attachments') ? $this->attachments->count() > 0 : $this->attachments()->count() > 0) {
            return $this->attachments;
        }

        if (!empty($this->file_lampiran)) {
            $legacyItem = new DocumentAttachment([
                'id'            => 0,
                'document_id'   => $this->id,
                'original_name' => basename($this->file_lampiran),
                'stored_name'   => basename($this->file_lampiran),
                'file_path'     => $this->file_lampiran,
                'mime_type'     => 'application/pdf',
                'sequence'      => 1,
            ]);
            $legacyItem->id = 0;
            return collect([$legacyItem]);
        }

        return collect([]);
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