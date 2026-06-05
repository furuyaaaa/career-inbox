<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['user_id', 'status', 'received_at']);
        });

        Schema::table('preference_profiles', function (Blueprint $table): void {
            $table->dropUnique(['name']);
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->unique(['user_id', 'name']);
        });

        Schema::table('gmail_connections', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['user_id', 'connected_at']);
        });

        Schema::table('gmail_imports', function (Blueprint $table): void {
            $table->dropUnique(['gmail_message_id']);
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->unique(['user_id', 'gmail_message_id']);
            $table->index(['user_id', 'status', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::table('gmail_imports', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'status', 'received_at']);
            $table->dropUnique(['user_id', 'gmail_message_id']);
            $table->dropConstrainedForeignId('user_id');
            $table->unique('gmail_message_id');
        });

        Schema::table('gmail_connections', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'connected_at']);
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('preference_profiles', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'name']);
            $table->dropConstrainedForeignId('user_id');
            $table->unique('name');
        });

        Schema::table('job_posts', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'status', 'received_at']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
