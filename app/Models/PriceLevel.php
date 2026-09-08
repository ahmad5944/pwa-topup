<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceLevel extends Model
{
    protected $fillable = [
        'name',
        'markup_percent',
        'is_reseller_level',
    ];

    protected function casts(): array
    {
        return [
            'markup_percent' => 'decimal:2',
            'is_reseller_level' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
