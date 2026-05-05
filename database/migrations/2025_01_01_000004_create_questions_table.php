<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['text', 'textarea', 'number', 'email', 'date', 'radio', 'checkbox', 'select', 'file']);
            $table->string('label');
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->json('validation_rules')->nullable();
            $table->foreignId('conditional_parent_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->string('conditional_value')->nullable();
            $table->timestamps();
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
    }
};
