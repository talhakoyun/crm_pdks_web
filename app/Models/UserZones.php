<?php

namespace App\Models;

use App\Models\BaseModel;

class UserZones extends BaseModel
{
    protected $table = 'user_zones';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
