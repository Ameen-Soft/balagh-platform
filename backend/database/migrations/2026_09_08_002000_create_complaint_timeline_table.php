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
        Schema::create('complaint_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->restrictOnDelete();
            $table->enum('event_type', [
                'created',
                'verified',
                'assigned',
                'transferred',
                'status_changed',
                'evidence_uploaded',
                'resolved',
                'closed',
                'reopened',
                'rejected',
            ]);
            $table->text('description');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_timeline');
    }
};
