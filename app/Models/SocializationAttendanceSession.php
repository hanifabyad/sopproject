<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocializationAttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'document_id',
        'user_id',
        'company',
        'agenda',
        'doc_number',
        'session_date',
        'session_time',
        'location',
        'speaker',
        'is_active',
    ];

    protected $casts = [
        'session_date' => 'date',
        'is_active'    => 'boolean',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->hasMany(SocializationAttendanceParticipant::class, 'session_id')->orderBy('id', 'asc');
    }

    /**
     * Dapatkan URL presensi yang konsisten antara layar scan dan berkas PDF
     */
    public function getPresensiUrl(): string
    {
        $scheme = request()->getScheme() ?: 'http';
        $host = request()->getHost();
        $port = request()->getPort();
        $portStr = ($port && $port != 80 && $port != 443) ? ":{$port}" : ":8000";

        if ($host && $host !== 'localhost' && $host !== '127.0.0.1') {
            return "{$scheme}://{$host}{$portStr}/presensi/{$this->token}";
        }

        $lanIp = gethostbyname(gethostname());
        if (!$lanIp || $lanIp === '127.0.0.1') {
            $lanIp = '192.168.110.169';
        }

        return "{$scheme}://{$lanIp}{$portStr}/presensi/{$this->token}";
    }
}
