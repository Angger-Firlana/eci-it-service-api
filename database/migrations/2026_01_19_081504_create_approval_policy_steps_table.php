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
        Schema::create('approval_policy_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_policy_id')->constrained('approval_policies');
            $table->integer('step_order');
            $table->foreignId('role_id')->constrained('roles');
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_policy_steps');
    }
};