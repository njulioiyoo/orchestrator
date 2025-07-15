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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('business_type')->nullable()->after('subdomain');
            $table->string('phone')->nullable()->after('business_type');
            $table->text('address')->nullable()->after('phone');
            $table->string('logo_path')->nullable()->after('address');
            $table->string('primary_color')->default('#3B82F6')->after('logo_path');
            $table->string('secondary_color')->default('#1E40AF')->after('primary_color');
            $table->string('timezone')->default('Asia/Jakarta')->after('secondary_color');
            $table->string('locale')->default('id_ID')->after('timezone');
            $table->string('currency')->default('IDR')->after('locale');
            
            // Add indexes
            $table->index(['business_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['business_type']);
            $table->dropColumn([
                'business_type',
                'phone',
                'address',
                'logo_path',
                'primary_color',
                'secondary_color',
                'timezone',
                'locale',
                'currency'
            ]);
        });
    }
};