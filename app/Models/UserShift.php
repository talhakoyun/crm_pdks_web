<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserShift extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_shifts';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shiftDefinition()
    {
        return $this->belongsTo(\App\Models\ShiftDefinition::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
