<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\CompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Şirket';
        $this->page = 'company';
        $this->upload = 'company';
        $this->model = new Company();
        $this->request = new CompanyRequest();

        $this->view = (object)array(
            'breadcrumb' => array(
                'Şirketler' => route('backend.company_list'),
            ),
        );

        view()->share('users', User::where('role_id', 3)->get());
        parent::__construct();
    }

    /**
     * Süper admin olmayan kullanıcılar şirket listesine erişemez
     */
    public function list(Request $request)
    {
        $roleData = $this->getRoleDataFromRequest($request);
        extract($roleData);
        if (!$isAdmin) {
            return redirect()->route('backend.index')->with('error', 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return parent::list($request);
    }

    /**
     * Süper admin olmayan kullanıcılar şirket ekleme/düzenleme sayfasına erişemez
     */
    public function form(Request $request, $unique = NULL)
    {
        $roleData = $this->getRoleDataFromRequest($request);
        extract($roleData);
        if (!$isAdmin) {
            return redirect()->route('backend.index')->with('error', 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return parent::form($request, $unique);
    }

    /**
     * Süper admin olmayan kullanıcılar şirket kaydedemez
     */
    public function save(Request $request, $unique = NULL)
    {
        $roleData = $this->getRoleDataFromRequest($request);
        extract($roleData);
        if (!$isAdmin) {
            return redirect()->route('backend.index')->with('error', 'Bu işlemi gerçekleştirme yetkiniz bulunmamaktadır.');
        }

        // CompanyRequest validation kurallarını kullan
        $companyRequest = new CompanyRequest();

        // Geçici olarak route parametresini set et
        app('request')->route()->setParameter('unique', $unique);

        $validator = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            $companyRequest->rules(),
            $companyRequest->messages()
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        return parent::save($request, $unique);
    }

    /**
     * Süper admin olmayan kullanıcılar şirket silemez
     */
    public function delete(Request $request)
    {
        $roleData = $this->getRoleDataFromRequest($request);
        extract($roleData);
        if (!$isAdmin) {
            return response()->json(['status' => false, 'message' => 'Bu işlemi gerçekleştirme yetkiniz bulunmamaktadır.']);
        }

        return parent::delete($request);
    }
}
