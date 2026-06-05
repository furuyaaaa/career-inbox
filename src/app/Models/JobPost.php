<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    /** @use HasFactory<\Database\Factories\JobPostFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'title',
        'occupation',
        'industry',
        'source',
        'agent_name',
        'location',
        'salary_min',
        'salary_max',
        'employment_type',
        'remote_type',
        'technologies',
        'status',
        'interest_level',
        'url',
        'received_at',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'received_at' => 'date',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'interest_level' => 'integer',
        ];
    }
}
