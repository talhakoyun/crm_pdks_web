<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends BaseModel
{
    use SoftDeletes;
    protected $table = 'menus';

    protected $guarded = ['id'];
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeCategory($query)
    {
        return $query->where('is_category', 1);
    }
    public function top()
    {
        return $this->hasOne(Menu::class, 'id', 'top_id');
    }
    public function subs()
    {
        return $this->hasMany(Menu::class, 'top_id', 'id');
    }
}
