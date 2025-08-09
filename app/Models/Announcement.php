<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Announcement extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'start_date',
        'end_date',
        'send_type',
        'roles',
        'role_user_type',
        'role_users',
        'branches',
        'branch_user_type',
        'branch_users',
        'departments',
        'department_user_type',
        'department_users',
        'users',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'roles' => 'array',
        'role_users' => 'array',
        'branches' => 'array',
        'branch_users' => 'array',
        'departments' => 'array',
        'department_users' => 'array',
        'users' => 'array',
        'status' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    // İlişkiler
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'announcement_user')
            ->withPivot('is_read', 'read_at')
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'announcement_role');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'announcement_branch');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'announcement_department');
    }

    // Yardımcı metodlar
    public function isActive()
    {
        if (!$this->status) {
            return false;
        }

        $now = Carbon::now();
        return $now->between($this->start_date, $this->end_date);
    }

    public function getFormattedStartDate()
    {
        return $this->start_date ? $this->start_date->format('d.m.Y H:i') : null;
    }

    public function getFormattedEndDate()
    {
        return $this->end_date ? $this->end_date->format('d.m.Y H:i') : null;
    }

    public function markAsRead($userId)
    {
        $this->users()->updateExistingPivot($userId, [
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    public function markAsUnread($userId)
    {
        $this->users()->updateExistingPivot($userId, [
            'is_read' => false,
            'read_at' => null
        ]);
    }

    public function isReadBy($userId)
    {
        return $this->users()->wherePivot('user_id', $userId)->wherePivot('is_read', true)->exists();
    }
}
