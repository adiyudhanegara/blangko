<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('public_token')->unique();
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->enum('status', ['scheduled', 'open', 'closed', 'cancelled'])->default('scheduled');
            $table->json('reminder_schedule')->nullable();
            $table->datetime('published_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('public_token');
            $table->index('status');
        });

        Schema::create('form_release_division', function (Blueprint $table) {
            $table->foreignId('form_release_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->primary(['form_release_id', 'division_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_release_division');
        Schema::dropIfExists('form_releases');
    }
};
