<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use SoftDeletes, HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id';

    protected $guarded = [];
    protected $dates = ['last_login', 'birthday'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime'
    ];

    protected $guard = 'user';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->created_by = $user->id;
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
    }

    /**
     * Kullanıcının belirli bir yetkiye sahip olup olmadığını kontrol eder.
     *
     * @param string $permission İzin adı
     * @return bool Kullanıcı yetkili ise true, değilse false
     */
    public function hasPermission($permission): bool
    {
        // Bu kısım veritabanı yapınıza bağlı olarak değişecektir
        // Örnek olarak, role tablosundan veya bir permissions tablosundan
        // kullanıcının yetkilerini kontrol edebilirsiniz.

        // Şu an için, yönetici rolüne sahip kullanıcıların
        // tüm izinlere sahip olduğunu varsayıyoruz
        if ($this->role && $this->role->name === 'admin') {
            return true;
        }

        // Gerçek bir uygulamada, rol-izin ilişkisini kontrol edecek bir mekanizma olmalıdır
        // TODO: Rol ve izin ilişkisini gerçek veritabanı yapısına göre implement et

        // Şimdilik, bazı temel izinleri kontrol edelim (örnek)
        $managerPermissions = [
            'approve_holidays',
            'reject_holidays',
            'list_all_holidays'
        ];

        if ($this->role && $this->role->name === 'manager' && in_array($permission, $managerPermissions)) {
            return true;
        }

        return false;
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} {$this->surname}";
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function userBranches()
    {
        return $this->hasMany(UserBranches::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'user_branches', 'user_id', 'branch_id');
    }

    public function userZones()
    {
        return $this->hasMany(UserZones::class);
    }

    public function userFiles()
    {
        return $this->hasMany(UserFile::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function shiftFollows()
    {
        return $this->hasMany(ShiftFollow::class);
    }

    public function userDevices()
    {
        return $this->hasOne(UserDevice::class);
    }

    public function userShifts()
    {
        return $this->hasOne(UserShift::class);
    }

    public function userShift()
    {
        return $this->hasOne(UserShift::class)->where('is_active', 1);
    }

    public function userPermits()
    {
        return $this->hasOne(UserPermit::class);
    }

    public function weeklyHoliday()
    {
        return $this->hasOne(UserWeeklyHoliday::class)->where('is_active', 1);
    }

    public function weeklyHolidays()
    {
        return $this->hasMany(UserWeeklyHoliday::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
