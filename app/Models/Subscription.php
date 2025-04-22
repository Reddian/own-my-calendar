<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
        'grades_used',
        'grades_limit',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'grades_used' => 'integer',
        'grades_limit' => 'integer',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->stripe_status === 'active' || $this->onTrial();
    }

    /**
     * Determine if the subscription is on trial.
     *
     * @return bool
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Determine if the subscription is canceled.
     *
     * @return bool
     */
    public function canceled(): bool
    {
        return !is_null($this->ends_at);
    }

    /**
     * Determine if the subscription has expired.
     *
     * @return bool
     */
    public function expired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Check if the user has grades remaining in their subscription.
     *
     * @return bool
     */
    public function hasGradesRemaining(): bool
    {
        // If user is on paid plan, they have unlimited grades
        if ($this->isActive() && !$this->onTrial()) {
            return true;
        }
        
        // If user is on free plan or trial, check grade limit
        return $this->grades_used < $this->grades_limit;
    }

    /**
     * Increment the number of grades used.
     *
     * @return void
     */
    public function incrementGradesUsed(): void
    {
        $this->increment('grades_used');
    }
}
