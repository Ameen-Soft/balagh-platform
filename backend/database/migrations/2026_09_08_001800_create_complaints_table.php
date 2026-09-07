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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('current_department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('duplicate_of_id')->nullable()->constrained('complaints')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('status', [
                'new',
                'under_review',
                'assigned',
                'in_progress',
                'resolved',
                'closed',
                'rejected',
                'reopened',
            ])->default('new');
            $table->string('priority');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();

            $table->index('status');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
