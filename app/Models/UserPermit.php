<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermit extends Model
{
    protected $table = 'user_permits';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
