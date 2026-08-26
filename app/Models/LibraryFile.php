<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryFile extends Model
{
    use HasFactory;

    protected $fillable = ['folder_id', 'name', 'path', 'mime_type', 'size', 'uploaded_by'];

    public function folder()
    {
        return $this->belongsTo(LibraryFolder::class, 'folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
