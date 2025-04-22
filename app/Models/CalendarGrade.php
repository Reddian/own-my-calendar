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
        'calendar_data',
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
        'calendar_data' => 'array',
    ];

    /**
     * Get the user that owns the calendar grade.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
