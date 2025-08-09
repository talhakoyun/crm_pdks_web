<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends BaseModel
{
    use SoftDeletes;
    protected $table = 'branches';
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class, 'branch_id', 'id');
    }

    public function allowedUsers()
    {
        return $this->belongsToMany(User::class, 'user_branches', 'branch_id', 'user_id');
    }

    public function zone()
    {
        return $this->hasOne(Zone::class, 'branch_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
