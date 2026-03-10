<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashbackHistory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'referral_id',
        'referral_name',
        'value',
        'is_positive',
        'date',
    ];

    protected $casts = [
        'is_positive' => 'boolean',
        'date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }
}
