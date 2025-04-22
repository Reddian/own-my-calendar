<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleCalendar extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'calendar_id',
        'name',
        'description',
        'color',
        'is_primary',
        'is_selected',
        'is_visible',
        'access_token',
        'token_expires_at',
        'refresh_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'is_selected' => 'boolean',
        'is_visible' => 'boolean',
        'access_token' => 'json',
        'token_expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the calendar.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the access token is expired.
     *
     * @return bool
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return true;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Check if the calendar has a valid access token.
     *
     * @return bool
     */
    public function hasValidToken(): bool
    {
        return !empty($this->access_token) && !$this->isTokenExpired();
    }

    /**
     * Check if the calendar has a refresh token.
     *
     * @return bool
     */
    public function hasRefreshToken(): bool
    {
        return !empty($this->refresh_token);
    }
}
