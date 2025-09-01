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
        if (!$request->attributes->get('is_admin', false)) {
            return redirect()->route('backend.index')->with('error', 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return parent::list($request);
    }

    /**
     * Süper admin olmayan kullanıcılar şirket ekleme/düzenleme sayfasına erişemez
     */
    public function form(Request $request, $unique = NULL)
    {
        if (!$request->attributes->get('is_admin', false)) {
            return redirect()->route('backend.index')->with('error', 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return parent::form($request, $unique);
    }

    /**
     * Süper admin olmayan kullanıcılar şirket kaydedemez
     */
    public function save(Request $request, $unique = NULL)
    {
        if (!$request->attributes->get('is_admin', false)) {
            return redirect()->route('backend.index')->with('error', 'Bu işlemi gerçekleştirme yetkiniz bulunmamaktadır.');
        }

        // Manuel validation
        $rules = [
            'title' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:companies,email' . ($unique ? ',' . $unique : ''),
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $messages = [
            'title.required' => 'Şirket adı zorunludur.',
            'title.string' => 'Şirket adı metin olmalıdır.',
            'title.min' => 'Şirket adı en az 2 karakter olmalıdır.',
            'title.max' => 'Şirket adı en fazla 255 karakter olmalıdır.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçersiz e-posta adresi.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'phone.required' => 'Telefon numarası zorunludur.',
            'phone.string' => 'Telefon numarası metin olmalıdır.',
            'phone.max' => 'Telefon numarası en fazla 255 karakter olmalıdır.',
            'address.required' => 'Adres zorunludur.',
            'address.string' => 'Adres metin olmalıdır.',
            'address.max' => 'Adres en fazla 255 karakter olmalıdır.',
            'user_id.required' => 'Yönetici seçimi zorunludur.',
            'user_id.exists' => 'Seçilen yönetici geçersizdir.',
            'image.image' => 'Resim bir görsel olmalıdır.',
            'image.mimes' => 'Resim formatı geçersiz.',
            'image.max' => 'Resim en fazla 2MB olmalıdır.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            $rules,
            $messages
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
        if (!$request->attributes->get('is_admin', false)) {
            return response()->json(['status' => false, 'message' => 'Bu işlemi gerçekleştirme yetkiniz bulunmamaktadır.']);
        }

        return parent::delete($request);
    }
}
