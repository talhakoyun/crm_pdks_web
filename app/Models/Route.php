<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use SoftDeletes;

    protected $table = 'routes';
    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
