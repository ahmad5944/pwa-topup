<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResellerDownline extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'downline_user_id',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    public function downline(): BelongsTo
    {
        return $this->belongsTo(User::class, 'downline_user_id');
    }
}
