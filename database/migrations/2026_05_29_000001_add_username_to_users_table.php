<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        $usedUsernames = [];
        $users = DB::table('users')->select('id', 'name', 'email')->orderBy('id')->get();

        foreach ($users as $user) {
            $baseUsername = Str::slug($user->name ?: Str::before($user->email, '@'), '_');
            $baseUsername = $baseUsername !== '' ? $baseUsername : 'user_' . $user->id;

            $username = $baseUsername;
            $suffix = 1;

            while (in_array($username, $usedUsernames, true)) {
                $username = $baseUsername . '_' . $suffix;
                $suffix++;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $username]);

            $usedUsernames[] = $username;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
