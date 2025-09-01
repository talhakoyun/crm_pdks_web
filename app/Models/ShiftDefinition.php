<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftDefinition extends BaseModel
{
    use SoftDeletes;

    protected $table = 'shift_definitions';
    protected $guarded = [];
    protected $dates = ['start_time', 'end_time', 'start_date', 'end_date', 'deleted_at'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Haftalık çalışma günleri
    const WEEKDAYS = [
        'monday' => 'Pazartesi',
        'tuesday' => 'Salı',
        'wednesday' => 'Çarşamba',
        'thursday' => 'Perşembe',
        'friday' => 'Cuma',
        'saturday' => 'Cumartesi',
        'sunday' => 'Pazar'
    ];

    public function getStartTimeAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getEndTimeAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    // Haftalık günler için accessor'lar
    public function getMondayStartAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getMondayEndAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getTuesdayStartAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getTuesdayEndAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getWednesdayStartAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getWednesdayEndAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getThursdayStartAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getThursdayEndAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getFridayStartAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getFridayEndAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getSaturdayStartAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getSaturdayEndAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getSundayStartAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getSundayEndAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getStartDateAttribute($value)
    {
        return $value ? date('Y-m-d', strtotime($value)) : null;
    }

    public function getEndDateAttribute($value)
    {
        return $value ? date('Y-m-d', strtotime($value)) : null;
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

    public function userShifts()
    {
        return $this->hasMany(\App\Models\UserShift::class);
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

    /**
     * Haftalık çalışma saatlerini al
     */
    public function getWeeklySchedule(): array
    {
        $schedule = [];
        foreach (self::WEEKDAYS as $day => $dayName) {
            $schedule[$day] = [
                'name' => $dayName,
                'start' => $this->{$day . '_start'} ? date('H:i', strtotime($this->{$day . '_start'})) : null,
                'end' => $this->{$day . '_end'} ? date('H:i', strtotime($this->{$day . '_end'})) : null,
                'is_working_day' => !empty($this->{$day . '_start'}) && !empty($this->{$day . '_end'})
            ];
        }
        return $schedule;
    }

    /**
     * Belirli bir gün için çalışma saatlerini al
     */
    public function getDaySchedule(string $day): ?array
    {
        if (!array_key_exists($day, self::WEEKDAYS)) {
            return null;
        }

        $start = $this->{$day . '_start'};
        $end = $this->{$day . '_end'};

        if (!$start || !$end) {
            return null;
        }

        return [
            'day' => $day,
            'name' => self::WEEKDAYS[$day],
            'start' => date('H:i', strtotime($start)),
            'end' => date('H:i', strtotime($end)),
            'duration' => $this->calculateDuration($start, $end)
        ];
    }

    /**
     * İki saat arasındaki süreyi hesapla (dakika cinsinden)
     */
    private function calculateDuration(string $start, string $end): int
    {
        $startTime = strtotime($start);
        $endTime = strtotime($end);

        // Eğer bitiş saati başlangıç saatinden küçükse, ertesi gün olarak hesapla
        if ($endTime < $startTime) {
            $endTime += 24 * 60 * 60; // 24 saat ekle
        }

        return ($endTime - $startTime) / 60; // Dakika cinsinden döndür
    }

    /**
     * Haftalık toplam çalışma saatini hesapla
     */
    public function getWeeklyWorkingHours(): float
    {
        $totalMinutes = 0;

        foreach (array_keys(self::WEEKDAYS) as $day) {
            $start = $this->{$day . '_start'};
            $end = $this->{$day . '_end'};

            if ($start && $end) {
                $totalMinutes += $this->calculateDuration($start, $end);
            }
        }

        return $totalMinutes / 60; // Saat cinsinden döndür
    }

    /**
     * Haftalık çalışma günlerini al
     */
    public function getWorkingDays(): array
    {
        $workingDays = [];

        foreach (self::WEEKDAYS as $day => $dayName) {
            $start = $this->{$day . '_start'};
            $end = $this->{$day . '_end'};

            if ($start && $end) {
                $workingDays[] = $day;
            }
        }

        return $workingDays;
    }

    /**
     * Belirli bir gün çalışma günü mü kontrolü
     */
    public function isWorkingDay(string $day): bool
    {
        if (!array_key_exists($day, self::WEEKDAYS)) {
            return false;
        }

        $start = $this->{$day . '_start'};
        $end = $this->{$day . '_end'};

        return !empty($start) && !empty($end);
    }

    /**
     * Vardiya belirli bir tarihte aktif mi kontrolü
     */
    public function isActiveOnDate($date): bool
    {
        $checkDate = is_string($date) ? \Carbon\Carbon::parse($date) : $date;

        // Başlangıç tarihi kontrolü
        if ($this->start_date && $checkDate->lt(\Carbon\Carbon::parse($this->start_date))) {
            return false;
        }

        // Bitiş tarihi kontrolü (eğer tanımlıysa)
        if ($this->end_date && $checkDate->gt(\Carbon\Carbon::parse($this->end_date))) {
            return false;
        }

        return $this->is_active == 1;
    }

    /**
     * Vardiya geçerlilik tarih aralığını al
     */
    public function getValidityPeriod(): array
    {
        return [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_indefinite' => is_null($this->end_date)
        ];
    }

    /**
     * Scope: Belirli tarihte aktif vardialar
     */
    public function scopeActiveOnDate($query, $date)
    {
        $checkDate = is_string($date) ? $date : $date->format('Y-m-d');

        return $query->where('is_active', 1)
                    ->where(function($q) use ($checkDate) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', $checkDate);
                    })
                    ->where(function($q) use ($checkDate) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $checkDate);
                    });
    }
}
