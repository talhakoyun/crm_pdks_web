<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class DebitDevice extends BaseModel
{
    use SoftDeletes;

    protected $table = 'debit_devices';
    protected $guarded = ['id'];

    /**
     * Bu cihazın zimmet atamaları
     */
    public function assignments()
    {
        return $this->hasMany(UserDebitDevice::class);
    }

    /**
     * Cihazın aktif zimmet ataması
     */
    public function activeAssignment()
    {
        return $this->hasOne(UserDebitDevice::class)
                    ->where('status', 'active')
                    ->latest();
    }

    /**
     * Cihazın şu anda atanmış olup olmadığını kontrol eder
     */
    public function isAssigned()
    {
        return $this->activeAssignment()->exists();
    }

    /**
     * Cihazın atandığı kullanıcıyı döndürür (varsa)
     */
    public function assignedUser()
    {
        $assignment = $this->activeAssignment;
        return $assignment ? $assignment->user : null;
    }
}
