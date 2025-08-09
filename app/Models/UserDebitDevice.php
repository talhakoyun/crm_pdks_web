<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class UserDebitDevice extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_debit_devices';
    protected $guarded = [];
    protected $dates = ['start_date', 'end_date', 'deleted_at'];

    /**
     * İlişkili kullanıcı
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * İlişkili zimmet cihazı
     */
    public function debitDevice()
    {
        return $this->belongsTo(DebitDevice::class);
    }

    /**
     * Aktif zimmetleri filtrele
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Belirli bir tarih aralığındaki zimmetleri filtrele
     */
    public function scopeByDateRange($query, $date)
    {
        return $query->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date);
    }

    /**
     * Süresi dolmuş zimmetleri filtrele
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now()->format('Y-m-d'))
                    ->where('status', 'active');
    }

    /**
     * Belirli bir kullanıcının zimmetlerini filtrele
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Belirli bir cihazın zimmetlerini filtrele
     */
    public function scopeByDevice($query, $deviceId)
    {
        return $query->where('debit_device_id', $deviceId);
    }
}
