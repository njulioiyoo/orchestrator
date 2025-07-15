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
            $table->boolean('is_active')->default(true)->after('password');
            $table->json('profile')->nullable()->after('is_active');
            $table->json('permissions')->nullable()->after('profile');
            $table->json('settings')->nullable()->after('permissions');
            $table->timestamp('last_login_at')->nullable()->after('settings');
            
            // Add indexes
            $table->index(['is_active']);
            $table->index(['last_login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['last_login_at']);
            $table->dropColumn([
                'is_active',
                'profile',
                'permissions',
                'settings',
                'last_login_at'
            ]);
        });
    }
};