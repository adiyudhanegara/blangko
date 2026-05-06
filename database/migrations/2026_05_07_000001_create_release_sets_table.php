<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('period_label')->nullable();
            $table->string('public_token')->unique();
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->enum('status', ['scheduled', 'open', 'closed', 'cancelled'])->default('scheduled');
            $table->json('reminder_schedule')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('public_token');
            $table->index('status');
        });

        Schema::create('release_set_division', function (Blueprint $table) {
            $table->foreignId('release_set_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->primary(['release_set_id', 'division_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('release_set_division');
        Schema::dropIfExists('release_sets');
    }
};
