<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Door extends Model
{
    protected $fillable = [
        'user_id',
        'friendly_name',
        'esp32_device_name',
        'last_interacted_at',
        'is_alarm_firing',
    ];

    protected $casts = [
        'last_interacted_at' => 'datetime',
        'is_alarm_firing' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alarmSlots(): HasMany
    {
        return $this->hasMany(AlarmSlot::class);
    }
}
