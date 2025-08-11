<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class BaseController extends Controller
{
    /**
     * Controller ile ilişkili model.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * İlişkili modelin adı.
     *
     * @var string
     */
    protected string $modelName = '';

    /**
     * Listeleme sorgusunda kullanılacak ilişkiler.
     *
     * @var array
     */
    protected array $relationships = [];

    /**
     * Arama yapılabilecek alanlar.
     *
     * @var array
     */
    protected array $searchableFields = [];

    /**
     * Sıralama yapılabilecek alanlar.
     *
     * @var array
     */
    protected array $sortableFields = ['id', 'created_at', 'updated_at'];

    /**
     * Öğeleri listeler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->buildIndexQuery($request);

            // Sayfalama
            $perPage = $request->input('per_page', 15);
            $items = $query->paginate($perPage);

            return ApiResponse::success(
                [$items],
                sprintf('%s listesi başarıyla alındı', $this->modelName)
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Kayıtlar alınırken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Index sorgusu oluşturur.
     *
     * @param Request $request
     * @return Builder
     */
    protected function buildIndexQuery(Request $request): Builder
    {
        $query = $this->model->query();

        // İlişkileri yükle
        if (!empty($this->relationships)) {
            $query->with($this->relationships);
        }

        // Rol ve erişim kontrolü
        $isSuperAdmin = $request->attributes->get('is_super_admin', false);
        $isAdmin = $request->attributes->get('is_admin', false);
        $isCompanyOwner = $request->attributes->get('is_company_owner', false);
        $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
        $isBranchAdmin = $request->attributes->get('is_branch_admin', false);

        $companyId = $request->attributes->get('company_id');
        $branchId = $request->attributes->get('branch_id');
        $userId = $request->attributes->get('user_id');

        $table = $this->model->getTable();
        $columns = Schema::getColumnListing($table);

        // Süper Admin ve Admin tüm verilere erişebilir
        if (!$isSuperAdmin && !$isAdmin) {
            // Şirket bazlı filtreleme
            if (in_array('company_id', $columns) && !is_null($companyId)) {
                $query->where("$table.company_id", $companyId);
            }

            // Şube bazlı filtreleme
            if (in_array('branch_id', $columns) && !is_null($branchId) && !$isCompanyOwner && !$isCompanyAdmin) {
                $query->where("$table.branch_id", $branchId);
            }

            // Kullanıcı bazlı filtreleme - sadece normal personel
            if (in_array('user_id', $columns) && !is_null($userId) && !$isBranchAdmin) {
                $query->where("$table.user_id", $userId);
            }
        }

        // Arama filtresi
        if ($request->has('search') && !empty($this->searchableFields)) {
            $searchTerm = $request->input('search');
            $query->where(function(Builder $q) use ($searchTerm) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
                }
            });
        }

        // Özel filtreler
        if (method_exists($this, 'applyFilters')) {
            $this->applyFilters($query, $request);
        }

        // Sıralama
        $sortField = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        if (in_array($sortField, $this->sortableFields)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query;
    }

    /**
     * Belirli bir kaydı gösterir.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $query = $this->model->query();
            $id = $request->id;
            if (!empty($this->relationships)) {
                $query->with($this->relationships);
            }

            $item = $query->find($id);

            if (!$item) {
                return ApiResponse::notFound(
                    sprintf('%s bulunamadı', $this->modelName)
                );
            }

            // Yetki kontrolü
            if (!$this->authorizeItemAccess($item, $request)) {
                return ApiResponse::forbidden('Bu kaydı görüntüleme yetkiniz bulunmamaktadır.');
            }

            return ApiResponse::success(
                [$item],
                sprintf('%s başarıyla alındı', $this->modelName)
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Kayıt alınırken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Yeni bir kayıt oluşturur.
     *
     * @param \Illuminate\Foundation\Http\FormRequest|Request $request
     * @return JsonResponse
     */
    public function store(\Illuminate\Foundation\Http\FormRequest|Request $request): JsonResponse
    {
        try {
            // Veri hazırlama
            $data = $this->prepareStoreData($request);

            // Kayıt oluşturma
            $item = $this->model->create($data);

            // İlişkileri yükle
            if (!empty($this->relationships)) {
                $item->load($this->relationships);
            }

            // saveHook metodu varsa çağır
            if (method_exists($this, 'saveHook')) {
                $item = $this->saveHook($item);
            }

            return ApiResponse::success(
                [$item],
                sprintf('%s başarıyla oluşturuldu', $this->modelName),
                SymfonyResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Kayıt oluşturulurken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Store için veriyi hazırlar.
     *
     * @param \Illuminate\Foundation\Http\FormRequest|Request $request
     * @return array
     */
    protected function prepareStoreData(\Illuminate\Foundation\Http\FormRequest|Request $request): array
    {
        $data = $request->all();

        $user = Auth::user();
        if ($user) {
            // Rol ve yetki bilgilerini alalım
            $isSuperAdmin = $request->attributes->get('is_super_admin', false);
            $isAdmin = $request->attributes->get('is_admin', false);

            // Eğer süper admin veya admin değilse, şirket ve şube bilgisi otomatik atanmalı
            if (!$isSuperAdmin && !$isAdmin) {
                $companyId = $request->attributes->get('company_id');
                if ($companyId && !isset($data['company_id'])) {
                    $data['company_id'] = $companyId;
                }

                $branchId = $request->attributes->get('branch_id');
                if ($branchId && !isset($data['branch_id'])) {
                    $data['branch_id'] = $branchId;
                }
            }

            if (!isset($data['created_by'])) {
                $data['created_by'] = $user->id;
            }

            if (!isset($data['user_id']) && method_exists($this->model, 'user')) {
                $data['user_id'] = $user->id;
            }
        }

        return $data;
    }

    /**
     * Mevcut bir kaydı günceller.
     *
     * @param \Illuminate\Foundation\Http\FormRequest|Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(\Illuminate\Foundation\Http\FormRequest|Request $request, int $id): JsonResponse
    {
        try {
            $item = $this->model->find($id);

            if (!$item) {
                return ApiResponse::notFound(
                    sprintf('%s bulunamadı', $this->modelName)
                );
            }

            // Yetki kontrolü
            if (!$this->authorizeItemAccess($item, $request)) {
                return ApiResponse::forbidden('Bu kaydı düzenleme yetkiniz bulunmamaktadır.');
            }

            // Veri hazırlama
            $data = $this->prepareUpdateData($request, $item);

            // Güncelleme
            $item->update($data);

            // İlişkileri yükle
            if (!empty($this->relationships)) {
                $item->load($this->relationships);
            }

            return ApiResponse::success(
                [$item],
                sprintf('%s başarıyla güncellendi', $this->modelName)
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Kayıt güncellenirken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update için veriyi hazırlar.
     *
     * @param \Illuminate\Foundation\Http\FormRequest|Request $request
     * @param Model $item
     * @return array
     */
    protected function prepareUpdateData(\Illuminate\Foundation\Http\FormRequest|Request $request, Model $item): array
    {
        return $request->all();
    }

    /**
     * Bir kaydı siler.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        try {
            $item = $this->model->find($id);

            if (!$item) {
                return ApiResponse::notFound(
                    sprintf('%s bulunamadı', $this->modelName)
                );
            }

            // Yetki kontrolü
            if (!$this->authorizeItemAccess($item, $request)) {
                return ApiResponse::forbidden('Bu kaydı silme yetkiniz bulunmamaktadır.');
            }

            $item->delete();

            return ApiResponse::success(
                [],
                sprintf('%s başarıyla silindi', $this->modelName)
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Kayıt silinirken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Kayıt işlemi sonrası ilave işlemler yapabilmek için hook.
     *
     * @param Model $item
     * @return Model
     */
    protected function saveHook(Model $item): Model
    {
        // Alt sınıflar bu metodu override edebilir
        return $item;
    }

    /**
     * Bir öğeye erişim yetkisini kontrol eder
     *
     * @param Model $item
     * @param Request $request
     * @return bool
     */
    protected function authorizeItemAccess(Model $item, Request $request): bool
    {
        // Rol ve erişim kontrolü
        $isSuperAdmin = $request->attributes->get('is_super_admin', false);
        $isAdmin = $request->attributes->get('is_admin', false);
        $isCompanyOwner = $request->attributes->get('is_company_owner', false);
        $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
        $isBranchAdmin = $request->attributes->get('is_branch_admin', false);

        $companyId = $request->attributes->get('company_id');
        $branchId = $request->attributes->get('branch_id');
        $userId = $request->attributes->get('user_id');

        // Süper Admin ve Admin her şeye erişebilir
        if ($isSuperAdmin || $isAdmin) {
            return true;
        }

        // Şirket kontrolü
        if (isset($item->company_id) && !is_null($companyId) && $item->company_id != $companyId) {
            return false;
        }

        // Şube kontrolü - Şirket sahibi ve yöneticisi tüm şubelere erişebilir
        if (!$isCompanyOwner && !$isCompanyAdmin &&
            isset($item->branch_id) && !is_null($branchId) && $item->branch_id != $branchId) {
            return false;
        }

        // Kullanıcı kontrolü - Sadece personel için
        if (!$isBranchAdmin && isset($item->user_id) && !is_null($userId) && $item->user_id != $userId) {
            return false;
        }

        return true;
    }
}
