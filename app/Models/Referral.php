<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_name',
        'referred_cpf',
        'referred_phone',
        'plan',
        'status',
        'cashback_value',
        'contracted_at',
        'paid_at',
        'rejection_reason',
    ];

    protected $casts = [
        'contracted_at' => 'datetime',
        'paid_at'       => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}
