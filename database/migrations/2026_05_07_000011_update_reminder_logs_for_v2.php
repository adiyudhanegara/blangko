<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->foreignId('release_set_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->integer('reminder_offset_days')->nullable()->after('channel');
        });

        // Fill release_set_id from the form_release's new release_set_id
        DB::statement('
            UPDATE reminder_logs rl
            JOIN form_releases fr ON fr.id = rl.form_release_id
            SET rl.release_set_id = fr.release_set_id
        ');

        Schema::table('reminder_logs', function (Blueprint $table) {
            // Make non-nullable now that every row has a value
            $table->foreignId('release_set_id')->nullable(false)->change();

            // Drop the old FK (keep the column for backward compat reading)
            $table->dropForeign(['form_release_id']);

            // New unique constraint per spec: no duplicate reminder per (set, participant, offset)
            $table->unique(['release_set_id', 'participant_id', 'reminder_offset_days'], 'rl_set_participant_offset_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->dropUnique('rl_set_participant_offset_unique');
            $table->dropForeign(['release_set_id']);
            $table->dropColumn(['release_set_id', 'reminder_offset_days']);
            $table->foreign('form_release_id')->references('id')->on('form_releases')->cascadeOnDelete();
        });
    }
};
