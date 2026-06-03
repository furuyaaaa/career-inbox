<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preference_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('default');
            $table->unsignedSmallInteger('desired_salary_min')->nullable();
            $table->json('preferred_locations')->nullable();
            $table->boolean('remote_required')->default(false);
            $table->json('preferred_remote_types')->nullable();
            $table->json('preferred_technologies')->nullable();
            $table->json('excluded_keywords')->nullable();
            $table->timestamps();

            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preference_profiles');
    }
};
