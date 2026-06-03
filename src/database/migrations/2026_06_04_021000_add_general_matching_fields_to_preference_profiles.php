<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preference_profiles', function (Blueprint $table): void {
            $table->json('preferred_occupations')->nullable()->after('desired_salary_min');
            $table->json('preferred_industries')->nullable()->after('preferred_occupations');
        });
    }

    public function down(): void
    {
        Schema::table('preference_profiles', function (Blueprint $table): void {
            $table->dropColumn(['preferred_occupations', 'preferred_industries']);
        });
    }
};
