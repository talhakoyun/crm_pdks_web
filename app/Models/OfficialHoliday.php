<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;

class OfficialHoliday extends BaseModel
{
    protected $table = 'official_holidays';
    protected $guarded = [];
    protected $dates = ['start_date', 'end_date', 'created_at', 'updated_at'];

    protected $casts = [
        'is_official' => 'boolean',
        'is_half_day' => 'boolean',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function type()
    {
        return $this->belongsTo(OfficialType::class, 'type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfficial($query)
    {
        return $query->where('is_official', true);
    }



    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * Tatilin oluşturanı.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Tatilin son güncelleyeni.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Tatilin gün sayısını hesaplar.
     *
     * @return int
     */
    public function getDaysCountAttribute()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        return $startDate->diffInDays($endDate) + 1;
    }

    /**
     * Tatilin formatlanmış tarih aralığını döndürür.
     *
     * @return string
     */
    public function getDateRangeAttribute()
    {
        $startDate = Carbon::parse($this->start_date)->format('d.m.Y');
        $endDate = Carbon::parse($this->end_date)->format('d.m.Y');

        if ($startDate === $endDate) {
            return $startDate;
        }

        return $startDate . ' - ' . $endDate;
    }
}
