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
        Schema::create('google_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('calendar_id')->comment('Google Calendar ID');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_selected')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->json('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('refresh_token')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate calendars for a user
            $table->unique(['user_id', 'calendar_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_calendars');
    }
};
