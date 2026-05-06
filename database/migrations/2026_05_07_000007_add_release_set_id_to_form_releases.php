<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_releases', function (Blueprint $table) {
            // Nullable first; the data migration (000009) fills it before we make it required.
            $table->foreignId('release_set_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('is_required')->default(true)->after('release_set_id');
            $table->unsignedInteger('order')->default(1)->after('is_required');
            $table->unsignedInteger('min_submissions_required')->nullable()->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('form_releases', function (Blueprint $table) {
            $table->dropForeign(['release_set_id']);
            $table->dropColumn(['release_set_id', 'is_required', 'order', 'min_submissions_required']);
        });
    }
};
