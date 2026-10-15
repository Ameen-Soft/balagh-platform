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
        Schema::table('complaints', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('longitude');
            $table->unsignedInteger('likes_count')->default(0)->after('is_public');
            $table->timestamp('community_alerted_at')->nullable()->after('likes_count');

            $table->index('is_public');
            $table->index('likes_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex(['is_public']);
            $table->dropIndex(['likes_count']);
            $table->dropColumn(['is_public', 'likes_count', 'community_alerted_at']);
        });
    }
};
