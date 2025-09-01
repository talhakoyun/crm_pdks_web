<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class UserShiftSchedule extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_shift_schedules';
    protected $guarded = [];
    protected $dates = ['schedule_date', 'deleted_at'];

    // Schedule types
    const TYPE_REGULAR = 'regular';
    const TYPE_CUSTOM = 'custom';
    const TYPE_HOLIDAY = 'holiday';
    const TYPE_OVERTIME = 'overtime';
    const TYPE_OFF = 'off';

    /**
     * Get all available schedule types
     */
    public static function getScheduleTypes(): array
    {
        return [
            self::TYPE_REGULAR => 'Normal Vardiya',
            self::TYPE_CUSTOM => 'Özel Vardiya',
            self::TYPE_HOLIDAY => 'Tatil Vardiyası',
            self::TYPE_OVERTIME => 'Mesai Vardiyası',
            self::TYPE_OFF => 'İzinli'
        ];
    }

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shiftDefinition()
    {
        return $this->belongsTo(ShiftDefinition::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('schedule_date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('schedule_date', [$startDate, $endDate]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByShift($query, $shiftId)
    {
        return $query->where('shift_definition_id', $shiftId);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('schedule_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeNextWeek($query)
    {
        return $query->whereBetween('schedule_date', [
            Carbon::now()->addWeek()->startOfWeek(),
            Carbon::now()->addWeek()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('schedule_date', Carbon::now()->month)
                    ->whereYear('schedule_date', Carbon::now()->year);
    }

    /**
     * Check if this is an off day
     */
    public function isOffDay(): bool
    {
        return $this->schedule_type === self::TYPE_OFF;
    }

    /**
     * Check if this is overtime
     */
    public function isOvertime(): bool
    {
        return $this->schedule_type === self::TYPE_OVERTIME;
    }

    /**
     * Get formatted schedule date
     */
    public function getFormattedDateAttribute(): string
    {
        return Carbon::parse($this->schedule_date)->format('d.m.Y');
    }

    /**
     * Get day name in Turkish
     */
    public function getDayNameAttribute(): string
    {
        $days = [
            'Monday' => 'Pazartesi',
            'Tuesday' => 'Salı',
            'Wednesday' => 'Çarşamba',
            'Thursday' => 'Perşembe',
            'Friday' => 'Cuma',
            'Saturday' => 'Cumartesi',
            'Sunday' => 'Pazar'
        ];

        return $days[Carbon::parse($this->schedule_date)->format('l')] ?? '';
    }
}
