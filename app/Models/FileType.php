<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_types';
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Aktif durumda olan dosya tiplerini getir
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Verilen uzantının bu dosya tipi için izin verilip verilmediğini kontrol eder
     *
     * @param string $extension
     * @return bool
     */
    public function isExtensionAllowed($extension)
    {
        // Uzantı boş ise, tüm uzantılara izin verilir
        if (empty($this->allowed_extensions)) {
            return true;
        }

        // Uzantıları dizi olarak ayır
        $allowedExtensions = explode(',', $this->allowed_extensions);

        // Verilen uzantı izin verilenler arasında mı?
        return in_array(strtolower($extension), array_map('strtolower', $allowedExtensions));
    }

    /**
     * Bu dosya tipine ait dosyaları getirir
     */
    public function userFiles()
    {
        return $this->hasMany(UserFile::class);
    }
}
