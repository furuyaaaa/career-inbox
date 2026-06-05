<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GmailOauthSetting extends Model
{
    protected $fillable = [
        'client_id',
        'client_secret',
        'redirect_uri',
    ];

    protected function casts(): array
    {
        return [
            'client_secret' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return self::query()->oldest('id')->first()
            ?? self::query()->create(['redirect_uri' => url('/gmail/callback')]);
    }
}
