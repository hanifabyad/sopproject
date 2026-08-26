<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'sequence',
        'stage',
        'signature_slot',
        'signature_page',
        'signature_x',
        'signature_y',
        'signature_anchor',
        'status',
        'notes',
        'processed_at'
    ];

    /**
     * Helper untuk menentukan stage dan signature_slot fisik berdasarkan role user
     */
    public static function getSlotAndStageForUser($user): array
    {
        $role = is_string($user) ? $user : ($user->role ?? '');

        $mapping = [
            'KA.DEPT.QMS'          => ['stage' => 'creator',  'signature_slot' => 'sig01'],
            'Chief of Staff'       => ['stage' => 'reviewer', 'signature_slot' => 'sig02'],
            'Ka. BU Gas & SPBE'    => ['stage' => 'reviewer', 'signature_slot' => 'sig03'],
            'Chief F&A'            => ['stage' => 'reviewer', 'signature_slot' => 'sig04'],
            'Ka. Div Retail'       => ['stage' => 'reviewer', 'signature_slot' => 'sig05'],
            'Wa. Ka. Div Retail'   => ['stage' => 'reviewer', 'signature_slot' => 'sig06'],
            'Ka. Div F&A'          => ['stage' => 'reviewer', 'signature_slot' => 'sig07'],
            'Dept. Internal Audit' => ['stage' => 'reviewer', 'signature_slot' => 'sig08'],
            'Direktur Utama'       => ['stage' => 'final',    'signature_slot' => 'sig09'],
            'Direktur CPT'         => ['stage' => 'final',    'signature_slot' => 'sig09'],
        ];

        if (isset($mapping[$role])) {
            return $mapping[$role];
        }

        if (str_contains(strtolower($role), 'dirut') || str_contains(strtolower($role), 'direktur')) {
            return ['stage' => 'final', 'signature_slot' => 'sig09'];
        }
        if (str_contains(strtolower($role), 'qms') || str_contains(strtolower($role), 'pembuat')) {
            return ['stage' => 'creator', 'signature_slot' => 'sig01'];
        }

        return ['stage' => 'reviewer', 'signature_slot' => 'sig02'];
    }

    // Relasi balik ke dokumen
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    // Relasi ke user (siapa yang meninjau)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
