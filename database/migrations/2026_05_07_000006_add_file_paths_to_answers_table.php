<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            // Stores multiple file uploads: [{"path":"...","original_name":"...","size":0,"mime":"..."}]
            $table->json('file_paths')->nullable()->after('file_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn('file_paths');
        });
    }
};
