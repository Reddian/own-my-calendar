<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'timezone',
        'password',
        'has_completed_onboarding', // Added flag
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'has_completed_onboarding' => 'boolean', // Added cast
    ];

    /**
     * Get the user's subscription.
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        // Ensure this relationship exists and points to UserProfile
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Determine if the user has an active subscription.
     *
     * @return bool
     */
    public function subscribed(): bool
    {
        if (!$this->subscription) {
            return false;
        }
        
        return $this->subscription->isActive() && !$this->subscription->expired();
    }

    /**
     * Get the number of grades used by the user.
     *
     * @return int
     */
    public function gradesUsed(): int
    {
        if (!$this->subscription) {
            return 0;
        }
        
        return $this->subscription->grades_used;
    }

    /**
     * Get the user's extension settings.
     */
    public function extension(): HasOne
    {
        return $this->hasOne(Extension::class);
    }

    /**
     * Get the Google Calendars associated with the user.
     */
    public function calendars(): HasMany
    {
        return $this->hasMany(GoogleCalendar::class);
    }
}

