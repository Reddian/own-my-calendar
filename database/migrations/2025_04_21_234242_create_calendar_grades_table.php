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
        Schema::create('calendar_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('week_start_date')->comment('Start date of the graded week');
            $table->date('week_end_date')->comment('End date of the graded week');
            $table->float('overall_grade', 4, 2)->comment('Overall grade for the week (0-100)');
            
            // Individual grades for each calendar rule
            $table->json('rule_grades')->comment('Individual grades for each calendar rule (A-Z)');
            
            // Detailed feedback
            $table->text('strengths')->nullable()->comment('Areas where the calendar excels');
            $table->text('improvement_areas')->nullable()->comment('Areas that need improvement');
            $table->text('recommendations')->nullable()->comment('AI recommendations for improvement');
            
            // Raw calendar data for reference
            $table->json('calendar_data')->nullable()->comment('Raw calendar data used for grading');
            
            $table->timestamps();
            
            // Add index for efficient querying by week
            $table->index(['user_id', 'week_start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_grades');
    }
};
