<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    use HasFactory;

    // 🔥 PERBAIKAN: Sesuaikan nama kolom dengan database dan controller agar tidak diblokir Laravel!
    protected $fillable = [
        'title', 
        'category', 
        'division_name',  // 👈 Ubah dari 'division'
        'business_unit',  // 👈 Tambahkan ini
        'company_name', 
        'file_path', 
        'uploaded_by'
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}