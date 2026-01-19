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
        Schema::create('service_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests');
            $table->foreignId('cost_type_id')->constrained('cost_types');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_costs');
    }
};