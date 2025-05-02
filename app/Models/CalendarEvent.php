<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "google_calendar_id",
        "event_id",
        "title",
        "start_time",
        "end_time",
        "is_all_day",
        "description",
        "location",
        "attendees",
        "raw_data",
        "last_synced_at",
    ];

    protected $casts = [
        "start_time" => "datetime",
        "end_time" => "datetime",
        "is_all_day" => "boolean",
        "attendees" => "array",
        "raw_data" => "array",
        "last_synced_at" => "datetime",
    ];

    /**
     * Get the user that owns the event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Google Calendar that the event belongs to.
     */
    public function googleCalendar()
    {
        return $this->belongsTo(GoogleCalendar::class);
    }
}

