<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'cpf',
        'email',
        'password',
        'pix_key',
        'referral_code',
        'referral_link',
        'role',
        'referred_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'email_verified_at',
        'role',
        'referred_by',
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ["id" => $this->id, "role" => $this->role];
    }
}
