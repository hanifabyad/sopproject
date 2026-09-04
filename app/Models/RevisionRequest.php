<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'reason',
        'status',
        'admin_id',
        'approved_at',
        'deadline_at',
        'admin_notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'deadline_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Sisa hari / jam sebelum deadline 7 hari berakhir
     */
    public function getRemainingDaysAttribute(): int
    {
        if (!$this->deadline_at) {
            return 0;
        }
        return max(0, (int) now()->diffInDays($this->deadline_at, false));
    }

    public function getIsExpiredAttribute(): bool
    {
        if (!$this->deadline_at) {
            return false;
        }
        return now()->greaterThan($this->deadline_at);
    }
}
