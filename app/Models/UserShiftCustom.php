<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class UserShiftCustom extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_shift_customs';
    protected $guarded = [];
    protected $dates = ['start_date', 'end_date', 'deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shiftDefinition()
    {
        return $this->belongsTo(\App\Models\ShiftDefinition::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByDateRange($query, $date)
    {
        return $query->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date);
    }
}
