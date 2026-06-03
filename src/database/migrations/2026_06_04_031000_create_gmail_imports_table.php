<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gmail_imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gmail_connection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gmail_message_id')->nullable()->unique();
            $table->string('subject')->nullable();
            $table->string('sender')->nullable();
            $table->text('snippet')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('job_post_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('imported');
            $table->timestamps();

            $table->index(['status', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gmail_imports');
    }
};
