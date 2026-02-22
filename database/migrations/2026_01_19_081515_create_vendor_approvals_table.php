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
        Schema::create('vendor_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_policy_id')->constrained('approval_policies');
            $table->foreignId('approval_policy_step_id')->constrained('approval_policy_steps');
            $table->foreignId('service_request_id')->constrained('service_requests');
            $table->foreignId('approver_id')->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('status_id')->constrained('statuses');
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_approvals');
    }
};