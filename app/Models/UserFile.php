<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UserFile extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_files';
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['file_url', 'human_file_size'];


    /**
     * Dosyanın ait olduğu kullanıcı
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dosya tipi
     */
    public function fileType()
    {
        return $this->belongsTo(FileType::class);
    }

    /**
     * Dosya URL'i
     */
    public function getFileUrlAttribute()
    {
        return url('storage/' . $this->file_path);
    }

    /**
     * İnsan dostu dosya boyutu formatı
     */
    public function getHumanFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Dosyayı fiziksel olarak sil
     */
    public function deleteFile()
    {
        if (!empty($this->file_path) && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
            return true;
        }
        return false;
    }
}
