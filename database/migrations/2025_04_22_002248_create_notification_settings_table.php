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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('weekly_grade')->default(false);
            $table->integer('weekly_grade_day')->default(1); // 0 = Sunday, 1 = Monday, etc.
            $table->string('weekly_grade_time')->default('09:00'); // 24-hour format
            $table->boolean('weekly_reminder')->default(false);
            $table->integer('weekly_reminder_day')->default(0); // 0 = Sunday, 1 = Monday, etc.
            $table->string('weekly_reminder_time')->default('18:00'); // 24-hour format
            $table->string('timezone')->default('America/New_York');
            $table->timestamp('last_grade_sent_at')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
