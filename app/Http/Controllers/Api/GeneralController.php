<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function isAvailable(Request $request)
    {
        $data = array(
            'android' => array(
                'usable' => true,
                'message' => "Mesaj Android",
                'version' =>  (int)str_replace(".", "", env('ANDROID_VERSION')),
                'link' => env('ANDROID_LINK'),
                'is_register' => true,
            ),
            'ios' => array(
                'usable' => false,
                'message' => "Mesaj iOS",
                'version' => (int)str_replace(".", "", env('IOS_VERSION')),
                'link' => env('IOS_LINK'),
                'is_register' => true,
            ),
        );

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => "İşlem Başarılı"
        ], 200);
    }
}
