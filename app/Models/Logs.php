<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Support\Facades\Auth;


class Logs extends BaseModel
{
    use HasFactory;

    const UPDATED_AT = null;
    
    protected $table = 'logs';
    protected $guarded = []; 

    public static function boot()
    {
        parent::boot();

        static::creating(function($model)
        {  
            $user = Auth::user();
            if ($user) {
                $model->created_by = $user->id;
            }else{ 
                $api_user = Auth::guard('api')->user();
                if ($api_user){
                    $model->created_by = $api_user->id;
                } 
            }    
        });
    }

}
