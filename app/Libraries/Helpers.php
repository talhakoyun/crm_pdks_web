<?php

namespace App\Libraries;

use App\Models\Logs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Countable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\TempFile;
use Symfony\Component\HttpFoundation\FileBag;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

class Helpers
{
    public static function activeMenu($groupArr)
    {
        if (Route::currentRouteName() == 'backend.' . $groupArr[0]) {
            return 'active open';
        }

        $temp = array();
        foreach ($groupArr as $value) {
            array_push($temp, 'backend.' . $value . '_list');
            array_push($temp, 'backend.' . $value . '_form');
            array_push($temp, 'backend.' . $value . '_show');
            array_push($temp, 'backend.' . $value . '_detail');
        }

        return in_array(Route::currentRouteName(), $temp) ? 'active open' : '';
    }

    public static function hasPermission($route_names)
    {
        $onuser = Auth::user();
        $permissions = $onuser->role['permissions'] ?? null;
        if (!$permissions) {
            return false;
        }

        $permissions = json_decode($permissions, true);
        foreach ((array) $route_names as $route_name) {
            if (in_array($route_name, $permissions)) {
                return true;
            }

            foreach ($permissions as $permission) {
                if (str_starts_with($permission, "backend." . $route_name)) {
                    return true;
                }
            }
        }

        return false;
    }

    // public static function sendsmsSOAP($telefon, $message, $ip, $user_agent, $otp = 0, $multiple = null)
    // {

    //     if ($multiple)
    //         $allPhone = implode(",", $telefon);
    //     else
    //         $allPhone =  '<Receiver>' . $telefon . '</Receiver>';

    //     $quer = array("phone" => $telefon, "message" => $message, "otp" => $otp);
    //     $message = trim($message);

    //     $quer = array("phone" => $telefon, "message" => $message, "otp" => $otp);
    //     $message = trim($message);

    //     $username = env('SOAP_USERNAME');
    //     $password = env('SOAP_PASWORD');
    //     $usercode = env('SOAP_USERCODE');
    //     $accountID = env('SOAP_ACCOUNTID', null);
    //     if ($otp == 1) {
    //         $accountID = env('SOAPSMS_ACCOUNTID_OTP', null);
    //     }
    //     $originator = env('SOAP_ORGINATOR');

    //     $url = "https://webservice.asistiletisim.com.tr/SmsProxy.asmx?WSDL";
    //     $request_headers = array(
    //         "Content-Type: text/xml; charset=utf-8",
    //         "SOAPAction: https://webservice.asistiletisim.com.tr/SmsProxy/sendSms",
    //         "Accept: text/xml"
    //     );

