<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('release_sets', function (Blueprint $table) {
            $table->string('splash_text_color')->nullable()->after('splash_bg_color');
            $table->string('splash_icon_bg_color')->nullable()->after('splash_text_color');
        });
    }

    public function down(): void
    {
        Schema::table('release_sets', function (Blueprint $table) {
            $table->dropColumn(['splash_text_color', 'splash_icon_bg_color']);
        });
    }
};
