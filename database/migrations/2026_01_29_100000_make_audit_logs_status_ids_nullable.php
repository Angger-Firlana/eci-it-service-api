<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['old_status_id']);
            $table->dropForeign(['new_status_id']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            // Make columns nullable
            $table->unsignedBigInteger('old_status_id')->nullable()->change();
            $table->unsignedBigInteger('new_status_id')->nullable()->change();
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            // Re-add foreign key constraints with nullable
            $table->foreign('old_status_id')->references('id')->on('statuses')->nullOnDelete();
            $table->foreign('new_status_id')->references('id')->on('statuses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reversing this would fail if there are null values in the columns
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['old_status_id']);
            $table->dropForeign(['new_status_id']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('old_status_id')->nullable(false)->change();
            $table->unsignedBigInteger('new_status_id')->nullable(false)->change();
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreign('old_status_id')->references('id')->on('statuses');
            $table->foreign('new_status_id')->references('id')->on('statuses');
        });
    }
};
