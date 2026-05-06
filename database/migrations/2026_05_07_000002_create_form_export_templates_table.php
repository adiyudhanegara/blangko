<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_export_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title_text')->nullable();
            $table->string('subtitle_template')->nullable();
            $table->boolean('show_auto_number')->default(true);
            $table->string('auto_number_label')->default('NO');
            $table->json('participant_columns')->nullable();
            $table->json('column_order')->nullable();
            $table->string('signature_role')->nullable();
            $table->string('signature_name')->nullable();
            $table->string('signature_nip')->nullable();
            $table->enum('signature_position', ['right', 'center', 'left'])->default('right');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_export_templates');
    }
};
