<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Schema;

class BaseModel extends Model
{
    use LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName($this->getTable())
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName}")
            ->logOnlyDirty();
    }
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->created_by = $user->id;

                // Şirket ID'sini ata
                if ((in_array('company_id', $model->getFillable()) ||
                    (!in_array('company_id', $model->getGuarded()) &&
                        Schema::hasColumn($model->getTable(), 'company_id')))) {
                    // Eğer model için company_id atanabilirse, kullanıcının şirket bilgisini kullan
                    if ($user->company_id && !$model->company_id) {
                        $model->company_id = $user->company_id;
                    }
                }

                // Şube ID'sini ata
                if ((in_array('branch_id', $model->getFillable()) || (!in_array('branch_id', $model->getGuarded()) &&
                    Schema::hasColumn($model->getTable(), 'branch_id')))) {
                    if ($user->branch_id && !$model->branch_id) {
                        $model->branch_id = $user->branch_id;
                    }
                }

                // Departman ID'sini ata
                if ((in_array('department_id', $model->getFillable()) ||
                    (!in_array('department_id', $model->getGuarded()) &&
                        Schema::hasColumn($model->getTable(), 'department_id')))) {
                    // Eğer model için department_id atanabilirse, kullanıcının departman bilgisini kullan
                    if ($user->department_id && !$model->department_id) {
                        $model->department_id = $user->department_id;
                    }
                }
            }
        });
        static::updating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->updated_by = $user->id;
            }
        });
        static::deleting(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->deleted_by = $user->id;
                $model->save();
            }
        });
        static::bootHook();
    }
    public static function bootHook() {}
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order', 'ASC');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
