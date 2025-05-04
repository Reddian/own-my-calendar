<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarGrade extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'week_start_date',
        'week_end_date',
        'overall_grade',
        'rule_grades',
        'strengths',
        'improvement_areas',
        'recommendations',
        // 'calendar_data', // Removed as requested
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'overall_grade' => 'float',
        'rule_grades' => 'array',
        'strengths' => 'array', // Add casting for strengths
        'improvement_areas' => 'array', // Add casting for improvement_areas
        'recommendations' => 'array', // Add casting for recommendations
        // 'calendar_data' => 'array', // Removed as requested
    ];

    /**
     * Get the user that owns the calendar grade.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

