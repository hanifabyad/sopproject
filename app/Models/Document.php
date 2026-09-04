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
        'doc_number', 'doc_revision', 'doc_date', 'company_header',
        'effective_date', 'evaluation_status', 'evaluation_due_date', 'evaluation_id',
        'sla_notes', 'sla_action_by', 'sla_action_at',
        'socialization_status', 'revision_deadline',
        'obsolete_at', 'review_due_date'
    ];

    protected $casts = [
        'sla_action_at' => 'datetime',
        'effective_date' => 'date',
        'evaluation_due_date' => 'date',
        'revision_deadline' => 'datetime',
        'obsolete_at' => 'datetime',
        'review_due_date' => 'date',
    ];

    public function quiz()
    {
        return $this->hasOne(SopQuiz::class, 'document_id');
    }

    public function quizAttempts()
    {
        return $this->hasMany(SopQuizAttempt::class, 'document_id');
    }

    public function socializations()
    {
        return $this->hasMany(DocumentSocialization::class, 'document_id')->latest();
    }

    public function latestSocialization()
    {
        return $this->hasOne(DocumentSocialization::class, 'document_id')->latestOfMany();
    }

    public function revisionRequests()
    {
        return $this->hasMany(RevisionRequest::class, 'document_id')->latest();
    }

    public function activeRevisionRequest()
    {
        return $this->hasOne(RevisionRequest::class, 'document_id')->whereIn('status', ['pending', 'approved'])->latestOfMany();
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'document_id');
    }

    public function latestEvaluation()
    {
        return $this->hasOne(Evaluation::class, 'document_id')->latestOfMany();
    }

    // RELASI INI YANG PALING PENTING!
    // Kita sebutkan modelnya secara lengkap dan kolom foreign key-nya
    public function approvals()
    {
        return $this->hasMany(\App\Models\DocumentApproval::class, 'document_id', 'id')
                    ->orderBy('sequence', 'asc');
    }

    public function latestRejectedApproval()
    {
        return $this->hasOne(\App\Models\DocumentApproval::class, 'document_id', 'id')
                    ->whereIn('status', ['rejected', 'need_revision'])
                    ->latestOfMany('id');
    }

    public function getLatestAnnotationsAttribute()
    {
        $rej = $this->latestRejectedApproval;
        return $rej ? $rej->annotations : null;
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

    public function slaActionUser()
    {
        return $this->belongsTo(User::class, 'sla_action_by');
    }

    public function logs()
    {
        return $this->hasMany(DocumentLog::class, 'document_id')->oldest();
    }

    /**
     * Menghitung durasi proses pembuatan/approval SOP dalam satuan hari.
     */
    public function getProcessDurationDaysAttribute(): int
    {
        $startDate = $this->created_at ?: now();
        
        if ($this->status === 'active') {
            $endDate = $this->effective_date 
                ? \Carbon\Carbon::parse($this->effective_date) 
                : ($this->updated_at ?: now());
        } else {
            $endDate = now();
        }

        return max(0, (int) $startDate->diffInDays($endDate));
    }

    /**
     * Menghitung usia SOP aktif (sejak disahkan / effective_date hingga hari ini) dalam hari.
     */
    public function getActiveLifespanDaysAttribute(): int
    {
        $startDate = $this->effective_date 
            ? \Carbon\Carbon::parse($this->effective_date) 
            : ($this->created_at ?: now());

        return max(0, (int) $startDate->diffInDays(now()));
    }

    /**
     * Menentukan status SLA (target 13 hari, overdue jika > 14 hari).
     */
    public function getSlaStatusAttribute(): string
    {
        $days = $this->process_duration_days;
        if ($days <= 10) {
            return 'on_track'; // Aman (<= 10 hari)
        } elseif ($days <= 13) {
            return 'warning';  // Mendekati batas target (11-13 hari)
        } else {
            return 'overdue';  // Melebihi 14 hari
        }
    }

    public function socializationSessions()
    {
        return $this->hasMany(\App\Models\SocializationAttendanceSession::class, 'document_id');
    }
}