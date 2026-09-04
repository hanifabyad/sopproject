<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username', // Pastikan ini username, bukan name
        'full_name',
        'email',
        'password',
        'role',
        'can_manage_cpt_contracts',
        'status',
        'whatsapp_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => 'boolean',
            'can_manage_cpt_contracts' => 'boolean',
        ];
    }
}
