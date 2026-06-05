<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GmailConnection extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'connected_at',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'scopes' => 'array',
            'connected_at' => 'datetime',
        ];
    }

    public static function primary(?int $userId = null): ?self
    {
        return self::query()
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->latest('connected_at')
            ->latest()
            ->first();
    }

    public function imports(): HasMany
    {
        return $this->hasMany(GmailImport::class);
    }
}
