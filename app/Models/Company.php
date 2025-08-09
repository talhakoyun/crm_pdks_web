<?php

namespace App\Models;

class Company extends BaseModel
{
    protected $table = 'companies';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function bootHook()
    {
        static::created(function ($model) {
            if ($model->user_id) {
                $user = User::find($model->user_id);
                if ($user) {
                    $user->company_id = $model->id;
                    $user->save();
                }
            }
        });
    }
}
