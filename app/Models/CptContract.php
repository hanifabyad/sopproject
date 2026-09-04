<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CptContract extends Model
{
    use HasFactory;

    protected $table = 'cpt_contracts';

    protected $fillable = [
        'customer',
        'contract_type',
        'project_title',
        'project_name',
        'project_number',
        'start_date',
        'end_date',
        'status',
        'notes',
        'last_notified_at',
        'document_file',
        'document_link',
        'created_by',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'last_notified_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'status_badge_class',
        'type',
        'has_document',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessor & Mutator for Type / Contract Type
     */
    public function getTypeAttribute(): ?string
    {
        return $this->contract_type;
    }

    public function setTypeAttribute($value): void
    {
        $this->attributes['contract_type'] = $value;
    }

    /**
     * Check if contract has either uploaded file or external link
     */
    public function getHasDocumentAttribute(): bool
    {
        return !empty($this->document_file) || !empty($this->document_link);
    }

    /**
     * Label Status Badge
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'        => 'Active',
            'expired'       => 'Expired',
            'still_not_yet' => 'Still Not Yet',
            'completed'     => 'Completed',
            default         => ucfirst($this->status),
        };
    }

    /**
     * Color Class for Status Badge
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active'        => 'bg-blue-50 text-[#1677B8] border-blue-200',
            'expired'       => 'bg-rose-50 text-rose-700 border-rose-200',
            'still_not_yet' => 'bg-amber-50 text-amber-700 border-amber-200',
            'completed'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            default         => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }
}
