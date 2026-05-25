<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('release_sets', function (Blueprint $table) {
            $table->string('splash_icon_path')->nullable()->after('splash_icon');
        });
    }

    public function down(): void
    {
        Schema::table('release_sets', function (Blueprint $table) {
            $table->dropColumn('splash_icon_path');
        });
    }
};
