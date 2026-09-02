<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'evaluator_id',
        'evaluation_period',
        'due_date',
        'started_at',
        'submitted_at',
        'status',
        
        // Form Evaluasi Data
        'usage_status',
        'usage_reason',
        'conformity_status',
        'conformity_notes',
        'process_change_status',
        'process_change_notes',
        'effectiveness_status',
        'implementation_issues',
        'recommendation',
        'result',

        // Admin Action Data
        'admin_id',
        'admin_reviewed_at',
        'admin_notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'admin_reviewed_at' => 'datetime',
    ];

    /**
     * Menghubungkan evaluasi ke dokumen SOP terkait.
     */
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /**
     * Menghubungkan evaluasi ke user evaluator (Kepala BU / Kepala Dept).
     */
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * Menghubungkan evaluasi ke admin QMS yang meninjau.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
