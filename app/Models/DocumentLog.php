<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentLog extends Model
{
    use HasFactory;

    // Daftarkan kolom yang boleh diisi
    protected $fillable = [
        'document_id',
        'user_id',
        'action',
        'notes'
    ];

    /**
     * Menghubungkan log kembali ke data user (pimpinan)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Menghubungkan log kembali ke dokumen terkait
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}