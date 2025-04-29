<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    protected $fillable = [
        'user_id',
        'is_connected',
        'last_sync',
        'quick_add_enabled',
        'notifications_enabled',
        'auto_sync_enabled',
        'sync_interval',
        'notification_time',
        'quick_adds_count',
        'notifications_count',
        'syncs_count'
    ];

    protected $casts = [
        'is_connected' => 'boolean',
        'quick_add_enabled' => 'boolean',
        'notifications_enabled' => 'boolean',
        'auto_sync_enabled' => 'boolean',
        'last_sync' => 'datetime',
        'sync_interval' => 'integer',
        'notification_time' => 'integer',
        'quick_adds_count' => 'integer',
        'notifications_count' => 'integer',
        'syncs_count' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 