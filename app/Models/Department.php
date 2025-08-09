<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends BaseModel
{
    protected $table = 'departments';
    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
