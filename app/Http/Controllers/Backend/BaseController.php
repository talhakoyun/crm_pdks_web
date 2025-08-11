<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait BasePattern
{
    protected $title;
    protected $page;
    protected $model;
    protected $listQuery;
    protected $upload;
    protected $validation;
    protected $view;
    protected ?array $attributes;
    protected $relation;
    protected $request;
}

class BaseController extends Controller
{
    use BasePattern;

    public function __construct()
    {
        $container = (object)array(
            'title' => $this->title ?? null,
            'page' => $this->page ?? null,
            'model' => $this->model ?? null,
            'upload' => $this->upload ?? null,
            'view' => $this->view ?? null,
            'relation' => $this->relation ?? null,
            'request' => $this->request ?? null,
        );
        View::share('container', $container);
    }

    public function list(Request $request)
    {
        if ($request->has('datatable')) {
            $select = (isset($this->listQuery) ? $this->listQuery : $this->model::select());

            // Rol ve erişim kontrolü
            $isSuperAdmin = $request->attributes->get('is_super_admin', false);
            $isAdmin = $request->attributes->get('is_admin', false);
            $isCompanyOwner = $request->attributes->get('is_company_owner', false);
            $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
            $isBranchAdmin = $request->attributes->get('is_branch_admin', false);
            $isDepartmentAdmin = $request->attributes->get('is_department_admin', false);
            $companyId = $request->attributes->get('company_id');
            $branchId = $request->attributes->get('branch_id');
            $departmentId = $request->attributes->get('department_id');
            $userId = $request->attributes->get('user_id');
            $loggedInUserId = Auth::check() ? Auth::id() : null;

            $table = $this->model->getTable();
            $columns = Schema::getColumnListing($table);

            // Süper Admin ve Admin tüm verilere erişebilir
            if (!$isSuperAdmin && !$isAdmin) {
                // Şirket bazlı filtreleme
                if (in_array('company_id', $columns) && !is_null($companyId)) {
                    $select->where("$table.company_id", $companyId);
                } else if (in_array('branch_id', $columns) && !is_null($branchId) && !$isCompanyOwner && !$isCompanyAdmin) {
                    $select->where("$table.branch_id", $branchId);
                } else if (in_array('department_id', $columns) && !is_null($departmentId) && !$isBranchAdmin) {
                    $select->where("$table.department_id", $departmentId);
                }else if ($isDepartmentAdmin && !in_array('department_id', $columns) && in_array('created_by', $columns)) {
                    // İki durum için OR koşulu:
                    // 1. Kaydın created_by'ı kullanıcının kendisi veya
                    // 2. Kaydın created_by'ı kullanıcının departmanındaki biri
                    $departmentUserIds = \App\Models\User::where('department_id', $departmentId)->pluck('id')->toArray();

                    $select->where(function ($query) use ($loggedInUserId, $departmentUserIds) {
                        $query->whereIn('created_by', $departmentUserIds)
                            ->orWhere('created_by', $loggedInUserId);
                    });
                }else  if (in_array('user_id', $columns) && !is_null($userId) && !$isDepartmentAdmin) {
                    $select->where("$table.user_id", $userId);
                }
            }

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
                ->editColumn('start_date', function ($item) {
                    return (!is_null($item->start_date) ? Carbon::parse($item->start_date)->format('d.m.Y H:i') : '-');
                })
                ->editColumn('end_date', function ($item) {
                    return (!is_null($item->end_date) ? Carbon::parse($item->end_date)->format('d.m.Y H:i') : '-');
                })
                ->editColumn('status', function ($item) {
                    return $item->status == 'active' ? '<span class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm"> Aktif </span>' : '<span class="bg-danger-focus text-danger-600 border border-danger-main px-24 py-4 radius-4 fw-medium text-sm"> Pasif </span>';
                })
                ->editColumn('deleted_at', function ($item) {
                    return (!is_null($item->deleted_at) ? Carbon::parse($item->deleted_at)->format('d.m.Y H:i') : '-');
                })
                ->editColumn('last_login', function ($item) {
                    return (!is_null($item->last_login) ? Carbon::parse($item->last_login)->format('d.m.Y H:i') : '-');
                })
                ->editColumn('image', function ($item) {
                    return !is_null($item->image) ? '<img src="' . env('CDN_URL') . '/upload/' . $this->upload . '/' . $item->image . '" class="img-fluid" style="width: 100px; height: 100px;">' : NULL;
                })
                ->editColumn('is_active', function ($item) {
                    return $item->is_active == 1 ? '<span class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm"> Aktif </span>' : '<span class="bg-danger-focus text-danger-600 border border-danger-main px-24 py-4 radius-4 fw-medium text-sm"> Pasif </span>';
                })
                ->editColumn('image', function ($item) {

                    $image = !is_null($item->image) ?  (env('CDN_URL') . "/upload/" . $this->upload . "/" . $item->image) : (env("CDN_URL") . '/assets/images/default.png');
                    return "<img src='$image' class='img-fluid' style='width: 100px; height: 100px;'>";
                })
                ->editColumn('created_by', function ($item) {
                    return $item->createdBy->fullname ?? null;
                })
                ->rawColumns(['is_active', 'image'])
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

    public function form(Request $request, $unique = NULL)
    {
        $isSuperAdmin = $request->attributes->get('is_super_admin', false);
        $isAdmin = $request->attributes->get('is_admin', false);
        $isCompanyOwner = $request->attributes->get('is_company_owner', false);
        $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
        $isBranchAdmin = $request->attributes->get('is_branch_admin', false);
        $isDepartmentAdmin = $request->attributes->get('is_department_admin', false);

        $companyId = $request->attributes->get('company_id');
        $branchId = $request->attributes->get('branch_id');
        $departmentId = $request->attributes->get('department_id');
        $userId = $request->attributes->get('user_id');
        $loggedInRoleId = $request->attributes->get('role_id', 0);

        if (!is_null($unique)) {
            // Mevcut kayıt
            $item = $this->model::find((int)$unique);

            // Kayıt bulunamadı
            if (is_null($item)) {
                return redirect()->back()->with('error', 'Kayıt bulunamadı');
            }

            // Şirket, şube ve kullanıcı bazlı erişim kontrolü
            if (!$isSuperAdmin && !$isAdmin) {
                // Şirket kontrolü
                if (isset($item->company_id) && (int)$item->company_id !== $companyId) {
                    return redirect()->back()->with('error', 'Bu kaydı görüntüleme yetkiniz bulunmamaktadır.');
                }

                // Şube kontrolü - Şirket sahibi ve yetkilisi tüm şubelere erişebilir
                if (
                    !$isCompanyOwner && !$isCompanyAdmin &&
                    isset($item->branch_id) && $branchId !== null && (int)$item->branch_id !== $branchId
                ) {
                    return redirect()->back()->with('error', 'Bu kaydı görüntüleme yetkiniz bulunmamaktadır.');
                }

                // Departman kontrolü - Şube yetkilisi tüm departmanlara erişebilir
                if (!$isBranchAdmin && isset($item->department_id) && $departmentId !== null && (int)$item->department_id !== $departmentId) {
                    return redirect()->back()->with('error', 'Bu kaydı görüntüleme yetkiniz bulunmamaktadır.');
                }

                // Kullanıcı kontrolü - Sadece personel için
                if (!$isDepartmentAdmin && isset($item->user_id) && $userId !== null && (int)$item->user_id !== $userId) {
                    return redirect()->back()->with('error', 'Bu kaydı görüntüleme yetkiniz bulunmamaktadır.');
                }
            }

            // Kullanıcının rolünü değiştirmeye çalışırken yetki kontrolü
            if (
                !$isSuperAdmin && !$isAdmin &&
                $this->model->getTable() === 'users' &&
                isset($item->role_id) && $item->role_id <= $loggedInRoleId
            ) {
                return redirect()->back()->with('error', 'Bu kullanıcıyı düzenleme yetkiniz bulunmamaktadır.');
            }

            // Form hook varsa uygula
            if (method_exists($this, 'formHook')) {
                $item = $this->formHook($item);
            }
        } else {
            // Yeni kayıt
            $item = new $this->model;

            // Yeni kayıt eklerken şirket ve şube bilgisini otomatik olarak set edelim
            if (!$isSuperAdmin && !$isAdmin) {
                $table = $this->model->getTable();
                $columns = Schema::getColumnListing($table);

                if (in_array('company_id', $columns) && !is_null($companyId)) {
                    $item->company_id = $companyId;
                }

                if (in_array('branch_id', $columns) && !is_null($branchId)) {
                    $item->branch_id = $branchId;
                }

                if (in_array('department_id', $columns) && !is_null($departmentId)) {
                    $item->department_id = $departmentId;
                }
            }
        }

        // Rol listesi hazırlama - Role bazlı yetkilendirme
        $userRoles = [];

        if ($isSuperAdmin) {
            // Süper admin tüm rolleri görebilir
            $userRoles = \App\Models\Role::all();
        } elseif ($isAdmin) {
            // Admin, süper admin (1) hariç tüm rolleri görebilir
            $userRoles = \App\Models\Role::where('id', '>', 1)->get();
        } elseif ($isCompanyOwner) {
            // Şirket sahibi, kendinden düşük rolleri görebilir (3'ten büyük)
            $userRoles = \App\Models\Role::where('id', '>', 3)->get();
        } elseif ($isCompanyAdmin) {
            // Şirket yöneticisi sadece şube yetkilisi ve personel rollerini görebilir
            $userRoles = \App\Models\Role::whereIn('id', [5, 6, 7])->get();
        } elseif ($isBranchAdmin) {
            // Şube yöneticisi sadece departman yetkilisi ve personel rolünü görebilir
            $userRoles = \App\Models\Role::whereIn('id', [6, 7])->get();
        } elseif ($isDepartmentAdmin) {
            // Departman yöneticisi sadece personel rolünü görebilir
            $userRoles = \App\Models\Role::where('id', 7)->get();
        }

        view()->share('userRoles', $userRoles);

        // Şirketler listesi (Süper admin ve Admin için)
        if ($isSuperAdmin || $isAdmin) {
            $companies = \App\Models\Company::all();
            view()->share('companies', $companies);
        }

        // Şubeler listesi
        if ($isSuperAdmin || $isAdmin) {
            // Süper Admin ve Admin tüm şubeleri görebilir
            $branches = \App\Models\Branch::all();
            view()->share('branches', $branches);
        } elseif ($isCompanyOwner || $isCompanyAdmin) {
            // Şirket sahibi ve yöneticisi kendi şirketinin tüm şubelerini görebilir
            $branches = \App\Models\Branch::where('company_id', $companyId)->get();
            view()->share('branches', $branches);
        } elseif ($isBranchAdmin) {
            // Şube yöneticisi sadece kendi şubesini görebilir
            $branches = \App\Models\Branch::where('id', $branchId)->get();
            view()->share('branches', $branches);
        }

        // Departmanlar listesi
        if ($isSuperAdmin || $isAdmin) {
            // Süper Admin ve Admin tüm departmanları görebilir
            $departments = \App\Models\Department::all();
            view()->share('departments', $departments);
        } elseif ($isCompanyOwner || $isCompanyAdmin) {
            // Şirket sahibi ve yöneticisi kendi şirketinin tüm departmanlarını görebilir
            $departments = \App\Models\Department::where('company_id', $companyId)->get();
            view()->share('departments', $departments);
        } elseif ($isBranchAdmin) {
            // Şube yöneticisi kendi şubesinin tüm departmanlarını görebilir
            $departments = \App\Models\Department::where('branch_id', $branchId)->get();
            view()->share('departments', $departments);
        } elseif ($isDepartmentAdmin) {
            // Departman yöneticisi sadece kendi departmanını görebilir
            $departments = \App\Models\Department::where('id', $departmentId)->get();
            view()->share('departments', $departments);
        }

        return view("backend.$this->page.form", compact('item'));
    }

    public function save(Request $request, $unique = NULL)
    {
        $isSuperAdmin = $request->attributes->get('is_super_admin', false);
        $isAdmin = $request->attributes->get('is_admin', false);
        $isCompanyOwner = $request->attributes->get('is_company_owner', false);
        $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
        $isBranchAdmin = $request->attributes->get('is_branch_admin', false);
        $isDepartmentAdmin = $request->attributes->get('is_department_admin', false);

        $companyId = $request->attributes->get('company_id');
        $branchId = $request->attributes->get('branch_id');
        $departmentId = $request->attributes->get('department_id');
        $userId = $request->attributes->get('user_id');
        $loggedInRoleId = $request->attributes->get('role_id', 0);

        // Eğer düzenleme yapılıyorsa ve süper admin/admin değilse, erişim kontrolü yap
        if (!is_null($unique) && !$isSuperAdmin && !$isAdmin) {
            $existingRecord = $this->model::find((int)$unique);

            // Kendi şirketinize ait olmayan kayıtları düzenleyemezsiniz
            if (isset($existingRecord->company_id) && $existingRecord->company_id != $companyId) {
                return redirect()->back()->with('error', 'Bu kaydı düzenleme yetkiniz bulunmamaktadır.');
            }

            // Kendi şubenize ait olmayan kayıtları düzenleyemezsiniz (şirket sahibi/yöneticisi hariç)
            if (
                !$isCompanyOwner && !$isCompanyAdmin &&
                isset($existingRecord->branch_id) && $branchId !== null && $existingRecord->branch_id != $branchId
            ) {
                return redirect()->back()->with('error', 'Bu kaydı düzenleme yetkiniz bulunmamaktadır.');
            }

            // Kendi kayıtlarınız dışındakileri düzenleyemezsiniz (sadece personel için)
            if (!$isBranchAdmin && isset($existingRecord->user_id) && $userId !== null && $existingRecord->user_id != $userId) {
                return redirect()->back()->with('error', 'Bu kaydı düzenleme yetkiniz bulunmamaktadır.');
            }
        }

        // Rol değiştirme kontrolü
        if ($request->has('role_id')) {
            $requestedRoleId = (int)$request->role_id;

            // Süper Admin
            if (!$isSuperAdmin && $requestedRoleId <= 1) {
                return redirect()->back()->with('error', 'Süper Admin rolünü atama yetkiniz bulunmamaktadır.');
            }

            // Admin
            if (!$isSuperAdmin && !$isAdmin && $requestedRoleId <= 2) {
                return redirect()->back()->with('error', 'Admin rolünü atama yetkiniz bulunmamaktadır.');
            }

            // Şirket Sahibi
            if (!$isSuperAdmin && !$isAdmin && $requestedRoleId <= 3) {
                return redirect()->back()->with('error', 'Şirket Sahibi rolünü atama yetkiniz bulunmamaktadır.');
            }

            // Şirket Yetkilisi
            if (!$isSuperAdmin && !$isAdmin && !$isCompanyOwner && $requestedRoleId <= 4) {
                return redirect()->back()->with('error', 'Şirket Yetkilisi rolünü atama yetkiniz bulunmamaktadır.');
            }

            // Şube Yetkilisi
            if (!$isSuperAdmin && !$isAdmin && !$isCompanyOwner && !$isCompanyAdmin && $requestedRoleId <= 5) {
                return redirect()->back()->with('error', 'Şube Yetkilisi rolünü atama yetkiniz bulunmamaktadır.');
            }
        }

        // Form Request Validation - Öncelik sırası: Form Request > Validation Array
        if (isset($this->request) && is_object($this->request)) {
            // Form Request kullanılıyorsa, manuel validation yapmaya gerek yok
            // Laravel otomatik olarak Form Request'i validate edecek

            try {
                // Form Request'ten validation rules'ları al ve manuel validate et
                $formRequestInstance = $this->request;
                $rules = $formRequestInstance->rules();
                $messages = method_exists($formRequestInstance, 'messages') ? $formRequestInstance->messages() : [];

                // Eğer düzenleme yapılıyorsa, image/file alanlarını opsiyonel yap
                if ($unique != null) {
                    $imageFields = ['image', 'logo', 'icon', 'banner'];
                    foreach ($imageFields as $field) {
                        if (isset($rules[$field])) {
                            // required'ı kaldır, sadece format kontrolü yap
                            $rules[$field] = str_replace('required|', '', $rules[$field]);
                            $rules[$field] = str_replace('required', '', $rules[$field]);
                            if (empty($rules[$field])) {
                                $rules[$field] = 'image|max:2048|mimes:jpeg,png,jpg';
                            } else {
                                $rules[$field] = 'image|max:2048|mimes:jpeg,png,jpg|' . $rules[$field];
                            }
                        }
                    }

                    $videoFields = ['video'];
                    foreach ($videoFields as $field) {
                        if (isset($rules[$field])) {
                            $rules[$field] = str_replace('required|', '', $rules[$field]);
                            $rules[$field] = str_replace('required', '', $rules[$field]);
                            if (empty($rules[$field])) {
                                $rules[$field] = 'max:51200|mimes:mp4,ogx,oga,ogv,ogg,webm,mov';
                            } else {
                                $rules[$field] = 'max:51200|mimes:mp4,ogx,oga,ogv,ogg,webm,mov|' . $rules[$field];
                            }
                        }
                    }
                }

                $validator = Validator::make($request->all(), $rules, $messages);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            } catch (\Exception $e) {
                // Form Request oluşturulamadıysa, eski validation sistemini kullan
                Log::error('Form Request validation failed: ' . $e->getMessage());
            }
        } elseif (isset($this->validation) && is_array($this->validation) && count($this->validation) > 0) {
            // Eski validation array sistemi
            if ($unique != null) {
                $validationType = array(
                    'image',
                    'logo',
                    'icon',
                    'banner'
                );
                foreach ($validationType as $key => $value) {
                    if (isset($this->validation[0][$value]))
                        $this->validation[0][$value] = 'image|max:2048|mimes:jpeg,png,jpg';
                }

                $validationDate = array(
                    'published_at',
                    'start_date'
                );
                foreach ($validationDate as $key => $value) {
                    if (isset($this->validation[0][$value]))
                        $this->validation[0][$value] = 'required|date';
                }

                $validationvideo = array(
                    'video'
                );
                foreach ($validationvideo as $key => $value) {
                    if (isset($this->validation[0][$value]))
                        $this->validation[0][$value] = 'max:51200|mimes:mp4,ogx,oga,ogv,ogg,webm,mov';
                }
            }

            $validator = Validator::make($request->all(), $this->validation[0] ?? [], $this->validation[1] ?? []);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
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

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            if (number_format($file->getSize() / 1048576, 1) > 11)
                return redirect()->back()->with('error', 'Dosya formatı 10MB büyük olamaz.')->withInput();

            if (strtolower($file->getClientOriginalExtension()) == "php" || strtolower($file->getClientOriginalExtension()) == "js" || strtolower($file->getClientOriginalExtension()) == "py")
                return redirect()->back()->with('error', 'Dosya yüklenemedi.')->withInput();

            $logo = md5(rand(1, 999999) . date('ymdhis')) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move("upload/$this->upload", $logo);

            $params['logo'] = $logo;
        }

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            if (number_format($file->getSize() / 1048576, 1) > 11)
                return redirect()->back()->with('error', 'Dosya formatı 10MB büyük olamaz.')->withInput();

            if (strtolower($file->getClientOriginalExtension()) == "php" || strtolower($file->getClientOriginalExtension()) == "js" || strtolower($file->getClientOriginalExtension()) == "py")
                return redirect()->back()->with('error', 'Dosya yüklenemedi.')->withInput();

            $icon = md5(rand(1, 999999) . date('ymdhis')) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move("upload/$this->upload", $icon);

            $params['icon'] = $icon;
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            if (number_format($file->getSize() / 1048576, 1) > 11)
                return redirect()->back()->with('error', 'Dosya formatı 10MB büyük olamaz.')->withInput();

            if (strtolower($file->getClientOriginalExtension()) == "php" || strtolower($file->getClientOriginalExtension()) == "js" || strtolower($file->getClientOriginalExtension()) == "py")
                return redirect()->back()->with('error', 'Dosya yüklenemedi.')->withInput();

            $banner = md5(rand(1, 999999) . date('ymdhis')) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move("upload/$this->upload", $banner);
            $params['banner'] = $banner;
        }

        if ($request->hasFile('video')) {

            $file = $request->file('video');
            if (strtolower($file->getClientOriginalExtension()) == "php" || strtolower($file->getClientOriginalExtension()) == "js" || strtolower($file->getClientOriginalExtension()) == "py")
                return redirect()->back()->with('error', 'Dosya yüklenemedi.')->withInput();

            $video = md5(rand(1, 999999) . date('ymdhis')) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move("upload/$this->upload", $video);

            $params['video'] = $video;
        }

        // Veritabanı işlemleri - Try-catch ile SQL hatalarını yakala
        try {
            if (is_null($unique)) {
                $obj = $this->model::create($params);
                // Bildirim gönderimi alt sınıflarda implement edilmeli
                if (method_exists($this, 'notificationHook')) {
                    $this->notificationHook($obj, $params);
                }
            } else {
                $obj = $this->model::find((int)$unique);
                if (!$obj) {
                    return redirect()->back()->with('error', 'Kayıt bulunamadı.');
                }
                $obj->update($params);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // SQL hatalarını yakala ve kullanıcı dostu mesaj göster
            Log::error('Database error in BaseController save: ' . $e->getMessage());

            // Unique constraint hatası
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return redirect()->back()->with('error', 'Bu kayıt zaten mevcut. Lütfen farklı değerler kullanın.')->withInput();
            }

            // Foreign key constraint hatası
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return redirect()->back()->with('error', 'Bu kayıt başka kayıtlar tarafından kullanıldığı için işlem yapılamadı.')->withInput();
            }

            // Genel SQL hatası
            return redirect()->back()->with('error', 'Veritabanı hatası oluştu. Lütfen tekrar deneyin.')->withInput();
        } catch (\Exception $e) {
            // Diğer hatalar
            Log::error('General error in BaseController save: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Bir hata oluştu. Lütfen tekrar deneyin.')->withInput();
        }

        Cache::flush();
        if (method_exists($this, 'saveBack')) {
            return $this->saveBack($obj);
        }

        return redirect()->route("backend." . $this->page . "_list")->with('success', 'Kayıt başarılı şekilde işlendi');
    }

