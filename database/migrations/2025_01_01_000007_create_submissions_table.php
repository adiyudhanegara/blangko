<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_release_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['in_progress', 'submitted'])->default('in_progress');
            $table->datetime('submitted_at')->nullable();
            $table->datetime('last_edited_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamps();

            $table->unique(['form_release_id', 'participant_id']);
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('release_question_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->json('value_json')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_original_name')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'release_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('submissions');
    }
};
