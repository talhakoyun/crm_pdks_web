<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\PasswordChangeRequest;
use App\Http\Requests\Backend\UserRequest;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Role;
use App\Models\ShiftDefinition;
use App\Models\User;
use App\Models\UserBranches;
use App\Models\UserDebitDevice;
use App\Models\Zone;
use App\Models\UserZones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Kullanıcı';
        $this->page = 'user';
        $this->model = new User();
        $this->relation = ['role', 'userBranches', 'userZones', 'company', 'branch', 'department'];
        $this->request = new UserRequest();

        $this->view = (object)array(
            'breadcrumb' => array(
                'Ayarlar' => '#',
                'Kullanıcılar' => route('backend.user_list'),
            ),
        );

        // Middleware kullanarak rol bazlı veriler yükleyelim
        $this->middleware(function ($request, $next) {
            $isAdmin = $request->attributes->get('is_admin', false);
            $isSuperAdmin = $request->attributes->get('is_super_admin', false);
            $isCompanyOwner = $request->attributes->get('is_company_owner', false);
            $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
            $isBranchAdmin = $request->attributes->get('is_branch_admin', false);
            $isDepartmentAdmin = $request->attributes->get('is_department_admin', false);

            $companyId = $request->attributes->get('company_id');
            $branchId = $request->attributes->get('branch_id');
            $departmentId = $request->attributes->get('department_id');

            // Rol bazlı verileri filtrele
            $rolesQuery = Role::query();

            // Roller için erişim kısıtlaması
            if ($isSuperAdmin) {
                // Süper admin tüm rolleri görebilir
            } elseif ($isAdmin) {
                // Admin, süper admin (1) hariç tüm rolleri görebilir
                $rolesQuery->where('id', '>', 1);
            } elseif ($isCompanyOwner) {
                // Şirket sahibi, kendinden düşük rolleri görebilir (3'ten büyük)
                $rolesQuery->where('id', '>', 3);
            } elseif ($isCompanyAdmin) {
                // Şirket yöneticisi sadece şube yetkilisi ve personel rollerini görebilir
                $rolesQuery->whereIn('id', [5, 6, 7]);
            } elseif ($isBranchAdmin) {
                // Şube yöneticisi sadece departman yetkilisi ve personel rolünü görebilir
                $rolesQuery->whereIn('id', [6, 7]);
            } elseif ($isDepartmentAdmin) {
                // Departman yöneticisi sadece personel rolünü görebilir
                $rolesQuery->where('id', 7);
            }

            view()->share('roles', $rolesQuery->get());

            // Şubeler için erişim kısıtlaması
            $branchesQuery = Branch::query();
            if (!$isSuperAdmin && !$isAdmin) {
                $branchesQuery->where('company_id', $companyId);

                if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                    $branchesQuery->where('id', $branchId);
                }
            }
            view()->share('branches', $branchesQuery->get());

            // Departmanlar için erişim kısıtlaması
            $departmentsQuery = Department::active();
            if (!$isSuperAdmin && !$isAdmin) {
                $departmentsQuery->where('company_id', $companyId);

                if (!$isCompanyOwner && !$isCompanyAdmin) {
                    if ($branchId) {
                        $departmentsQuery->where('branch_id', $branchId);

                        if (!$isBranchAdmin && $departmentId) {
                            $departmentsQuery->where('id', $departmentId);
                        }
                    }
                }
            }
            view()->share('departments', $departmentsQuery->get());

            // Vardiyalar için erişim kısıtlaması (şirket bazlı filtreleme)
            $shiftsQuery = ShiftDefinition::active();
            if (!$isSuperAdmin && !$isAdmin && $companyId) {
                $shiftsQuery->where('company_id', $companyId);
            }
            view()->share('shiftDefinitions', $shiftsQuery->get());

            // Alanlar (Zones) için erişim kısıtlaması
            $zonesQuery = Zone::query();
            if (!$isSuperAdmin && !$isAdmin && $companyId) {
                $zonesQuery->where('company_id', $companyId);

                if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                    $zonesQuery->where('branch_id', $branchId);
                }
            }
            view()->share('zones', $zonesQuery->get());

            return $next($request);
        });

        parent::__construct();
    }

    /**
     * Form görünümüne ek veriler eklemek için kullanılır
     */
    public function formHook($item)
    {
        // Kullanıcıya ait şubeler - many-to-many ilişkisini kullan
        // Bu sayede form'da $item->branches->pluck('id')->toArray() kullanabiliriz

        // Kullanıcıya ait vardiya bilgileri
        $userShift = \App\Models\UserShift::where('user_id', $item->id)->first();
        if ($userShift) {
            $item->shift_definition_id = $userShift->shift_definition_id;
        }

        // Kullanıcıya ait izin bilgileri
        $userPermit = \App\Models\UserPermit::where('user_id', $item->id)->first();
        if ($userPermit) {
            $item->allow_outside = $userPermit->allow_outside;
        }

        return $item;
    }

    /**
     * Datatable'a ek sütunlar eklemek için kullanılır
     */
    public function datatableHook($datatable)
    {
        $datatable->addColumn('company_name', function ($item) {
            return $item->company->name ?? '-';
        });

        $datatable->addColumn('role_name', function ($item) {
            return $item->role->name ?? '-';
        });

        return $datatable;
    }

    /**
     * Kullanıcı kaydı için özel kayıt işlemleri
     */
    public function saveHook(Request $request)
    {
        // Form'dan gelen verileri al, branch_ids hariç
        $params = $request->except('branch_ids');

        $isAdmin = $request->attributes->get('is_admin', false);
        $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
        $loggedInRoleId = $request->attributes->get('role_id', 0);

        // Mevcut kullanıcı güncelleniyor ve is_active değeri 0 (pasif) olarak ayarlanmışsa
        if ($request->route('unique') !== null && isset($params['is_active']) && $params['is_active'] == 0) {
            $userId = $request->route('unique');

            // Kullanıcının üzerinde aktif zimmet var mı kontrol et
            $activeDebits = \App\Models\UserDebitDevice::where('user_id', $userId)
                ->where(function($query) {
                    $query->where('status', 'active');
                })
                ->count();

            if ($activeDebits > 0) {
                // Kullanıcının üzerinde aktif zimmet varsa, pasife çekme işlemini iptal et
                return redirect()->back()->with('error', 'Bu kullanıcının üzerinde ' . $activeDebits . ' adet aktif zimmet bulunmaktadır. Kullanıcıyı pasife çekmeden önce zimmetleri teslim almalısınız.')->withInput();
            }
        }

        // Company_id kontrolü
        if (!$isAdmin) {
            // Süper admin değilse, kendi şirket ID'sini kullan
            $params['company_id'] = $request->attributes->get('company_id');
        }

        // Branch_id kontrolü - auth kullanıcının role_id'sine göre
        $authUserRoleId = Auth::user()->role_id;
        if (in_array($authUserRoleId, [5, 6])) {
            // Role 5 ve 6 için auth kullanıcının şubesini ata
            $params['branch_id'] = $request->attributes->get('branch_id');
        } elseif (in_array($authUserRoleId, [3, 4])) {
            // Role 3 ve 4 için form'dan gelen branch_id'yi kullan
            if ($request->has('branch_id')) {
                $params['branch_id'] = $request->branch_id;
            }
        }

        // Rol kontrolü
        if (isset($params['role_id'])) {
            $requestedRoleId = (int)$params['role_id'];

            // Rol atama kısıtlamaları
            if (!$isAdmin) {
                // Süper admin değilse bazı kısıtlamalar uygulayalım

                if ($requestedRoleId <= $loggedInRoleId) {
                    // Kendi rolünde veya daha yüksek seviyede bir rol atayamaz
                    unset($params['role_id']);
                } else if ($requestedRoleId <= 2 && !$isAdmin) {
                    // Süper admin değilse, süper admin (1) veya şirket yöneticisi (2) rolü atayamaz
                    unset($params['role_id']);
                }
            }
        }

        // Yeni kullanıcı ekleniyorsa veya şifre değiştiriliyorsa
        if ($request->route('unique') === null) {
            // Yeni kullanıcı
            $params['password'] = Hash::make($params['password']);
        } else {
            // Mevcut kullanıcı güncelleniyor
            if (isset($params['password']) && $params['password'] != '') {
                $params['password'] = Hash::make($params['password']);
            } else {
                unset($params['password']);
            }
        }

        // İzin ve vardiya bilgilerini User tablosuna kaydetmemek için filtreleme
        unset($params['shift_definition_id']);
        unset($params['allow_outside']);

        // Departman yetkilisi (role_id = 6) için department_id'yi null yap
        if (isset($params['role_id']) && $params['role_id'] == 6) {
            $params['department_id'] = null;
        }

        return $params;
    }

    public function saveBack($obj)
    {
        $request = request();

        // Role ID kontrolü - personel rolleri (5, 6, 7) için harici tablolara kayıt yap
        $roleId = $obj->role_id ?? $request->role_id;
        $isPersonnelRole = in_array($roleId, [5, 6, 7]);

                if ($isPersonnelRole) {
            // Şubelerin kaydedilmesi (branch_ids - çoklu seçim için)
            if ($request->has('branch_ids')) {
                // Önce kullanıcının mevcut şubelerini temizle
                UserBranches::where('user_id', $obj->id)->delete();

                // Yeni şubeleri ekle
                foreach ($request->branch_ids as $branchId) {
                    if (!empty($branchId)) {
                        UserBranches::create([
                            'user_id' => $obj->id,
                            'branch_id' => $branchId,
                        ]);
                    }
                }
            }



            // Vardiya bilgilerinin kaydedilmesi
            if ($request->has('shift_definition_id')) {
                // Önce mevcut vardiya kaydını sil
                \App\Models\UserShift::where('user_id', $obj->id)->delete();

                // Yeni vardiya kaydını ekle
                \App\Models\UserShift::create([
                    'user_id' => $obj->id,
                    'shift_definition_id' => $request->shift_definition_id,
                    'is_active' => 1,
                ]);
            }

            // Kullanıcı izin bilgilerinin kaydedilmesi
            if ($request->has('allow_outside')) {
                // Önce mevcut izin kaydını sil
                \App\Models\UserPermit::where('user_id', $obj->id)->delete();

                // Yeni izin kaydını ekle
                \App\Models\UserPermit::create([
                    'user_id' => $obj->id,
                    'allow_outside' => $request->allow_outside ?? 0,
                    'allow_offline' => 0, // Artık kullanılmıyor
                    'allow_zone' => 0, // Artık kullanılmıyor
                    'zone_flexible' => 0, // Artık kullanılmıyor
                    'is_active' => 1,
                ]);
            }
        } else {
            // Personel rolü değilse, mevcut harici tablo kayıtlarını temizle
            UserBranches::where('user_id', $obj->id)->delete();
            UserZones::where('user_id', $obj->id)->delete();
            \App\Models\UserShift::where('user_id', $obj->id)->delete();
            \App\Models\UserPermit::where('user_id', $obj->id)->delete();
        }

        return redirect()->route("backend." . $this->page . "_list")->with('success', 'Kullanıcı başarılı şekilde kaydedildi');
    }

    public function profile(Request $request)
    {
        $item = User::find(Auth::user()->id);
        return view('backend.user.profile', compact('item'));
    }

    public function profile_save(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $params = $request->all();
        $request->validate(
            [
                'name' => 'required|min:2|max:80',
                'surname' => 'required|min:2|max:80',
                'email' => 'required|email|min:10|max:191|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
                'phone' => 'required',
            ],
            [
                'name.required' => 'Ad boş geçilemez',
                'name.min' => 'Ad minimum 2 karakter olmalıdır.',
                'name.max' => 'Ad maksimum 80 karakter olmalıdır.',
                'surname.required' => 'Soyad boş geçilemez',
                'surname.min' => 'Soyad minimum 2 karakter olmalıdır.',
                'surname.max' => 'Soyad maksimum 80 karakter olmalıdır.',
                'email.required' => 'E-mail boş geçilemez',
                'email.min' => 'E-mail minimum 10 karakter olmalıdır.',
                'email.max' => 'E-mail maksimum 191 karakter olmalıdır.',
                'email.email' => 'E-mail adresini kontrol ediniz.',
                'phone.required' => 'Telefon boş geçilemez'
            ]
        );
        $user->update($params);

        return redirect()->route("backend.profile")->with('success', 'Profil bilgileriniz kaydedildi');
    }

    public function delete(Request $request)
    {
        $restrictedIds = [1, 2];

        $userId = (int) $request->post('id');
        if (in_array($userId, $restrictedIds)) {
            return response()->json(['status' => false, 'message' => 'Bu kayıtlar silinemez.']);
        }

        $user = $this->model::find($userId);

        if (!is_null($user)) {
            // Kullanıcının üzerinde aktif zimmet var mı kontrol et
            $activeDebits = UserDebitDevice::where('user_id', $userId)
                ->where(function($query) {
                    $query->where('status', 'active');
                })
                ->count();

            if ($activeDebits > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bu kullanıcının üzerinde ' . $activeDebits . ' adet aktif zimmet bulunmaktadır. Kullanıcıyı silmeden önce zimmetleri teslim almalısınız.'
                ]);
            }

            $user->delete();
        } else {
            return response()->json(['status' => false, 'message' => 'Kayıt bulunamadı']);
        }

        return response()->json(['status' => true]);
    }

    public function password(PasswordChangeRequest $request)
    {
        $user = User::find(Auth::user()->id);

        if (is_null($user)) {
            return redirect()->back()->with('warning', 'Kayıt bulunamadı');
        }

        if (isset($request->password) && !empty($request->password)) {
            if (Hash::check($request->password, $user->password)) {
                return redirect()->back()->with('warning', 'Yeni şifreniz mevcut şifreniz ile aynı olamaz')->withInput();
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            Auth::logout(); // Kullanıcıyı çıkış yaptır

            // Oturum temizliği gerekebilir
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('signin')->with('success', 'Şifre güncellendi. Lütfen tekrar giriş yapın.');
        }

        return redirect()->route("backend.profile", ['tab' => 'password'])->with('success', 'Şifre başarılı şekilde güncellendi');
    }

    /**
     * Kullanıcıları listelerken ek filtreler uygulayalım
     */
    public function list(Request $request)
    {
        // Kullanıcılar için özel listQuery tanımlayalım
        $isAdmin = $request->attributes->get('is_admin', false);
        $isSuperAdmin = $request->attributes->get('is_super_admin', false);
        $isCompanyOwner = $request->attributes->get('is_company_owner', false);
        $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
        $isBranchAdmin = $request->attributes->get('is_branch_admin', false);
        $isDepartmentAdmin = $request->attributes->get('is_department_admin', false);

        $companyId = $request->attributes->get('company_id');
        $branchId = $request->attributes->get('branch_id');
        $departmentId = $request->attributes->get('department_id');
        $loggedInRoleId = $request->attributes->get('role_id', 0);

        $this->listQuery = $this->model::query();

        // Süper Admin ve Admin için kısıtlama yok
        if (!$isSuperAdmin && !$isAdmin) {
            // Şirket bazlı kısıtlama
            $this->listQuery->where('company_id', $companyId);

            // Rol bazlı kısıtlama - kullanıcı kendinden düşük rolleri görebilir
            $this->listQuery->where('role_id', '>=', $loggedInRoleId);

            // Şube bazlı kısıtlama
            if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                $this->listQuery->where('branch_id', $branchId);

                // Departman bazlı kısıtlama
                if (!$isBranchAdmin && $departmentId) {
                    $this->listQuery->where('department_id', $departmentId);
                }
            }
        }

        return parent::list($request);
    }
}
