<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GmailImport extends Model
{
    protected $fillable = [
        'user_id',
        'gmail_connection_id',
        'gmail_message_id',
        'subject',
        'sender',
        'snippet',
        'received_at',
        'job_post_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(GmailConnection::class, 'gmail_connection_id');
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }
}
