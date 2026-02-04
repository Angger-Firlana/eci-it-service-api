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
        Schema::table('service_locations', function (Blueprint $table) {
            //
            $table->dropColumn('city');
            $table->dropColumn('province');
            $table->dropColumn('postal_code');
            $table->string('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_locations', function (Blueprint $table) {
            //
            $table->string('postal_code');
            $table->string('city');
            $table->string('province');
        });
    }
};
