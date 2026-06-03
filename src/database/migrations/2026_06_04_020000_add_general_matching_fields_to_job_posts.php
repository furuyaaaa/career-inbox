<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table): void {
            $table->string('occupation')->nullable()->after('title');
            $table->string('industry')->nullable()->after('occupation');

            $table->index('occupation');
            $table->index('industry');
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table): void {
            $table->dropIndex(['occupation']);
            $table->dropIndex(['industry']);
            $table->dropColumn(['occupation', 'industry']);
        });
    }
};
