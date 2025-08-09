<?php

namespace App\Models;

use App\Models\BaseModel;

class UserBranches extends BaseModel
{
    protected $table = 'user_branches';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
