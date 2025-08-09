<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route as RoutePackage;

class DevController extends Controller
{
    public function cache(Request $request)
    {
        Cache::flush();
        Artisan::call('route:cache');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        echo 'CACHE - OK';
    }

    public function pull(Request $request)
    {
        $hooks = array();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $hooks[$_SERVER['SERVER_ADDR']]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $output = curl_exec($ch);
        curl_close($ch);

        echo 'PULL - OK';
    }

    public function cacheall(Request $request)
    {
        $servers = array();

        foreach ($servers as $ip) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://" . $ip . "/dev/cache");

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $output = curl_exec($ch);

            curl_close($ch);

            echo '<pre>' . $ip . '</pre>';
        }
    }

    public function oneclick(Request $request)
    {
        $servers = array();

        foreach ($servers as $ip) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "#");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $output = curl_exec($ch);
            curl_close($ch);
            echo '<pre>' . $ip . '</pre>';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "#");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $output = curl_exec($ch);
            curl_close($ch);
            echo '<pre>' . $ip . '</pre>';
        }
    }
    public function routeToDB()
    {
        $routeCollection = RoutePackage::getRoutes();

        $types = [
            "list" => "Listele",
            "form" => "Ekle - Görüntüle",
            "save" => "Ekle - Kaydet",
            "delete" => "Sil",
            "show" => "Görüntüle",
            'detail' => 'Detay',
        ];
        foreach ($routeCollection->getRoutesByName() as $route => $data) {

            if (Route::withTrashed()->where('route_name', $route)->first() == null) {
                $addroute = new Route;
                if ($data->getController() != null && property_exists($data->getController(), 'title')) {

                    $name = $data->getController()->container->title;
                    $method = $data->getActionMethod();
                    $addroute->category_name = $name;

                    if (array_key_exists($method, $types)) {
                        $addroute->name = $types[$method];
                    }
                }

                $addroute->route_name = $route;
                $addroute->save();
            }
        }

        return "ok";
    }
}
