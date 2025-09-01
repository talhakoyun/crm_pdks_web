<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\UserDebitDeviceRequest;
use App\Models\DebitDevice;
use App\Models\User;
use App\Models\UserDebitDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class UserDebitDeviceController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Zimmet Atamaları';
        $this->page = 'user_debit_device';
        $this->model = new UserDebitDevice();
        $this->relation = ['user', 'debitDevice'];
        $this->request = new UserDebitDeviceRequest();

        $this->view = (object)array(
            'breadcrumb' => array(
                'Zimmet Atamaları' => route('backend.user_debit_device_list'),
            ),
        );

        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Kullanıcılar ve zimmet cihazları listesini view'a gönder
            view()->share('users', User::where('company_id', $companyId)->get());

            // Sadece atanmamış cihazları getir
            $assignedDeviceIds = UserDebitDevice::where('status', 'active')->pluck('debit_device_id')->toArray();

            // Eğer düzenleme yapılıyorsa, mevcut cihazı da listeye dahil et
            $currentDeviceId = $request->route('unique') ? UserDebitDevice::find($request->route('unique'))?->debit_device_id : null;

            $devices = DebitDevice::where('company_id', $companyId)
                ->where(function($query) use ($assignedDeviceIds, $currentDeviceId) {
                    $query->whereNotIn('id', $assignedDeviceIds)
                        ->orWhere('id', $currentDeviceId);
                })
                ->get();

            view()->share('devices', $devices);

            return $next($request);
        });

        parent::__construct();
    }

    /**
     * Zimmet atama listesi
     */
    public function list(Request $request)
    {
        if ($request->has('datatable')) {
            $select = $this->model::with($this->relation);
            $user = Auth::user();
            // Role ve şirket bazlı veri erişimi kontrolü
            $isAdmin = $user->role_id == 2;
            $companyId = $user->company_id;

            // Admin olmayan kullanıcılar için şirket filtrelemesi
            if (!$isAdmin) {
                $select->whereHas('user', function($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                });
            }

            $obj = datatables()->of($select)
                ->addIndexColumn()
                ->editColumn('user_id', function ($item) {
                    return $item->user?->name . ' ' . $item->user?->surname .
                           ($item->user?->department ? ' (' . $item->user?->department?->title . ')' : '');
                })
                ->editColumn('debit_device_id', function ($item) {
                    return $item->debitDevice?->name . ' - ' . $item->debitDevice?->brand . ' ' . $item->debitDevice?->model;
                })
                ->editColumn('start_date', function ($item) {
                    // Tarih null ise boş dön
                    if (is_null($item->start_date)) {
                        return '';
                    }
                    // Tarih string ise Carbon nesnesine dönüştür
                    if (is_string($item->start_date)) {
                        return \Carbon\Carbon::parse($item->start_date)->format('d.m.Y');
                    }
                    // Tarih zaten Carbon nesnesi ise formatı dönüştür
                    return $item->start_date->format('d.m.Y');
                })
                ->editColumn('end_date', function ($item) {
                    // Tarih null ise boş dön
                    if (is_null($item->end_date)) {
                        return '';
                    }
                    // Tarih string ise Carbon nesnesine dönüştür
                    if (is_string($item->end_date)) {
                        return \Carbon\Carbon::parse($item->end_date)->format('d.m.Y');
                    }
                    // Tarih zaten Carbon nesnesi ise formatı dönüştür
                    return $item->end_date->format('d.m.Y');
                })
                ->editColumn('status', function ($item) {
                    // Durum değerini kontrol et - "returned_" ile başlıyorsa "returned" olarak kabul et
                    $status = $item->status;
                    if (strpos($status, 'returned_') === 0) {
                        $status = 'returned';
                    }

                    $statusClass = [
                        'active' => 'success',
                        'expired' => 'danger',
                        'returned' => 'warning'
                    ][$status] ?? 'secondary';

                    $statusText = [
                        'active' => 'Personelde',
                        'expired' => 'Süresi Dolmuş',
                        'returned' => 'Teslim Alınmış'
                    ][$status] ?? $status;

                    $statusIcon = [
                        'active' => '<i class="fas fa-user me-1"></i>',
                        'expired' => '<i class="fas fa-calendar-times me-1"></i>',
                        'returned' => '<i class="fas fa-check-circle me-1"></i>'
                    ][$status] ?? '';

                    return '<span class="badge bg-'.$statusClass.' fs-6 px-3 py-2">'.$statusIcon.$statusText.'</span>';
                })
                ->addColumn('actions', function ($item) {
                    $editBtn = '<a href="'.route('backend.'.$this->page.'_form', ['unique' => $item->id]).'" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                        <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                    </a>';

                    $deleteBtn = '<button type="button" class="remove-item-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" row-delete="'.$item->id.'">
                        <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                    </button>';

                    // Excel export butonu
                    $excelBtn = '<button type="button" class="export-excel-btn bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" data-id="'.$item->id.'">
                        <iconify-icon icon="vscode-icons:file-type-excel2" class="menu-icon"></iconify-icon>
                    </button>';

                    // Teslim alma butonu - sadece aktif zimmetler için göster
                    $returnBtn = '';
                    if ($item->status === 'active' && strpos($item->status, 'returned_') !== 0) {
                        // İlk ikon seçeneğini kullan, diğerleri yedek
                        $returnBtn = '<button type="button" class="return-device-btn bg-warning-focus bg-hover-warning-200 text-warning-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" data-id="'.$item->id. '">
                            <iconify-icon icon="tabler:arrow-back" class="menu-icon"></iconify-icon>
                        </button>';
                    }

                    return '<div class="d-flex align-items-center gap-10 justify-content-center">'.$excelBtn.$returnBtn.$editBtn.$deleteBtn.'</div>';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);

            return $obj;
        }

        return view("backend.$this->page.list");
    }

    /**
     * Zimmet atama formu
     */
    public function form(Request $request, $unique = null)
    {
        $item = new UserDebitDevice();

        if (!is_null($unique)) {
            $item = UserDebitDevice::with($this->relation)->find($unique);

            if (is_null($item)) {
                return redirect()->back()->with('error', 'Kayıt bulunamadı');
            }

            // Tarih alanlarını doğru formatta hazırla
            if (isset($item->start_date) && !is_string($item->start_date)) {
                $item->start_date = $item->start_date->format('Y-m-d');
            }

            if (isset($item->end_date) && !is_string($item->end_date)) {
                $item->end_date = $item->end_date->format('Y-m-d');
            }
        }

        return view("backend.$this->page.form", compact('item'));
    }

    /**
     * Override BaseController save method for proper request validation
     */
    public function save(Request $request, $unique = null)
    {
        // Form doğrulama işlemi - UserDebitDeviceRequest kullanarak
        $validator = Validator::make($request->all(),
            (new UserDebitDeviceRequest())->rules(),
            (new UserDebitDeviceRequest())->messages()
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Form doğrulama başarılı, işleme devam et
        $params = $request->all();

        // Özel saveHook işlemleri
        // Eğer cihaz başka birine atanmışsa kontrol et
        $existingAssignment = UserDebitDevice::where('debit_device_id', $params['debit_device_id'])
            ->where('status', 'active')
            ->where('id', '!=', $request->route('unique'))
            ->first();

        if ($existingAssignment) {
            return redirect()->back()->with('error', 'Bu cihaz zaten başka bir kullanıcıya atanmış.')->withInput();
        }

        // Yeni kayıt için status'u active olarak ayarla
        if (is_null($unique)) {
            $params['status'] = 'active';

            // Şirket ve şube bilgilerini ekleyelim
            $user = Auth::user();
            $params['company_id'] = $user->company_id;
            $params['branch_id'] = $user->branch_id;
            $params['created_by'] = $user->id;
        }

        // Kaydetme işlemi
        if (is_null($unique)) {
            $obj = $this->model::create($params);
        } else {
            $obj = $this->model::find((int)$unique);
            $obj->update($params);
        }

        return redirect()->route("backend." . $this->page . "_list")->with('success', 'Zimmet ataması başarılı şekilde kaydedildi');
    }

    /**
     * Zimmet atama silme
     */
    public function delete(Request $request)
    {
        $id = (int) $request->post('id');
        $item = UserDebitDevice::find($id);

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Kayıt bulunamadı']);
        }

        try {
            $item->delete();
            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Silme işlemi sırasında bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Zimmet teslim alma işlemi
     */
    public function returnDevice(Request $request)
    {
        $id = (int) $request->post('id');
        $returnDate = $request->post('return_date');
        $returnNote = $request->post('return_note');

        $item = UserDebitDevice::find($id);

        if (!$item) {
            return redirect()->back()->with('error', 'Zimmet kaydı bulunamadı');
        }

        if ($item->status !== 'active') {
            return redirect()->back()->with('error', 'Bu zimmet zaten teslim alınmış veya süresi dolmuş');
        }

        try {
            // Benzersiz bir durum değeri oluştur
            $uniqueStatus = 'returned_' . $item->id . '_' . time();

            // Zimmet durumunu güncelle - benzersiz bir değer kullanarak unique constraint'i bypass et
            $item->status = $uniqueStatus;
            $item->end_date = $returnDate;

            // Eğer not varsa, mevcut notlara ekle
            if (!empty($returnNote)) {
                $existingNotes = $item->notes ?? '';
                $newNote = "Teslim Alma Notu (" . date('d.m.Y') . "): " . $returnNote;

                if (!empty($existingNotes)) {
                    $item->notes = $existingNotes . "\n\n" . $newNote;
                } else {
                    $item->notes = $newNote;
                }
            }

            $item->save();

            return redirect()->route('backend.' . $this->page . '_list')->with('success', 'Zimmet başarıyla teslim alındı');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Teslim alma işlemi sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request, $id)
    {
        try {
            // İlgili zimmet kaydını bul
            $userDebitDevice = UserDebitDevice::with(['user', 'debitDevice'])->findOrFail($id);

            // Aynı kullanıcı ve aynı tarihte başka zimmetler var mı kontrol et
            $sameUserSameDate = UserDebitDevice::with(['debitDevice'])
                ->where('user_id', $userDebitDevice->user_id)
                ->whereDate('start_date', $userDebitDevice->start_date)
                ->where('id', '!=', $id)
                ->get();

            // Birleştirme seçeneği kontrolü
            $mergeDevices = $request->get('merge', false);

            $devices = collect([$userDebitDevice]);
            if ($mergeDevices && $sameUserSameDate->count() > 0) {
                $devices = $devices->merge($sameUserSameDate);
            }

                        // Excel oluştur
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Başlık ayarları
            $sheet->setTitle('Zimmet Tutanağı');

            // Sayfa genişliği ayarları - A4 boyutunda
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            // Tüm sayfaya kalın mavi border
            $sheet->getStyle('A1:E50')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('A1:E50')->getBorders()->getOutline()->getColor()->setRGB('1F4E79');

            // Kolon genişliklerini önce ayarla
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(15);

            // ROW 1: Üst başlık - ZİMMET TUTANAĞI | ÖZSAĞLAM DTM | LOGO
            $sheet->setCellValue('A1', 'ZİMMET TUTANAĞI');
            $sheet->mergeCells('A1:B1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('A1')->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getRowDimension(1)->setRowHeight(50);

            $sheet->setCellValue('C1', 'ÖZSAĞLAM DTM');
            $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(18)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('C1')->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getStyle('C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('C1')->getFill()->getStartColor()->setRGB('1F4E79');

            // Logo alanı - daha geniş
            $sheet->setCellValue('D1', 'ÇINAR');
            $sheet->mergeCells('D1:E1');
            $sheet->getStyle('D1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('D1')->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getStyle('D1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('D1')->getFill()->getStartColor()->setRGB('1F4E79');

            // ROW 2: İlgili/İlişkili Ekipman | Boş alan | Tarih
            $sheet->setCellValue('A2', 'İlgili / İlişkili Ekipman:');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('A2')->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getStyle('A2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('A2')->getFill()->getStartColor()->setRGB('1F4E79');
            $sheet->getRowDimension(2)->setRowHeight(35);

            $sheet->setCellValue('B2', '');
            $sheet->mergeCells('B2:C2');
            $sheet->getStyle('B2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('B2')->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');

            $sheet->setCellValue('D2', 'Tarih:');
            $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('D2')->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');

            $sheet->setCellValue('E2', '');
            $sheet->getStyle('E2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('E2')->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');

                        // ROW 3-6: Açıklama metni kutusu (4 satır yüksekliğinde)
            $explanationText = "İş yerinde kullanılması gereken ve aşağıda adı geçen ekipmanları sağlam, çalışır vaziyette teslim aldım.\n\nİşim ile ilgili verilen bu ekipmanların bakımını yaparak muhafaza edeceğimi, doğru ve uygun şekilde kullanacağımı, iş sonunda ilgili ekipmanı tüm parçaları ile birim yetkilisine ve/veya depo sorumlusuna teslim edeceğimi beyan ve taahhüt ediyorum.";

            $sheet->setCellValue('A3', $explanationText);
            $sheet->mergeCells('A3:E6');
            $sheet->getStyle('A3')->getAlignment()->setWrapText(true);
            $sheet->getStyle('A3')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('A3')->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getStyle('A3')->getFont()->setSize(11);

            // Açıklama kutusu için satır yükseklikleri
            $sheet->getRowDimension(3)->setRowHeight(30);
            $sheet->getRowDimension(4)->setRowHeight(30);
            $sheet->getRowDimension(5)->setRowHeight(30);
            $sheet->getRowDimension(6)->setRowHeight(30);

            // ROW 7: ZİMMET TARİHİ satırı
            $sheet->setCellValue('A7', '');
            $sheet->mergeCells('A7:D7');
            $sheet->getStyle('A7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('A7')->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getRowDimension(7)->setRowHeight(35);

            $sheet->setCellValue('E7', 'ZİMMET TARİHİ');
            $sheet->getStyle('E7')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('E7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('E7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('E7')->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');

            // Başlangıç satırı
            $row = 8;

            // ROW 8: TESLİM EDİLEN EKİPMANLAR başlığı
            $sheet->setCellValue('A' . $row, 'TESLİM EDİLEN EKİPMANLAR');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setRGB('1F4E79');
            $sheet->getRowDimension($row)->setRowHeight(35);

            // ROW 9: Tablo başlıkları
            $row++;
            $sheet->setCellValue('A' . $row, 'SIRA');
            $sheet->setCellValue('B' . $row, 'EKİPMAN');
            $sheet->setCellValue('C' . $row, 'MİKTARI');
            $sheet->setCellValue('D' . $row, 'CİNSİ' . "\n" . '(adet/kv.)');
            $sheet->setCellValue('E' . $row, '');

            // Başlık stilini ayarla
            $headerRange = 'A' . $row . ':E' . $row;
            $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($headerRange)->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($row)->setRowHeight(40);

            // ROW 10+: Veri satırları (15 satır sabit)
            $row++;
            $deviceIndex = 1;

            // Cihazları listele
            foreach ($devices as $device) {
                $sheet->setCellValue('A' . $row, $deviceIndex);
                $sheet->setCellValue('B' . $row, $device->debitDevice->name);
                $sheet->setCellValue('C' . $row, '1');
                $sheet->setCellValue('D' . $row, 'adet');
                $sheet->setCellValue('E' . $row, '');

                // Satır stilini ayarla
                $rowRange = 'A' . $row . ':E' . $row;
                $sheet->getStyle($rowRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
                $sheet->getStyle($rowRange)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('B' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($row)->setRowHeight(40);

                $row++;
                $deviceIndex++;
            }

            // En az 15 satır olacak şekilde boş satırlar ekle
            while ($deviceIndex <= 15) {
                $sheet->setCellValue('A' . $row, $deviceIndex);
                $sheet->setCellValue('B' . $row, '');
                $sheet->setCellValue('C' . $row, '');
                $sheet->setCellValue('D' . $row, '');
                $sheet->setCellValue('E' . $row, '');

                $rowRange = 'A' . $row . ':E' . $row;
                $sheet->getStyle($rowRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
                $sheet->getStyle($rowRange)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('B' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($row)->setRowHeight(40);

                $row++;
                $deviceIndex++;
            }

                                    // İmza alanları başlığı
            $row++;
            $sheet->setCellValue('A' . $row, 'TESLİM EDEN');
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->getStartColor()->setRGB('C5D9F1');
            $sheet->getRowDimension($row)->setRowHeight(35);

            $sheet->setCellValue('C' . $row, 'TESLİM ALAN');
            $sheet->mergeCells('C' . $row . ':E' . $row);
            $sheet->getStyle('C' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C' . $row . ':E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('C' . $row . ':E' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getStyle('C' . $row . ':E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('C' . $row . ':E' . $row)->getFill()->getStartColor()->setRGB('C5D9F1');

            // İmza alanları (boş kutular) - görseldeki gibi 4 satır
            for ($i = 1; $i <= 4; $i++) {
                $row++;
                $sheet->setCellValue('A' . $row, '');
                $sheet->mergeCells('A' . $row . ':B' . $row);
                $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
                $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
                $sheet->getRowDimension($row)->setRowHeight(40);

                $sheet->setCellValue('C' . $row, '');
                $sheet->mergeCells('C' . $row . ':E' . $row);
                $sheet->getStyle('C' . $row . ':E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
                $sheet->getStyle('C' . $row . ':E' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            }

            // Alt kısım - İletişim bilgileri
            $row++;

            $sheet->setCellValue('A' . $row, 'İLETİŞİM BİLGİLERİ');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setRGB('1F4E79');
            $sheet->getRowDimension($row)->setRowHeight(35);

            $sheet->setCellValue('B' . $row, '0530 395 2477');
            $sheet->getStyle('B' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('B' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');

            $sheet->setCellValue('C' . $row, '');
            $sheet->mergeCells('C' . $row . ':E' . $row);
            $sheet->getStyle('C' . $row . ':E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('C' . $row . ':E' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');

            $row++;
            $sheet->setCellValue('A' . $row, '');
            $sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');
            $sheet->getRowDimension($row)->setRowHeight(35);

            $sheet->setCellValue('B' . $row, 'ozsaglam@ozsaglamltd.com');
            $sheet->getStyle('B' . $row)->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('B' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');

            $sheet->setCellValue('C' . $row, 'www.ozsaglam.com');
            $sheet->mergeCells('C' . $row . ':E' . $row);
            $sheet->getStyle('C' . $row)->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C' . $row . ':E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyle('C' . $row . ':E' . $row)->getBorders()->getAllBorders()->getColor()->setRGB('1F4E79');

            // Excel dosyasını oluştur
            $writer = new Xlsx($spreadsheet);
            $fileName = 'zimmet_tutanagi_' . $userDebitDevice->user->name . '_' . $userDebitDevice->user->surname . '_' . date('d_m_Y', strtotime($userDebitDevice->start_date)) . '.xlsx';

            // HTTP headers
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Excel dosyası oluşturulurken hata oluştu: ' . $e->getMessage());
        }
    }

    public function checkMergeDevices(Request $request, $id)
    {
        try {
            $userDebitDevice = UserDebitDevice::with(['user'])->findOrFail($id);

            // Aynı kullanıcı ve aynı tarihte başka zimmetler var mı kontrol et
            $sameUserSameDate = UserDebitDevice::with(['debitDevice'])
                ->where('user_id', $userDebitDevice->user_id)
                ->whereDate('start_date', $userDebitDevice->start_date)
                ->where('id', '!=', $id)
                ->get();

            if ($sameUserSameDate->count() > 0) {
                $deviceNames = $sameUserSameDate->pluck('debitDevice.name')->join(', ');
                return response()->json([
                    'has_multiple' => true,
                    'message' => 'Bu personele ait aynı tarihte ' . $sameUserSameDate->count() . ' adet daha zimmet kaydı bulundu: ' . $deviceNames . '. Bu kayıtları birleştirmek istiyor musunuz?',
                    'devices' => $sameUserSameDate->count()
                ]);
            }

            return response()->json(['has_multiple' => false]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Kontrol sırasında hata oluştu.'], 500);
        }
    }
}
