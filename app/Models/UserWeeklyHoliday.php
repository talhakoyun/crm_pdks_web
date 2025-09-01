<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class UserWeeklyHoliday extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_weekly_holidays';

    protected $fillable = [
        'user_id',
        'holiday_days',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'holiday_days' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Kullanıcı ilişkisi
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tatil günlerini gün isimlerine çevir
     */
    public function getHolidayDayNamesAttribute()
    {
        $dayNames = [
            1 => 'Pazartesi',
            2 => 'Salı',
            3 => 'Çarşamba',
            4 => 'Perşembe',
            5 => 'Cuma',
            6 => 'Cumartesi',
            7 => 'Pazar'
        ];

        $holidayDays = $this->holiday_days ?? [];
        return collect($holidayDays)->map(function($day) use ($dayNames) {
            return $dayNames[$day] ?? '';
        })->filter()->values()->toArray();
    }

    /**
     * Belirli bir günün tatil günü olup olmadığını kontrol et
     */
    public function isHolidayDay($dayNumber)
    {
        $holidayDays = $this->holiday_days ?? [];
        return in_array($dayNumber, $holidayDays);
    }

    /**
     * Haftalık tatil günlerini string olarak döndür
     */
    public function getHolidayDaysStringAttribute()
    {
        $dayNames = $this->getHolidayDayNamesAttribute();
        return empty($dayNames) ? 'Tanımlı değil' : implode(', ', $dayNames);
    }
}
