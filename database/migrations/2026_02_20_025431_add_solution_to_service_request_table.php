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
        Schema::table('service_request_details', function (Blueprint $table) {
            //
            $table->string('solution', 8000)->nullable()->after('complaint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_request_details', function (Blueprint $table) {
            //
            $table->dropColumn('solution');
        });
    }
};