    public function delete(Request $request)
    {
        $exits = $this->model::find((int)$request->post('id'));
        if (!is_null($exits)) {
            $isSuperAdmin = $request->attributes->get('is_super_admin', false);
            $isAdmin = $request->attributes->get('is_admin', false);
            $isCompanyOwner = $request->attributes->get('is_company_owner', false);
            $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
            $isBranchAdmin = $request->attributes->get('is_branch_admin', false);
            $isDepartmentAdmin = $request->attributes->get('is_department_admin', false);

            $companyId = $request->attributes->get('company_id');
            $branchId = $request->attributes->get('branch_id');
            $departmentId = $request->attributes->get('department_id');
            $userId = $request->attributes->get('user_id');
            $loggedInRoleId = $request->attributes->get('role_id', 0);

            // Şirket, şube ve kullanıcı bazlı erişim kontrolü
            if (!$isSuperAdmin && !$isAdmin) {
                // Şirket kontrolü
                if (isset($exits->company_id) && $exits->company_id != $companyId) {
                    return response()->json(['status' => false, 'message' => 'Bu kaydı silme yetkiniz bulunmamaktadır.']);
                }

                // Şube kontrolü - Şirket sahibi ve yöneticisi tüm şubelere erişebilir
                if (
                    !$isCompanyOwner && !$isCompanyAdmin &&
                    isset($exits->branch_id) && $branchId !== null && $exits->branch_id != $branchId
                ) {
                    return response()->json(['status' => false, 'message' => 'Bu kaydı silme yetkiniz bulunmamaktadır.']);
                }

                // Departman kontrolü - Şube yetkilisi tüm departmanlara erişebilir
                if (!$isBranchAdmin && isset($exits->department_id) && $departmentId !== null && $exits->department_id != $departmentId) {
                    return response()->json(['status' => false, 'message' => 'Bu kaydı silme yetkiniz bulunmamaktadır.']);
                }

                // Kullanıcı kontrolü - Sadece personel için
                if (!$isDepartmentAdmin && isset($exits->user_id) && $userId !== null && $exits->user_id != $userId) {
                    return response()->json(['status' => false, 'message' => 'Bu kaydı silme yetkiniz bulunmamaktadır.']);
                }
            }

            // Rol kontrolü - Kullanıcının rolü kendinden üst seviyedeyse (sayısal olarak küçükse) silemez
            if ($this->model->getTable() === 'users' && isset($exits->role_id)) {
                if (!$isSuperAdmin && $exits->role_id <= 1) {
                    return response()->json(['status' => false, 'message' => 'Süper Admin kullanıcısını silme yetkiniz bulunmamaktadır.']);
                }

                if (!$isSuperAdmin && !$isAdmin && $exits->role_id <= 2) {
                    return response()->json(['status' => false, 'message' => 'Admin kullanıcısını silme yetkiniz bulunmamaktadır.']);
                }

                if (!$isSuperAdmin && !$isAdmin && !$isCompanyOwner && $exits->role_id <= 3) {
                    return response()->json(['status' => false, 'message' => 'Şirket Sahibi kullanıcısını silme yetkiniz bulunmamaktadır.']);
                }

                if (!$isSuperAdmin && !$isAdmin && !$isCompanyOwner && !$isCompanyAdmin && $exits->role_id <= 4) {
                    return response()->json(['status' => false, 'message' => 'Şirket Yetkilisi kullanıcısını silme yetkiniz bulunmamaktadır.']);
                }

                if (!$isSuperAdmin && !$isAdmin && !$isCompanyOwner && !$isCompanyAdmin && !$isBranchAdmin && $exits->role_id <= 5) {
                    return response()->json(['status' => false, 'message' => 'Şube Yetkilisi kullanıcısını silme yetkiniz bulunmamaktadır.']);
                }
            }

            $obj = $this->model::find($exits->id);
            $obj->delete();

            if (method_exists($this, 'deleteBack')) {
                return $this->deleteBack($obj);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Kayıt bulunamadı']);
        }

        Cache::flush();
        return response()->json(['status' => true]);
    }
}
