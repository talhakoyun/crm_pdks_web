<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\DebitDeviceRequest;
use App\Models\DebitDevice;
use Illuminate\Http\Request;

class DebitDeviceController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Zimmetler';
        $this->page = 'debit_device';
        $this->upload = 'debit_device';
        $this->model = new DebitDevice();
        $this->request = new DebitDeviceRequest();

        $this->view = (object)array(
            'breadcrumb' => array(
                'Zimmetler' => route('backend.debit_device_list'),
            ),
        );
        parent::__construct();
    }

    /**
     * Cihaz silme işlemi öncesi kontrol
     * Eğer cihaz herhangi bir personele atanmışsa silinmesini engelle
     */
    public function delete(Request $request)
    {
        $device = $this->model::find((int)$request->post('id'));

        if (!$device) {
            return response()->json(['status' => false, 'message' => 'Cihaz bulunamadı']);
        }

        // Cihazın atanmış olup olmadığını kontrol et
        if ($device->isAssigned()) {
            $assignedUser = $device->assignedUser();
            $userName = $assignedUser ? $assignedUser->name . ' ' . $assignedUser->surname : 'bir personel';

            return response()->json([
                'status' => false,
                'message' => "Bu cihaz şu anda {$userName}e atanmış durumda. Atanmış cihazlar silinemez."
            ]);
        }

        // Eğer cihaz atanmamışsa normal silme işlemini devam ettir
        return parent::delete($request);
    }
}
