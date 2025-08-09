<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\BranchRequest;
use App\Models\Branch;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class BranchController extends BaseController
{
    public function __construct()
    {
        $this->title = 'Şube';
        $this->page = 'branch';
        $this->upload = 'branch';
        $this->model = new Branch();
        $this->request = new BranchRequest();
        $this->relation = ['zone'];
        $this->view = (object)array(
            'breadcrumb' => array(
                'Şubeler' => route('backend.branch_list'),
            ),
        );
        parent::__construct();
    }

    public function saveHook(Request $request)
    {
        // Leaflet harita parametrelerini otomatik filtreleme
        $params = $request->all();
        $filteredParams = [];

        foreach ($params as $key => $value) {
            // leaflet-base-layers ile başlayan veya _leaflet ile biten tüm parametreleri hariç tut
            if (!str_starts_with($key, 'leaflet-') && !str_ends_with($key, '_leaflet') && $key !== 'positions') {
                $filteredParams[$key] = $value;
            }
        }

        return $filteredParams;
    }

    public function saveBack($obj)
    {
        $request = request();
        $params = $request->all();

        // Positions parametresini izole et, diğer leaflet parametrelerini yok say
        if ($request->has('positions')) {
            try {
                $positions = Polygon::fromJson($request->input('positions'));

                $zone = Zone::query()->updateOrCreate(
                    ['branch_id' => $obj->id],
                    [
                        'positions' => $positions,
                        'branch_id' => $obj->id,
                        'company_id' => Auth::user()->company_id,
                    ]
                );
            } catch (\Exception $e) {
                // Hatayı sessizce geç, belki ileride log eklenir
            }
        }

        return redirect()->route('backend.branch_list')->with('success', 'Şube başarıyla kaydedildi');
    }
}
