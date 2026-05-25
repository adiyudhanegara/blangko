<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('release_sets', function (Blueprint $table) {
            $table->string('splash_bg_color', 20)->nullable()->after('splash_subtitle');
            $table->string('splash_icon', 50)->nullable()->after('splash_bg_color');
        });
    }

    public function down(): void
    {
        Schema::table('release_sets', function (Blueprint $table) {
            $table->dropColumn(['splash_bg_color', 'splash_icon']);
        });
    }
};
