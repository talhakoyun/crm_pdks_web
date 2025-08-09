<?php

namespace App\Traits;

use Storage;

use Illuminate\Http\Request;

trait ImageTrait {

    public static function verifyAndUpload(Request $request, $fieldname = 'image', $directory = 'images' ) {

        if( $request->hasFile( $fieldname ) ) {

            if (!$request->file($fieldname)->isValid()) {
                return null;
            }

            $file = $request->file($fieldname);
            if (number_format($file->getSize() / 1048576, 1) > 11)
                return Helpers::JsonWarning('Dosya formatı 10MB büyük olamaz.');

            if (strtolower($file->getClientOriginalExtension()) == "php" || strtolower($file->getClientOriginalExtension()) == "js" || strtolower($file->getClientOriginalExtension()) == "py")
                return Helpers::JsonWarning('Dosya yüklenemedi.');  

			$image = md5(rand(1,999999) . date('ymdhis')) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move( public_path("upload/$directory"), $image);

            return $image;
        }

        return null;

    }

}
