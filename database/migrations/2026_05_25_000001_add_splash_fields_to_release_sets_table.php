<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('release_sets', function (Blueprint $table) {
            $table->string('splash_title')->nullable()->after('period_label');
            $table->string('splash_subtitle')->nullable()->after('splash_title');
        });
    }

    public function down(): void
    {
        Schema::table('release_sets', function (Blueprint $table) {
            $table->dropColumn(['splash_title', 'splash_subtitle']);
        });
    }
};
