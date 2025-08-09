<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'location',
        'quota',
        'start_date',
        'end_date',
        'status',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function approvedParticipants(): HasMany
    {
        return $this->participants()->where('status', 'approved');
    }

    public function pendingParticipants(): HasMany
    {
        return $this->participants()->where('status', 'pending');
    }

    public function rejectedParticipants(): HasMany
    {
        return $this->participants()->where('status', 'rejected');
    }
}
