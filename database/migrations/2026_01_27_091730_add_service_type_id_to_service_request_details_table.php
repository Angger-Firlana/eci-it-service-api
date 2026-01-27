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
            $table->foreignId('service_type_id')->nullable()->after('service_request_id')->constrained('service_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_request_details', function (Blueprint $table) {
            $table->dropForeign(['service_type_id']);
            $table->dropColumn('service_type_id');
        });
    }
};
