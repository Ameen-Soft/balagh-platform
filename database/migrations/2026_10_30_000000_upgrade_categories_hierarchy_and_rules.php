<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add ministry_id and is_active, and adjust indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('ministry_id')
                ->nullable()
                ->after('id')
                ->constrained('ministries')
                ->restrictOnDelete();

            $table->boolean('is_active')
                ->default(true)
                ->after('level');

            $table->index(['ministry_id', 'is_active']);
            $table->index(['parent_id', 'is_active']);
        });

        // 2. Populate ministry_id from existing department_id if records exist
        if (Schema::hasColumn('categories', 'department_id')) {
            $categories = DB::table('categories')
                ->join('departments', 'categories.department_id', '=', 'departments.id')
                ->select('categories.id', 'departments.ministry_id')
                ->get();

            foreach ($categories as $cat) {
                DB::table('categories')
                    ->where('id', $cat->id)
                    ->update(['ministry_id' => $cat->ministry_id]);
            }
        }

        // 3. Make department_id and description nullable on categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->change();

            $table->string('description')
                ->nullable()
                ->change();
        });

        // 4. Update foreign key constraints to restrictOnDelete for MySQL
        if (DB::getDriverName() === 'mysql') {
            try {
                Schema::table('categories', function (Blueprint $table) {
                    $table->dropForeign(['department_id']);
                    $table->foreign('department_id')
                        ->references('id')
                        ->on('departments')
                        ->restrictOnDelete();

                    $table->dropForeign(['parent_id']);
                    $table->foreign('parent_id')
                        ->references('id')
                        ->on('categories')
                        ->restrictOnDelete();
                });
            } catch (\Throwable $e) {
                // If foreign keys have custom names or already updated
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                Schema::table('categories', function (Blueprint $table) {
                    $table->dropForeign(['department_id']);
                    $table->foreign('department_id')
                        ->references('id')
                        ->on('departments')
                        ->cascadeOnDelete();

                    $table->dropForeign(['parent_id']);
                    $table->foreign('parent_id')
                        ->references('id')
                        ->on('categories')
                        ->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // ignore rollback FK rename failure
            }
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['ministry_id', 'is_active']);
            $table->dropIndex(['parent_id', 'is_active']);
            $table->dropForeign(['ministry_id']);
            $table->dropColumn(['ministry_id', 'is_active']);
        });
    }
};
