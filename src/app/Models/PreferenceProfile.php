<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreferenceProfile extends Model
{
    /** @use HasFactory<\Database\Factories\PreferenceProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'desired_salary_min',
        'preferred_occupations',
        'preferred_industries',
        'preferred_locations',
        'remote_required',
        'preferred_remote_types',
        'preferred_technologies',
        'excluded_keywords',
    ];

    protected function casts(): array
    {
        return [
            'desired_salary_min' => 'integer',
            'preferred_occupations' => 'array',
            'preferred_industries' => 'array',
            'preferred_locations' => 'array',
            'remote_required' => 'boolean',
            'preferred_remote_types' => 'array',
            'preferred_technologies' => 'array',
            'excluded_keywords' => 'array',
        ];
    }

    public static function primary(): self
    {
        return self::query()->firstOrCreate(
            ['name' => 'default'],
            [
                'desired_salary_min' => 650,
                'preferred_occupations' => ['営業', 'マーケティング', 'カスタマーサクセス', 'エンジニア'],
                'preferred_industries' => ['IT', 'SaaS', '人材', '教育'],
                'preferred_locations' => ['東京', '全国'],
                'remote_required' => true,
                'preferred_remote_types' => ['フルリモート', 'ハイブリッド', '週3リモート'],
                'preferred_technologies' => ['法人営業', 'CRM', 'データ分析', '企画', 'Laravel', 'Python'],
                'excluded_keywords' => ['常駐のみ', '飛び込み営業'],
            ],
        );
    }
}
