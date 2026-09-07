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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('national_id')->nullable()->unique()->after('phone');
            $table->foreignId('department_id')->nullable()->after('national_id')
                  ->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['phone', 'national_id', 'department_id', 'is_active']);
        });
    }
};
