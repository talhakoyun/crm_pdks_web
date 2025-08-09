<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class Zone extends BaseModel
{
    use SoftDeletes;

    protected $table = 'zones';
    protected $guarded = [];

    protected $casts = [
        'positions' => Polygon::class,
    ];
    public function users()
    {
        return $this->hasMany(User::class, 'zone_id', 'id');
    }
}
