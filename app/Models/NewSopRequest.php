<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewSopRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'department',
        'description',
        'attachment_file',
        'status',
        'document_id',
        'admin_id',
        'admin_notes',
        'revision_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Pemohon SOP Baru
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Dokumen SOP yang diterbitkan/dihubungkan dari pengajuan ini
     */
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /**
     * Admin yang memverifikasi pengajuan
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
