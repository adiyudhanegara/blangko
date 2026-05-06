<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix status enum to match what the application actually uses ('draft' not 'in_progress')
        // and drop the unique constraint to support multi-submission forms.
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM('draft','in_progress','submitted') DEFAULT 'draft'");

        // Rename any legacy 'in_progress' rows to 'draft'
        DB::table('submissions')->where('status', 'in_progress')->update(['status' => 'draft']);

        // Now narrow enum to just draft/submitted
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM('draft','submitted') DEFAULT 'draft'");

        Schema::table('submissions', function (Blueprint $table) {
            // Add a plain index first so the FK on form_release_id is still covered
            // after we drop the unique index (MySQL requires an index on FK columns).
            $table->index('form_release_id', 'submissions_form_release_id_index');

            // Drop unique constraint — uniqueness is now enforced at the service layer
            // so multi-submission forms can have many rows per (release, participant).
            $table->dropUnique(['form_release_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM('in_progress','submitted') DEFAULT 'in_progress'");

        Schema::table('submissions', function (Blueprint $table) {
            $table->unique(['form_release_id', 'participant_id']);
            $table->dropIndex('submissions_form_release_id_index');
        });
    }
};
