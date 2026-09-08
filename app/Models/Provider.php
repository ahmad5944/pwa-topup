<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'driver',
        'username',
        'api_key',
        'api_secret',
        'webhook_secret',
        'base_url',
        'priority',
        'is_active',
        'consecutive_failures',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'api_secret' => 'encrypted',
            'webhook_secret' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    protected $hidden = [
        'api_key',
        'api_secret',
        'webhook_secret',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
