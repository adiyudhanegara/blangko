<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('language', 5)->default('id');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('allow_edit_after_submit')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('form_division', function (Blueprint $table) {
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->primary(['form_id', 'division_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_division');
        Schema::dropIfExists('forms');
    }
};
