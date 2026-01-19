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
        Schema::create('service_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests');
            $table->foreignId('cancelled_by')->constrained('users');
            $table->text('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_cancellations');
    }
};