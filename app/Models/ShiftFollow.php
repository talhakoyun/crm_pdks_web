<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MatanYadaev\EloquentSpatial\Objects\Point;

class ShiftFollow extends BaseModel
{
    use SoftDeletes;

    protected $table = 'shift_follows';
    protected $guarded = [];
    protected $dates = ['transaction_date', 'deleted_at'];
    protected $casts = [
        'positions' => Point::class,
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function enterBranch()
    {
        return $this->belongsTo(Branch::class, 'enter_branch_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(ShiftDefinition::class, 'shift_id');
    }

    public function followType()
    {
        return $this->belongsTo(ShiftFollowType::class, 'shift_follow_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year);
    }

    public function scopeByDateRange($query, $start, $end)
    {
        return $query->whereBetween('transaction_date', [$start, $end]);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeCheckIn($query)
    {
        return $query->whereHas('followType', function($q) {
            $q->where('type', 'check_in');
        });
    }

    public function scopeCheckOut($query)
    {
        return $query->whereHas('followType', function($q) {
            $q->where('type', 'check_out');
        });
    }
}
