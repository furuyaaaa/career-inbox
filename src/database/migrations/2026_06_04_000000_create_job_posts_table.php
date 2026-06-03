<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table): void {
            $table->id();
            $table->string('company_name');
            $table->string('title');
            $table->string('source')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('salary_min')->nullable();
            $table->unsignedSmallInteger('salary_max')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('remote_type')->nullable();
            $table->json('technologies')->nullable();
            $table->string('status')->default('未確認');
            $table->unsignedTinyInteger('interest_level')->default(3);
            $table->string('url')->nullable();
            $table->date('received_at')->nullable();
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->index(['status', 'received_at']);
            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
