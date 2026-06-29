<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlarmSlot extends Model
{
    protected $fillable = [
        'door_id',
        'music_id',
        'start_time',
        'end_time',
        'days',
        'is_active',
    ];

    protected $casts = [
        'days' => 'array',
        'is_active' => 'boolean',
    ];

    public function door(): BelongsTo
    {
        return $this->belongsTo(Door::class);
    }

    public function music(): BelongsTo
    {
        return $this->belongsTo(Music::class);
    }
}
