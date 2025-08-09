<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftDefinition extends BaseModel
{
    use SoftDeletes;

    protected $table = 'shift_definitions';
    protected $guarded = [];
    protected $dates = ['start_time', 'end_time', 'deleted_at'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function getStartTimeAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getEndTimeAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function shiftFollows()
    {
        return $this->hasMany(ShiftFollow::class, 'shift_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('start_time', '>=', now())
            ->orderBy('start_time');
    }
}
