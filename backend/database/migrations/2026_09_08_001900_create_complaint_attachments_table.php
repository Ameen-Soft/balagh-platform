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
        Schema::create('complaint_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_type');
            $table->decimal('captured_latitude', 10, 7);
            $table->decimal('captured_longitude', 10, 7);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->enum('type', ['before', 'after']);
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_attachments');
    }
};
