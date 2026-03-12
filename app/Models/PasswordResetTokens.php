<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetTokens extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'email';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
