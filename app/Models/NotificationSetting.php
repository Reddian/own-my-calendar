<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'weekly_grade',
        'weekly_grade_day',
        'weekly_grade_time',
        'weekly_reminder',
        'weekly_reminder_day',
        'weekly_reminder_time',
        'timezone',
        'last_grade_sent_at',
        'last_reminder_sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weekly_grade' => 'boolean',
        'weekly_reminder' => 'boolean',
        'last_grade_sent_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification settings.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if it's time to send a weekly grade notification.
     *
     * @return bool
     */
    public function shouldSendWeeklyGrade()
    {
        if (!$this->weekly_grade) {
            return false;
        }

        // Get current time in user's timezone
        $now = now()->setTimezone($this->timezone);
        $dayOfWeek = $now->dayOfWeek;
        $currentTime = $now->format('H:i');

        // Check if it's the right day and time
        if ($dayOfWeek == $this->weekly_grade_day && $currentTime == $this->weekly_grade_time) {
            // Check if we've already sent a notification today
            if ($this->last_grade_sent_at && $this->last_grade_sent_at->setTimezone($this->timezone)->isToday()) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Check if it's time to send a weekly planning reminder.
     *
     * @return bool
     */
    public function shouldSendWeeklyReminder()
    {
        if (!$this->weekly_reminder) {
            return false;
        }

        // Get current time in user's timezone
        $now = now()->setTimezone($this->timezone);
        $dayOfWeek = $now->dayOfWeek;
        $currentTime = $now->format('H:i');

        // Check if it's the right day and time
        if ($dayOfWeek == $this->weekly_reminder_day && $currentTime == $this->weekly_reminder_time) {
            // Check if we've already sent a reminder today
            if ($this->last_reminder_sent_at && $this->last_reminder_sent_at->setTimezone($this->timezone)->isToday()) {
                return false;
            }
            return true;
        }

        return false;
    }
}
