<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * After data migration: make release_set_id non-nullable, drop columns that moved up
 * to release_sets, and drop the old pivot table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_releases', function (Blueprint $table) {
            // Drop the old FK (was created with ON DELETE SET NULL, incompatible with NOT NULL)
            $table->dropForeign(['release_set_id']);

            // Make the column NOT NULL now that every row has a release_set_id
            $table->unsignedBigInteger('release_set_id')->nullable(false)->change();

            // Re-add FK without SET NULL
            $table->foreign('release_set_id')->references('id')->on('release_sets')->restrictOnDelete();

            $table->dropColumn(['name', 'public_token', 'start_at', 'end_at', 'status', 'reminder_schedule']);
        });

        Schema::dropIfExists('form_release_division');
    }

    public function down(): void
    {
        Schema::table('form_releases', function (Blueprint $table) {
            $table->string('name')->after('release_set_id');
            $table->string('public_token')->unique()->after('name');
            $table->datetime('start_at')->after('public_token');
            $table->datetime('end_at')->after('start_at');
            $table->enum('status', ['scheduled', 'open', 'closed', 'cancelled'])->default('scheduled')->after('end_at');
            $table->json('reminder_schedule')->nullable()->after('status');

            // Restore nullable FK with SET NULL
            $table->dropForeign(['release_set_id']);
            $table->unsignedBigInteger('release_set_id')->nullable()->change();
            $table->foreign('release_set_id')->references('id')->on('release_sets')->nullOnDelete();
        });

        Schema::create('form_release_division', function (Blueprint $table) {
            $table->foreignId('form_release_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->primary(['form_release_id', 'division_id']);
        });
    }
};
