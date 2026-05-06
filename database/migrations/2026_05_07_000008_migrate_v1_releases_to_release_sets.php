<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data migration: create one ReleaseSet per v1 FormRelease, copying token/dates/divisions.
 * The public URL (/r/{token}) continues to work because the token moves to release_sets.
 */
return new class extends Migration
{
    public function up(): void
    {
        $releases = DB::table('form_releases')->whereNull('deleted_at')->get();

        foreach ($releases as $release) {
            $setId = DB::table('release_sets')->insertGetId([
                'name'              => $release->name,
                'description'       => null,
                'period_label'      => null,
                'public_token'      => $release->public_token,
                'start_at'          => $release->start_at,
                'end_at'            => $release->end_at,
                'status'            => $release->status,
                'reminder_schedule' => $release->reminder_schedule,
                'created_by'        => $release->created_by,
                'deleted_at'        => $release->deleted_at,
                'created_at'        => $release->created_at,
                'updated_at'        => $release->updated_at,
            ]);

            // Copy division pivot rows
            $pivotRows = DB::table('form_release_division')
                ->where('form_release_id', $release->id)
                ->get();

            foreach ($pivotRows as $pivot) {
                DB::table('release_set_division')->insert([
                    'release_set_id' => $setId,
                    'division_id'    => $pivot->division_id,
                ]);
            }

            // Point the form_release to its new set
            DB::table('form_releases')
                ->where('id', $release->id)
                ->update(['release_set_id' => $setId, 'order' => 1]);
        }

        // Also migrate soft-deleted releases
        $deleted = DB::table('form_releases')->whereNotNull('deleted_at')->get();
        foreach ($deleted as $release) {
            if (DB::table('release_sets')->where('public_token', $release->public_token)->exists()) {
                continue; // already migrated (unlikely but safe)
            }

            $setId = DB::table('release_sets')->insertGetId([
                'name'              => $release->name,
                'description'       => null,
                'period_label'      => null,
                'public_token'      => $release->public_token,
                'start_at'          => $release->start_at,
                'end_at'            => $release->end_at,
                'status'            => $release->status,
                'reminder_schedule' => $release->reminder_schedule,
                'created_by'        => $release->created_by,
                'deleted_at'        => $release->deleted_at,
                'created_at'        => $release->created_at,
                'updated_at'        => $release->updated_at,
            ]);

            DB::table('form_releases')
                ->where('id', $release->id)
                ->update(['release_set_id' => $setId, 'order' => 1]);
        }
    }

    public function down(): void
    {
        // Detach release_set_id from form_releases
        DB::table('form_releases')->update(['release_set_id' => null]);

        // Clear pivot + truncate
        DB::table('release_set_division')->truncate();
        DB::table('release_sets')->truncate();
    }
};
