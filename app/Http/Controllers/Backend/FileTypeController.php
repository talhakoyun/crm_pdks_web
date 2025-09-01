<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\FileTypeRequest;
use App\Models\FileType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FileTypeController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Dosya Tipleri';
        $this->page = 'file_type';
        $this->upload = 'file_type';
        $this->model = new FileType();
        $this->request = new FileTypeRequest();
        $this->view = (object)array(
            'breadcrumb' => array(
                'Dosya Tipleri' => route('backend.file_type_list'),
            ),
        );

        parent::__construct();
    }

    /**
     * Dosya tipleri herkes tarafından görülebilir olduğu için
     * BaseController'daki company_id ve branch_id filtrelemesini bypass ediyoruz
     */
    public function list(Request $request)
    {
        if ($request->has('datatable')) {
            $select = $this->model::select();

            // İlişkileri ekleyelim
            $select = isset($this->relation) ? $select->with($this->relation) : $select;
            $obj = datatables()->of($select);

            $obj = $obj
                ->editColumn('created_at', function ($item) {
                    return (!is_null($item->created_at) ? Carbon::parse($item->created_at)->format('d.m.Y H:i') : '-');
                })
                ->editColumn('updated_at', function ($item) {
                    return (!is_null($item->updated_at) ? Carbon::parse($item->updated_at)->format('d.m.Y H:i') : '-');
                })
                ->editColumn('is_active', function ($item) {
                    return $item->is_active == 1 ? '<span class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm"> Aktif </span>' : '<span class="bg-danger-focus text-danger-600 border border-danger-main px-24 py-4 radius-4 fw-medium text-sm"> Pasif </span>';
                })
                ->editColumn('created_by', function ($item) {
                    return $item->createdBy->fullname ?? null;
                })
                ->rawColumns(['is_active'])
                ->escapeColumns([]);

            // Özelleştirmeler varsa uygulayalım
            if (method_exists($this, 'datatableHook')) {
                $obj = $this->datatableHook($obj);
            }
            $obj = $obj->addIndexColumn()->make(true);

            return $obj;
        }

        return view("backend.$this->page.list");
    }

    /**
     * Dosya tipleri için form - company_id ve branch_id otomatik atanmasın
     */
    public function form(Request $request, $unique = NULL)
    {
        if (!is_null($unique)) {
            // Mevcut kayıt
            $item = $this->model::find((int)$unique);

            // Kayıt bulunamadı
            if (is_null($item)) {
                return redirect()->back()->with('error', 'Kayıt bulunamadı');
            }

            // Form hook varsa uygula
            if (method_exists($this, 'formHook')) {
                $item = $this->formHook($item);
            }
        } else {
            // Yeni kayıt - company_id ve branch_id otomatik atanmasın
            $item = new $this->model;
        }

        return view("backend.$this->page.form", compact('item'));
    }

    /**
     * Dosya tipleri için save - company_id ve branch_id erişim kontrolü yapılmasın
     */
    public function save(Request $request, $unique = NULL)
    {
        // Form Request Validation
        if (isset($this->request) && is_object($this->request)) {
            try {
                $formRequestInstance = $this->request;
                $rules = $formRequestInstance->rules();
                $messages = method_exists($formRequestInstance, 'messages') ? $formRequestInstance->messages() : [];

                // Eğer düzenleme yapılıyorsa, image/file alanlarını opsiyonel yap
                if ($unique != null) {
                    $imageFields = ['image', 'logo', 'icon', 'banner'];
                    foreach ($imageFields as $field) {
                        if (isset($rules[$field])) {
                            $rules[$field] = str_replace('required|', '', $rules[$field]);
                            $rules[$field] = str_replace('required', '', $rules[$field]);
                            if (empty($rules[$field])) {
                                $rules[$field] = 'image|max:2048|mimes:jpeg,png,jpg';
                            } else {
                                $rules[$field] = 'image|max:2048|mimes:jpeg,png,jpg|' . $rules[$field];
                            }
                        }
                    }
                }

                $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Form Request validation failed: ' . $e->getMessage());
            }
        }

        // saveHook - parametreleri filtrele/düzenle
        $params = $request->all();
        if (method_exists($this, 'saveHook')) {
            $params = $this->saveHook($request);
        }

        // Dosya yükleme işlemleri
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (number_format($file->getSize() / 1048576, 1) > 11)
                return redirect()->back()->with('error', 'Dosya formatı 10MB büyük olamaz.')->withInput();

            if (strtolower($file->getClientOriginalExtension()) == "php" || strtolower($file->getClientOriginalExtension()) == "js" || strtolower($file->getClientOriginalExtension()) == "py")
                return redirect()->back()->with('error', 'Dosya yüklenemedi.')->withInput();

            $image = md5(rand(1, 999999) . date('ymdhis')) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move("upload/$this->upload", $image);
            $params['image'] = $image;
        }

        // Veritabanı işlemleri
        try {
            if (is_null($unique)) {
                $obj = $this->model::create($params);
            } else {
                $obj = $this->model::find((int)$unique);
                if (!$obj) {
                    return redirect()->back()->with('error', 'Kayıt bulunamadı.');
                }
                $obj->update($params);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error('Database error in FileTypeController save: ' . $e->getMessage());

            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return redirect()->back()->with('error', 'Bu kayıt zaten mevcut. Lütfen farklı değerler kullanın.')->withInput();
            }

            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return redirect()->back()->with('error', 'Bu kayıt başka kayıtlar tarafından kullanıldığı için işlem yapılamadı.')->withInput();
            }

            return redirect()->back()->with('error', 'Veritabanı hatası oluştu. Lütfen tekrar deneyin.')->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('General error in FileTypeController save: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Bir hata oluştu. Lütfen tekrar deneyin.')->withInput();
        }

        \Illuminate\Support\Facades\Cache::flush();
        if (method_exists($this, 'saveBack')) {
            return $this->saveBack($obj);
        }

        return redirect()->route("backend." . $this->page . "_list")->with('success', 'Kayıt başarılı şekilde işlendi');
    }

    public function delete(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->role_id == 2;
        $isSuperAdmin = $user->role_id == 1;

        if (!$isAdmin && !$isSuperAdmin) {
            return response()->json(['status' => false, 'message' => 'Bu işlemi gerçekleştirme yetkiniz bulunmamaktadır.']);
        }

        return parent::delete($request);
    }
}
