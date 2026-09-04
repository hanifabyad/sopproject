<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'category',
        'division',
        'department',
        'business_unit',
    ];

    public function parent()
    {
        return $this->belongsTo(LibraryFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(LibraryFolder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(LibraryFile::class, 'folder_id');
    }
}
