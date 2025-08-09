<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use App\Models\Route as RouteModel;


class SaveRoutesToDatabase extends Command
{
    protected $signature = 'routes:save-to-database';
    protected $description = 'Mevcut tüm rotaları veritabanına kaydeder';

    public function handle()
    {
        $routes = Route::getRoutes();
        $this->info('Toplam ' . count($routes) . ' route bulundu.');
        $saveCount = 0;

        foreach ($routes as $route) {
            $routeName = $route->getName();

            // Route adı yoksa veya hariç tutulan bir route ise atla
            if (!$routeName || $this->shouldExcludeRoute($routeName)) {
                continue;
            }

            // getDesc metodu olmayabilir veya null dönebilir
            $category_name = $this->getCategoryName($routeName);

            RouteModel::updateOrCreate(
                ['route_name' => $routeName],
                [
                    'name' => $this->getRouteDescription($routeName),
                    'route_name' => $routeName,
                    'category_name' => $category_name,
                ]
            );

            $saveCount++;
        }

        $this->info($saveCount . ' route başarıyla veritabanına kaydedildi!');
    }

    protected function getCategoryName($routeName)
    {
        // Route isminden kategori ismini çıkar
        $parts = explode('.', $routeName);
        if (count($parts) < 2) return '';

        $category = $parts[1]; // backend.project_type_list gibi bir isimden project_type kısmını al

        // Kategorileri daha spesifikten daha genele doğru sıralayalım
        $categories = [
            'user_shift_custom' => 'Özel Vardiya Tanımlamaları',
            'shift_definition' => 'Vardiya Tanımlamaları',
            'shift_follow' => 'Giriş-Çıkış Kayıtları',
            'user_debit_device' => 'Zimmet Atamaları',
            'debit_device' => 'Zimmet Cihazları',
            'department' => 'Departmanlar',
            'company' => 'Şirketler',
            'branch' => 'Şubeler',
            'menu' => 'Menüler',
            'role' => 'Roller',
            'user' => 'Kullanıcılar',
            'holiday' => 'İzin Talepleri',
            'official_holiday' => 'Resmi Tatil Günleri',
            'file_type' => 'Dosya Tipleri',
            'user_file' => 'Personel Dosyaları',
            'announcements' => 'Duyurular',
            'event' => 'Etkinlikler'
        ];

        // Tam eşleşmeleri önce deneyelim
        if (isset($categories[$category])) {
            return $categories[$category];
        }

        // Tam eşleşme yoksa, daha akıllı bir eşleştirme yapalım
        // Önce en uzun anahtarları kontrol edelim
        $keys = array_keys($categories);
        usort($keys, function ($a, $b) {
            return strlen($b) - strlen($a); // Uzunluğa göre azalan sıralama
        });

        foreach ($keys as $key) {
            // Kategori tam olarak bu anahtar ile başlıyorsa veya bu anahtarı içeriyorsa
            if (strpos($category, $key) === 0 || strpos($category, '_' . $key) !== false) {
                return $categories[$key];
            }
        }

        return ucfirst($category);
    }

    protected function shouldExcludeRoute($routeName)
    {
        // Profil, login ve livewire ile ilgili routeları hariç tut
        if (strpos($routeName, 'profile') !== false) return true;
        if (strpos($routeName, 'signin') !== false) return true;
        if (strpos($routeName, 'signup') !== false) return true;
        if (strpos($routeName, 'logout') !== false) return true;
        if (strpos($routeName, 'livewire') !== false) return true;
        if (strpos($routeName, 'backend.index') !== false) return true;
        if (strpos($routeName, 'forgot') !== false) return true;
        if (strpos($routeName, 'exchange_rate') !== false) return true;
        if (strpos($routeName, 'get_exchange_rate') !== false) return true;

        // Backend routeları dışındakileri hariç tut
        if (strpos($routeName, 'backend.') === false) return true;

        return false;
    }

    protected function getRouteDescription($routeName)
    {
        $patterns = [
            'list' => 'Listele',
            'form' => 'Form Görüntüle',
            'save' => 'Kaydet',
            'detail' => 'Detay',
            'add' => 'Ekle',
            'edit' => 'Düzenle',
            'reorder' => 'Sıralama',
            'upload_file' => 'Dosya Yükle',
            'upload_files' => 'Dosyaları Yükle',
            'delete_file' => 'Dosya Sil',
            'get_files' => 'Dosyaları Getir',
            'get_exchange_rate' => 'Döviz Kuru Getir',
            'change_status' => 'Durum Değiştir',
            'fetch' => 'Getir',
            'bulk_add' => 'Toplu Ekle',
            'calendar' => 'Takvim',
            'get_events' => 'Etkinlikleri Getir',
            'get_upcoming_holidays' => 'Yaklaşan Tatil Günleri Getir',
            'get_active_holidays' => 'Aktif Tatil Günleri Getir',
            'return' => 'Teslim Alındı Olarak İşaretle',
            'upload_temp' => 'Geçici Dosya Yükle',
            'delete_temp' => 'Geçici Yüklenmiş Dosyayı Sil',
            'delete' => 'Sil',
            'fetch' => 'Resmi Tatil Günlerini Getir',
            'bulk_add' => 'Kullanıcılara Toplu Ekle',
            'get_users' => 'Kullanıcıları Getir',
            'participants' => 'Katılımcıları Getir',
            'participant_status' => 'Katılımcı Durumu Güncelle',
            'participant_bulk_status' => 'Toplu Katılımcı Durumu Güncelle',
            'user_debit_device_return' => 'Zimmet Teslim Alma'
        ];

        foreach ($patterns as $key => $desc) {
            if (strpos($routeName, $key) !== false) {
                return $desc;
            }
        }

        return 'İşlem';
    }
}
