<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('release_questions', function (Blueprint $table) {
            $table->boolean('allow_duplicate_in_new_submission')->default(true)->after('conditional_value');
        });

        Schema::table('release_question_options', function (Blueprint $table) {
            $table->boolean('is_other')->default(false)->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('release_question_options', function (Blueprint $table) {
            $table->dropColumn('is_other');
        });

        Schema::table('release_questions', function (Blueprint $table) {
            $table->dropColumn('allow_duplicate_in_new_submission');
        });
    }
};
