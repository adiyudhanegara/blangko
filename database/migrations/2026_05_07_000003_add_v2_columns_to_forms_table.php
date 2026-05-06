<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->boolean('allow_multiple_submissions')->default(false)->after('allow_edit_after_submit');
            $table->boolean('allow_duplicate_from_previous')->default(false)->after('allow_multiple_submissions');
            $table->json('preview_question_ids')->nullable()->after('allow_duplicate_from_previous');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['allow_multiple_submissions', 'allow_duplicate_from_previous', 'preview_question_ids']);
        });
    }
};
