<?php

namespace App\Models;

class HolidayType extends BaseModel
{
    protected $table = 'holiday_type';
    protected $guarded = [];

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }
}