    //     $xml = '
    //         <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns="https://webservice.asistiletisim.com.tr/SmsProxy">
    //         <soapenv:Header/>
    //             <soapenv:Body>
    //                 <sendSms>
    //                     <requestXml>
    //                     <![CDATA[
    //                         <SendSms>
    //                             <Username>' . $username . '</Username>
    //                             <Password>' . $password . '</Password>
    //                             <UserCode>' . $usercode . '</UserCode>
    //                             <AccountId>' . $accountID . '</AccountId>
    //                             <Originator>' . $originator . '</Originator>
    //                             <SendDate></SendDate>
    //                             <ValidityPeriod>60</ValidityPeriod>
    //                             <MessageText>' . $message . '</MessageText>
    //                             <IsCheckBlackList>0</IsCheckBlackList>
    //                             <ReceiverList>
    //                                 ' . $allPhone . '
    //                             </ReceiverList>
    //                         </SendSms>
    //                     ]]>
    //                     </requestXml>
    //                 </sendSms>
    //             </soapenv:Body>
    //         </soapenv:Envelope>
    //         ';

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_VERBOSE, 1);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

    //     if (curl_errno($ch)) {
    //         return 999;
    //     } else {
    //         $response = curl_exec($ch);
    //         curl_close($ch);

    //         $xml = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response);
    //         $xml = simplexml_load_string($xml);
    //         $xml = json_decode(json_encode($xml), TRUE);
    //         $response_code = $xml["Body"]["sendSmsResponse"]["sendSmsResult"]["ErrorCode"];
    //         if ($multiple) {
    //             SmsUser::latest()->first()->update([
    //                 'response' => !is_null($xml["Body"]["sendSmsResponse"]["sendSmsResult"]["MessageIdList"] ?? null) ? json_encode($xml["Body"]["sendSmsResponse"]["sendSmsResult"]["MessageIdList"]) : NULL,
    //                 'list_id' => $xml["Body"]["sendSmsResponse"]["sendSmsResult"]["PacketId"] ?? 0,
    //                 'status' => $xml["Body"]["sendSmsResponse"]["sendSmsResult"]["ErrorCode"] == 0 ? 1 : $xml["Body"]["sendSmsResponse"]["sendSmsResult"]["ErrorCode"],
    //             ]);
    //         } else {
    //             Helpers::logInsert("SMS", $telefon, $quer, $response_code == 0 ? 1 : $response_code, $ip, $user_agent);
    //         }

    //         return $response_code;
    //     }
    // }

    public static function JsonWarning($message)
    {
        return response()->json(['status' => false, 'message' =>  $message], 400);
    }

    public static function JsonWarningEmpty($message)
    {
        return response()->json(['status' => true, 'data' => [], 'message' =>  $message], 200);
    }

    public static function JsonSuccess($params = [])
    {
        return response()->json(['status' => $params['status'], 'data' => $params['data'], 'message' =>  $params['message']], $params['code']);
    }

    // public static function sendSMS($phone, $message)
    // {
    //     $response = Curl::to('https://api.netgsm.com.tr/sms/send/get/')
    //         ->withData(array(
    //             'usercode' => env('SMS_USERNAME', env('SMS_USERNAME')),
    //             'password' => env('SMS_PASSWORD', env('SMS_HEADER')),
    //             'msgheader' => env('SMS_HEADER', env('SMS_PASSWORD')),
    //             'gsmno' => $phone,
    //             'message' => $message,
    //         ))
    //         ->get();

    //     return explode(' ', $response)[0] == '00';
    // }

    // public static function validationArray($messages = NULL)
    // {
    //     $message = array();
    //     foreach ($messages->get('*') as $item) {
    //         array_push($message, $item[0]);
    //     }

    //     return $message;
    // }


    // public static function tr_strtoupper($text)
    // {
    //     $text = mb_strtoupper(Helpers::tr_up($text), 'UTF-8');
    //     return $text;
    // }

    // public static function tr_up($str)
    // {
    //     $str = str_replace('i', 'İ', $str);
    //     $str = str_replace('ı', 'I', $str);
    //     return $str;
    // }

    // public static function shortURL($url)
    // {
    //     $url = file_get_contents("http://tinyurl.com/api-create.php?url=$url");

    //     return str_replace('https://', '', $url);
    // }


    public static function PhoneNumber($phone)
    {
        return  preg_replace("/[^0-9]/", "", $phone);
    }

    public static function logInsert($source, $what, $before, $status, $ip, $user_agent)
    {
        Logs::create([
            'source' => $source,
            'what' => $what,
            'before' =>  json_encode($before),
            'status' => $status,
            'ip' => $ip,
            'user_agent' => $user_agent
        ]);
    }

    // public static function totalAmount($product_id)
    // {
    //     $total = 0;
    //     $girisId = TransactionType::where('type_id', '1')->first()->id;
    //     $cikisId = TransactionType::where('type_id', '2')->first()->id;
    //     $giris = Transaction::where('product_id', $product_id)->where('type_id', $girisId)->sum('amount');
    //     $cikis = Transaction::where('product_id', $product_id)->where('type_id', $cikisId)->sum('amount');
    //     $total = $giris - $cikis;
    //     return $total;
    // }

    // public static function entryAmount($product_id)
    // {
    //     $girisId = TransactionType::where('slug', 'giris')->first()->id;
    //     return Transaction::where('product_id', $product_id)->where('type_id', $girisId)->sum('amount');
    // }

    // public static function exitAmount($product_id)
    // {
    //     $cikisId = TransactionType::where('slug', 'cikis')->first()->id;
    //     return Transaction::where('product_id', $product_id)->where('type_id', $cikisId)->sum('amount');
    // }

    public static function str($string = null)
    {
        if (func_num_args() === 0) {
            return new class
            {
                public function __call($method, $parameters)
                {
                    return Str::$method(...$parameters);
                }

                public function __toString()
                {
                    return '';
                }
            };
        }

        return Str::of($string);
    }

    public static function str_slug_tr($str)
    {
        if (is_array($str)) {
            $str = implode(' ', $str);
        }

        $str = str_replace(
            ['Ö', 'ö', 'Ü', 'ü', 'Ş', 'ş', 'I', 'ı', 'İ'],
            ['O', 'o', 'U', 'u', 'S', 's', 'i', 'i', 'i'],
            $str
        );

        return Str::slug($str);
    }

    // public static function tl($money, $icon = false)
    // {
    //     // $money = (int) $money;
    //     return number_format($money, 2, ",", ".") . ($icon ? " ₺" : "");
    // }

    /**
     * Create a collection from the given value.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>|null  $value
     * @return \Illuminate\Support\Collection<TKey, TValue>
     */
    public static function collect($value = [])
    {
        return new Collection($value);
    }

    public static function tempFile(FileBag $files)
    {
        $request = request();
        $token = $request->header("X-CSRF-TOKEN");
        $list = collect();
        foreach ($files->all() as $file) {
            $uniqId = uniqid();
            $result = Storage::disk("temp_path")
                ->putFileAs(
                    'upload',
                    $file,
                    $uniqId
                );

            $list->push(TempFile::query()->create([
                "path" => $result,
                "token" => $token,
                "name" => $file->getClientOriginalName(),
                "fileable_name" => $request->get("fname", "file"),
                "type" => $file->getMimeType(),
                "fileable_type" => $request->get("ftype", "file"),
                "ip" => $request->ip(),
                "user_agent" => $request->userAgent(),
                "referer" => $request->headers->get("referer"),
                "origin" => $request->headers->get("origin"),
                "expires_at" => Carbon::now()->addMinutes(30),
            ]));
        }

        return $list;
    }

    public static function tempFileManager($token, Countable $tempFileIds, Model $model)
    {
        if (!method_exists($model, 'files')) return $model;

        if (!($model->files() instanceof MorphMany) and !($model->files() instanceof MorphOne) and !($model->files() instanceof MorphOneOrMany)) return $model;

        $temp_files = TempFile::query()->whereIn("id", $tempFileIds)->where("token", $token)->get();

        if ($temp_files->count() <= 0) return $model;
        $m_class = mb_split('\\\\', $model::class);

        $directory = public_path("upload/" . Str::lower(Arr::last($m_class)));
        $files = collect();

        if (!FacadesFile::exists($directory)) {
            FacadesFile::makeDirectory($directory, 0777, true);
        }


        //Move file to storage
        foreach ($temp_files as $temp_file) {
            $old_path = storage_path('temp/' . $temp_file->path);
            $new_file_path = Str::lower(Arr::last($m_class)) . "/" . Helpers::str(now()->format("YmdHis") . "-" . $temp_file->name)->snake()->__toString();
            $new_path = $directory . "/" . Helpers::str(now()->format("YmdHis") . "-" . $temp_file->name)->snake()->__toString();

            $r = FacadesFile::copy($old_path, $new_path);

            if ($r) {
                $model->files()->create([
                    "name" => $temp_file->fileable_name,
                    "type" => $temp_file->fileable_type,
                    "extension" => $temp_file->type,
                    "path" => $new_file_path,
                    "disk" => "public_path",
                ]);

                $temp_file->delete();
                FacadesFile::delete($old_path);
            }
        }
        return $model;
    }

    public static function fileManager(Model $model, \Symfony\Component\HttpFoundation\File\UploadedFile $file, $name = 'file', $disk = 'public_path')
    {
        if ($model->id == null) {
            return $model;
        }

        if (!method_exists($model, 'files')) {
            return $model;
        }

        if (!($model->files() instanceof MorphMany) and !($model->files() instanceof MorphOne) and !($model->files() instanceof MorphOneOrMany)) {
            return $model;
        }
        $m_class = mb_split('\\\\', $model::class);

        $file = Storage::disk($disk)
            ->putFileAs('upload/' . Str::lower(Arr::last($m_class)), $file, Str::snake(Date::now()->timestamp) . " " . $file->getClientOriginalName());

        $model->files()->create([
            'path' => $file,
            'name' => $name,
        ]);

        return $model;
    }
}
