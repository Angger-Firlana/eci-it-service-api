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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('users');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('entity_type_id')->constrained('entity_types');
            $table->string('action');
            $table->foreignId('old_status_id')->constrained('statuses');
            $table->foreignId('new_status_id')->constrained('statuses');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};