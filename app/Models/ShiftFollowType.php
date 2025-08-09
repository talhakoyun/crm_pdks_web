<?php

namespace App\Models;

class ShiftFollowType extends BaseModel
{
    protected $table = 'shift_follow_types';
    protected $guarded = [];

    public function shiftFollows()
    {
        return $this->hasMany(ShiftFollow::class);
    }
}
