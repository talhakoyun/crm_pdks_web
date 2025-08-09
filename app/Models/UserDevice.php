<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $table = 'user_devices';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
