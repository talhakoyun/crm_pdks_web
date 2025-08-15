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

    // Status constants
    const STATUS_NORMAL = 'normal';
    const STATUS_LATE = 'late';
    const STATUS_EARLY_OUT = 'early_out';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NORMAL => 'Normal',
            self::STATUS_LATE => 'Geç Giriş',
            self::STATUS_EARLY_OUT => 'Erken Çıkış'
        ];
    }

    /**
     * Check if the record is late
     */
    public function isLate(): bool
    {
        return $this->status === self::STATUS_LATE;
    }

    /**
     * Check if the record is early out
     */
    public function isEarlyOut(): bool
    {
        return $this->status === self::STATUS_EARLY_OUT;
    }

    /**
     * Get status minutes with default value
     */
    public function getStatusMinutesAttribute($value): int
    {
        return $value ?? 0;
    }
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
            $q->where('type', 'in');
        });
    }

    public function scopeCheckOut($query)
    {
        return $query->whereHas('followType', function($q) {
            $q->where('type', 'out');
        });
    }
}
