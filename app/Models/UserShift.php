<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShift extends Model
{
    protected $table = 'user_shifts';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shiftDefinition()
    {
        return $this->belongsTo(ShiftDefinition::class);
    }
}
