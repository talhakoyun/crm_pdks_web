<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\UserFileRequest;
use App\Models\FileType;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserFileController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Personel Dosyaları';
        $this->page = 'user_file';
        $this->upload = 'user_files';
        $this->model = new UserFile();
        $this->request = new UserFileRequest();
        $this->relation = ['user', 'fileType'];
        $this->view = (object)array(
            'breadcrumb' => array(
                'Personel Dosyaları' => route('backend.user_file_list'),
            ),
        );

        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $isAdmin = $user->role_id == 2;
            $isSuperAdmin = $user->role_id == 1;
            $isCompanyOwner = $user->role_id == 3;
            $isCompanyAdmin = $user->role_id == 4;
            $isBranchAdmin = $user->role_id == 5;
            $isDepartmentAdmin = $user->role_id == 6;

            $companyId = $user->company_id;
            $branchId = $user->branch_id;
            $departmentId = $user->department_id;

            // Yetki seviyesine göre kullanıcı filtreleme
            $usersQuery = User::query();

            if (!$isSuperAdmin && !$isAdmin) {
                $usersQuery->where('company_id', $companyId);

                if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                    $usersQuery->where('branch_id', $branchId);

                    if (!$isBranchAdmin && $departmentId) {
                        $usersQuery->where('department_id', $departmentId);
                    }
                }
            }

            view()->share('users', $usersQuery->get());
            view()->share('fileTypes', FileType::active()->orderBy('name')->get());

            return $next($request);
        });

        parent::__construct();
    }

    /**
     * Personel dosyaları listesi - rol bazlı filtreleme
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        // Rol bazlı filtreleme
        $user = Auth::user();
        $isAdmin = $user->role_id == 2;
        $isSuperAdmin = $user->role_id == 1;
        $isCompanyOwner = $user->role_id == 3;
        $isCompanyAdmin = $user->role_id == 4;
        $isBranchAdmin = $user->role_id == 5;
        $isDepartmentAdmin = $user->role_id == 6;

        $companyId = $user->company_id;
        $branchId = $user->branch_id;
        $departmentId = $user->department_id;
        $loggedInUserId = Auth::id();

        $this->listQuery = $this->model::query()->with(['user', 'fileType']);

        // Süper Admin ve Admin haricinde şirket filtrelemesi uygula
        if (!$isSuperAdmin && !$isAdmin) {
            $this->listQuery->whereHas('user', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });

            // Şirket sahibi ve yetkilisi tüm şubelere erişebilir
            if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                $this->listQuery->whereHas('user', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });

                // Departman yetkilisi sadece kendi departmanının dosyalarını görebilir
                if (!$isBranchAdmin && $isDepartmentAdmin && $departmentId) {
                    $this->listQuery->whereHas('user', function($query) use ($departmentId) {
                        $query->where('department_id', $departmentId);
                    });
                }
            }

            // Sadece personel ise, sadece kendi dosyalarını görebilir
            if (!$isCompanyOwner && !$isCompanyAdmin && !$isBranchAdmin && !$isDepartmentAdmin) {
                $this->listQuery->where('user_id', $loggedInUserId);
            }
        }

        return parent::list($request);
    }

    /**
     * Datatables için özel sütunlar
     *
     * @param mixed $obj
     * @return mixed
     */
    public function datatableHook($obj)
    {
        return $obj
            ->addColumn('user_name', function ($item) {
                return $item->user->name . ' ' . $item->user->surname;
            })
            ->addColumn('file_type_name', function ($item) {
                return $item->fileType->name ?? '-';
            })
            ->addColumn('file_info', function ($item) {
                return $item->original_filename . ' (' . $item->human_file_size . ')';
            })
            ->addColumn('actions', function ($item) {
                return '<div class="d-flex align-items-center gap-10 justify-content-center">
                    <a href="' . $item->file_url . '" target="_blank" class="bg-info-focus text-info-600 bg-hover-info-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="Görüntüle">
                        <iconify-icon icon="carbon:view" class="menu-icon"></iconify-icon>
                    </a>
                    <a href="' . route('backend.user_file_form', $item->id) . '" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="Düzenle">
                        <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                    </a>
                    <button type="button" class="remove-item-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" row-delete="' . $item->id . '" title="Sil">
                        <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                    </button>
                </div>';
            });
    }

    /**
     * Personel dosyası formu - rol bazlı erişim kontrolü
     *
     * @param Request $request
     * @param int|null $unique
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function form(Request $request, $unique = null)
    {
        // Rol bazlı erişim kontrolü
        $user = Auth::user();
        $isAdmin = $user->role_id == 2;
        $isSuperAdmin = $user->role_id == 1;
        $isCompanyOwner = $user->role_id == 3;
        $isCompanyAdmin = $user->role_id == 4;
        $isBranchAdmin = $user->role_id == 5;
        $isDepartmentAdmin = $user->role_id == 6;
        $loggedInUserId = Auth::id();

        // Dosya düzenleme durumunda yetki kontrolü
        if ($unique) {
            $userFile = UserFile::find($unique);

            if (!$userFile) {
                return redirect()->route('backend.user_file_list')->with('error', 'Dosya bulunamadı.');
            }

            // Süper admin ve admin her dosyayı düzenleyebilir
            if (!$isSuperAdmin && !$isAdmin) {
                // Personel sadece kendi dosyalarını düzenleyebilir
                if (!$isCompanyOwner && !$isCompanyAdmin && !$isBranchAdmin && !$isDepartmentAdmin) {
                    if ($userFile->user_id != $loggedInUserId) {
                        return redirect()->route('backend.user_file_list')->with('error', 'Bu dosyayı düzenleme yetkiniz bulunmamaktadır.');
                    }
                }

                // Departman yöneticisi sadece kendi departmanındaki personel dosyalarını düzenleyebilir
                if ($isDepartmentAdmin) {
                    $departmentId = $user->department_id;
                    if ($userFile->user->department_id != $departmentId) {
                        return redirect()->route('backend.user_file_list')->with('error', 'Bu dosyayı düzenleme yetkiniz bulunmamaktadır.');
                    }
                }

                // Şube yöneticisi sadece kendi şubesindeki personel dosyalarını düzenleyebilir
                if ($isBranchAdmin) {
                    $branchId = $user->branch_id;
                    if ($userFile->user->branch_id != $branchId) {
                        return redirect()->route('backend.user_file_list')->with('error', 'Bu dosyayı düzenleme yetkiniz bulunmamaktadır.');
                    }
                }

                // Şirket yöneticisi ve sahibi sadece kendi şirketindeki personel dosyalarını düzenleyebilir
                if ($isCompanyOwner || $isCompanyAdmin) {
                    $companyId = $user->company_id;
                    if ($userFile->user->company_id != $companyId) {
                        return redirect()->route('backend.user_file_list')->with('error', 'Bu dosyayı düzenleme yetkiniz bulunmamaktadır.');
                    }
                }
            }
        }

        return parent::form($request, $unique);
    }

    /**
     * Personel dosyası kaydet
     *
     * @param Request $request
     * @param int|null $unique
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request, $unique = null)
    {
        // Geçici dosya yükleme ile mi yapılıyor?
        if ($request->has('temp_file') && $request->temp_file) {
            // Geçici dosyadan kalıcı dosyaya taşı
            $tempFileName = $request->temp_file;
            $tempPath = storage_path('app/public/temp/' . $tempFileName);

            if (!file_exists($tempPath)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['file' => 'Geçici dosya bulunamadı. Lütfen dosyayı tekrar yükleyin.']);
            }

            // Eğer düzenleme yapılıyorsa, eski dosyayı sil
            if ($unique) {
                $userFile = UserFile::find($unique);
                if ($userFile) {
                    $userFile->deleteFile();
                }
            }

            // Yeni dosya adı oluştur
            $fileName = Str::random(40) . '.' . $request->file_extension;
            $folderPath = 'user_files/' . $request->user_id;
            $fullFolderPath = storage_path('app/public/' . $folderPath);

            // Kullanıcı klasörünü oluştur
            if (!file_exists($fullFolderPath)) {
                mkdir($fullFolderPath, 0755, true);
            }

            $finalPath = $fullFolderPath . '/' . $fileName;

            // Dosyayı taşı
            if (rename($tempPath, $finalPath)) {
                $filePath = $folderPath . '/' . $fileName;

                // Dosya bilgilerini kaydet
                $request->merge([
                    'filename' => $fileName,
                    'original_filename' => $request->original_filename,
                    'file_path' => $filePath,
                    'file_extension' => $request->file_extension,
                    'file_size' => $request->file_size
                ]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['file' => 'Dosya taşınırken bir hata oluştu.']);
            }

        } else if ($request->hasFile('file')) {
            // Normal dosya yükleme
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            // Dosya tipine göre uzantı kontrolü
            $fileType = FileType::find($request->file_type_id);
            if ($fileType && !$fileType->isExtensionAllowed($extension)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['file' => 'Bu dosya tipi için izin verilmeyen bir dosya formatı seçtiniz. İzin verilen formatlar: ' . $fileType->allowed_extensions]);
            }

            // Eğer düzenleme yapılıyorsa, eski dosyayı sil
            if ($unique) {
                $userFile = UserFile::find($unique);
                if ($userFile) {
                    $userFile->deleteFile();
                }
            }

            // Yeni dosyayı yükle
            $fileName = Str::random(40) . '.' . $extension;
            $folderPath = 'user_files/' . $request->user_id;
            $filePath = $file->storeAs($folderPath, $fileName, 'public');

            // Dosya bilgilerini kaydet
            $request->merge([
                'filename' => $fileName,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_extension' => $extension,
                'file_size' => $file->getSize()
            ]);
        } else {
            // Dosya yükleme zorunlu mu kontrol et
            if (!$unique) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['file' => 'Dosya yüklemeniz gerekmektedir.']);
            }

            // Düzenleme yapılıyor ve dosya yüklenmemişse, eski dosya bilgilerini koru
            $existingFile = UserFile::find($unique);
            if ($existingFile) {
                $request->merge([
                    'filename' => $existingFile->filename,
                    'original_filename' => $existingFile->original_filename,
                    'file_path' => $existingFile->file_path,
                    'file_extension' => $existingFile->file_extension,
                    'file_size' => $existingFile->file_size
                ]);
            }
        }

        return parent::save($request, $unique);
    }

    /**
     * Personel dosyası sil
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        // Rol bazlı erişim kontrolü
        $user = Auth::user();
        $isAdmin = $user->role_id == 2;
        $isSuperAdmin = $user->role_id == 1;
        $isCompanyOwner = $user->role_id == 3;
        $isCompanyAdmin = $user->role_id == 4;
        $isBranchAdmin = $user->role_id == 5;
        $isDepartmentAdmin = $user->role_id == 6;
        $loggedInUserId = Auth::id();

        // Silme işlemi için yetki kontrolü
        if (!$isSuperAdmin && !$isAdmin) {
            $ids = explode(',', $request->input('id'));
            $userFiles = UserFile::whereIn('id', $ids)->get();

            foreach ($userFiles as $userFile) {
                // Personel sadece kendi dosyalarını silebilir
                if (!$isCompanyOwner && !$isCompanyAdmin && !$isBranchAdmin && !$isDepartmentAdmin) {
                    if ($userFile->user_id != $loggedInUserId) {
                        return response()->json(['status' => false, 'message' => 'Bu dosyayı silme yetkiniz bulunmamaktadır.']);
                    }
                }

                // Departman yöneticisi sadece kendi departmanındaki personel dosyalarını silebilir
                if ($isDepartmentAdmin) {
                    $departmentId = $user->department_id;
                    if ($userFile->user->department_id != $departmentId) {
                        return response()->json(['status' => false, 'message' => 'Bu dosyayı silme yetkiniz bulunmamaktadır.']);
                    }
                }

                // Şube yöneticisi sadece kendi şubesindeki personel dosyalarını silebilir
                if ($isBranchAdmin) {
                    $branchId = $user->branch_id;
                    if ($userFile->user->branch_id != $branchId) {
                        return response()->json(['status' => false, 'message' => 'Bu dosyayı silme yetkiniz bulunmamaktadır.']);
                    }
                }

                // Şirket yöneticisi ve sahibi sadece kendi şirketindeki personel dosyalarını silebilir
                if ($isCompanyOwner || $isCompanyAdmin) {
                    $companyId = $user->company_id;
                    if ($userFile->user->company_id != $companyId) {
                        return response()->json(['status' => false, 'message' => 'Bu dosyayı silme yetkiniz bulunmamaktadır.']);
                    }
                }
            }
        }

        // Önce dosyayı fiziksel olarak silmek için ilgili kayıtları bulalım
        $ids = explode(',', $request->input('id'));
        $userFiles = UserFile::whereIn('id', $ids)->get();

        // Her dosya için fiziksel silme işlemini gerçekleştir
        foreach ($userFiles as $userFile) {
            $userFile->deleteFile();
        }

        // Veritabanı kayıtlarını sil
        return parent::delete($request);
    }

    /**
     * Temp dosya yükleme (AJAX için)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
        public function uploadTemp(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dosya bulunamadı'
                ]);
            }

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            // Dosya tipi kontrolü
            if ($request->has('file_type_id') && $request->file_type_id) {
                $fileType = FileType::find($request->file_type_id);
                if ($fileType && !$fileType->isExtensionAllowed($extension)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu dosya tipi için izin verilmeyen bir dosya formatı seçtiniz. İzin verilen formatlar: ' . $fileType->allowed_extensions
                    ]);
                }
            }

            // Dosya boyutu kontrolü (10MB limit)
            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($file->getSize() > $maxSize) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dosya boyutu çok büyük. Maksimum 10MB yükleyebilirsiniz.'
                ]);
            }

                        // Geçici dosyayı manuel olarak yükle
            $fileName = 'temp_' . Str::random(20) . '.' . $extension;
            $tempDir = storage_path('app/public/temp');

            // Temp klasörünün varlığını kontrol et ve gerekirse oluştur
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

                        // Dosyayı PHP'nin native move_uploaded_file fonksiyonu ile taşı
            if (!move_uploaded_file($file->getPathname(), $fullPath)) {
                // Alternatif yöntem: file_put_contents kullan
                $fileContent = file_get_contents($file->getPathname());
                if ($fileContent === false || file_put_contents($fullPath, $fileContent) === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dosya yüklenirken bir hata oluştu. Kaynak: ' . $file->getPathname() . ', Hedef: ' . $fullPath
                    ]);
                }
            }

            // Dosyanın başarıyla taşındığını kontrol et
            if (!file_exists($fullPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dosya taşındı ama bulunamıyor: ' . $fullPath
                ]);
            }

            $filePath = 'temp/' . $fileName;

            return response()->json([
                'success' => true,
                'temp_file' => $fileName,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_extension' => $extension,
                'file_size' => $file->getSize(),
                'file_url' => url('storage/' . $filePath)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya yüklenirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Temp dosya sil (AJAX için)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTempFile(Request $request)
    {
        $tempFile = $request->input('temp_file');
        $filePath = 'temp/' . $tempFile;

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
            return response()->json([
                'success' => true,
                'message' => 'Dosya başarıyla silindi'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Dosya bulunamadı'
        ]);
    }
}
