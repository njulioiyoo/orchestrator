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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean, file, select, textarea
            $table->text('description')->nullable();
            $table->string('group')->default('general'); // general, email, appearance, system
            $table->json('options')->nullable(); // for select type
            $table->boolean('is_public')->default(false); // if setting can be accessed by non-admin
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['group', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
