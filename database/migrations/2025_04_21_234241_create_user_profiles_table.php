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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('mt_everest')->comment('User\'s ultimate goal or aspiration');
            $table->text('money_making_activities')->comment('User\'s top 5 money-making activities');
            $table->text('energy_renewal_activities')->comment('Activities for personal time and energy renewal');
            $table->json('calendar_preferences')->nullable()->comment('User preferences for calendar organization');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
