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
        Schema::create('approval_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_type_id')->constrained('entity_types');
            $table->foreignId('condition_type_id')->constrained('condition_types');
            $table->string('condition_value');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_policies');
    }
};