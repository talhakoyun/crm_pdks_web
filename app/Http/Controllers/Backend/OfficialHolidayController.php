<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\OfficialHolidayRequest;
use App\Models\OfficialHoliday;
use App\Models\OfficialType;
use App\Services\OfficialHolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OfficialHolidayController extends BaseController
{
    use BasePattern;

    protected OfficialHolidayService $holidayService;

    public function __construct(OfficialHolidayService $holidayService)
    {
        $this->title = 'Resmi Tatil Günleri';
        $this->page = 'official_holiday';
        $this->upload = 'official_holiday';
        $this->model = new OfficialHoliday();
        $this->request = new OfficialHolidayRequest();

        $this->view = (object)array(
            'breadcrumb' => array(
                'Resmi Tatil Günleri' => route('backend.official_holiday_list'),
            ),
        );

        $this->holidayService = $holidayService;

        view()->share('holidayTypes', OfficialType::all());
        parent::__construct();
    }

    public function list(Request $request)
    {

        if ($request->ajax()) {
            $query = OfficialHoliday::select('*');

            return DataTables::of($query)
                ->editColumn('start_date', function ($item) {
                    return '<div class="d-flex align-items-center justify-content-center"><iconify-icon icon="mdi:calendar-start" style="font-size: 18px; margin-right: 5px;"></iconify-icon>' . Carbon::parse($item->start_date)->format('d.m.Y') . '</div>';
                })
                ->editColumn('end_date', function ($item) {
                    return '<div class="d-flex align-items-center justify-content-center"><iconify-icon icon="mdi:calendar-end" style="font-size: 18px; margin-right: 5px;"></iconify-icon>' . Carbon::parse($item->end_date)->format('d.m.Y') . '</div>';
                })
                ->editColumn('type_id', function ($item) {
                    $type = OfficialType::find($item->type_id);
                    return $item->type_id ? '<span class="badge bg-' . $type->color . '">' . $type->name . '</span>' : '<span class="badge bg-danger">-</span>';
                })
                ->editColumn('is_active', function ($item) {
                    return $item->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Pasif</span>';
                })
                ->rawColumns(['start_date', 'end_date', 'is_active', 'type_id'])
                ->make(true);
        }


        return view('backend.official_holiday.list');
    }

    public function form(Request $request, $unique = null)
    {
        $today = Carbon::now()->format('Y-m-d');

        // Upcoming holidays for sidebar
        $holidays = OfficialHoliday::active()
            ->where(function ($query) use ($today) {
                $query->where('start_date', '>=', $today)
                    ->orWhere('end_date', '>=', $today);
            })
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        if ($unique) {
            $holiday = OfficialHoliday::findOrFail($unique);
            return view('backend.official_holiday.form', compact('holiday', 'holidays'));
        }

        return view('backend.official_holiday.form', compact('holidays'));
    }

    public function save(Request $request, $unique = null)
    {
        try {

            // Form Request instance oluştur ve validate et
            $formRequest = new OfficialHolidayRequest();
            $validator = Validator::make($request->all(), $formRequest->rules(), $formRequest->messages());

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // saveHook ile veri hazırlama (tarih validation dahil)
            try {
                $data = $this->saveHook($request);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'errors' => ['genel' => [$e->getMessage()]]
                ], 422);
            }

            // Kaydetme işlemi
            if ($unique) {
                $holiday = OfficialHoliday::findOrFail($unique);
                $holiday->update($data);
                $message = 'Tatil günü başarıyla güncellendi.';
            } else {
                $holiday = OfficialHoliday::create($data);
                $message = 'Tatil günü başarıyla eklendi.';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveHook(Request $request)
    {
        $data = $request->all();

        // Tarih formatını kontrol et ve düzelt
        if (isset($data['start_date'])) {
            try {
                // d/m/Y formatından Y-m-d formatına çevir
                $startDate = Carbon::createFromFormat('d/m/Y', $data['start_date']);
                $data['start_date'] = $startDate->format('Y-m-d');
            } catch (\Exception $e) {
                // Hatalı tarih formatı
                throw new \Exception('Başlangıç tarihi geçerli bir format olmalıdır (dd/mm/yyyy).');
            }
        }

        if (isset($data['end_date'])) {
            try {
                // d/m/Y formatından Y-m-d formatına çevir
                $endDate = Carbon::createFromFormat('d/m/Y', $data['end_date']);
                $data['end_date'] = $endDate->format('Y-m-d');

                // Bitiş tarihi başlangıç tarihinden önce olamaz kontrolü
                if (isset($data['start_date'])) {
                    $startDate = Carbon::parse($data['start_date']);
                    if ($endDate->lt($startDate)) {
                        throw new \Exception('Bitiş tarihi, başlangıç tarihinden önce olamaz.');
                    }
                }
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'Bitiş tarihi') !== false) {
                    throw $e; // Kendi hata mesajımızı yeniden fırlat
                }
                // Hatalı tarih formatı
                throw new \Exception('Bitiş tarihi geçerli bir format olmalıdır (dd/mm/yyyy).');
            }
        }

        return $data;
    }

    public function saveBack($obj)
    {
        return redirect()->route('backend.official_holiday_calendar')
            ->with('success', 'Tatil günü başarıyla kaydedildi');
    }

    /**
     * API'den resmi tatilleri çekme
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOfficialHolidays(Request $request)
    {
        try {
            DB::beginTransaction();

            // Resmi tatilleri API'den çekiyoruz
            $officialHolidays = $this->holidayService->getOfficialHolidays();

            // Eğer resmi tatiller boş gelirse hata döndürüyoruz
            if (empty($officialHolidays)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resmi tatil günleri bulunamadı.'
                ]);
            }

            // Mevcut resmi tatilleri siliyoruz
            OfficialHoliday::whereYear('start_date', Carbon::now()->year)
                ->whereYear('end_date', Carbon::now()->year)
                ->delete();

            // Resmi tatil günlerini ekliyoruz
            foreach ($officialHolidays as $holiday) {
                OfficialHoliday::create([
                    'title' => $holiday['title'],
                    'description' => $holiday['description'] ?? null,
                    'start_date' => $holiday['start_date'],
                    'end_date' => $holiday['end_date'],
                    'is_active' => true
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resmi tatil günleri başarıyla eklendi',
                'data' => [
                    'official_count' => count($officialHolidays),
                    'total_count' => count($officialHolidays)
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Tatil günleri eklenirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Toplu tatil günü ekleme (personellere)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkAddToUsers(Request $request)
    {
        $validator = validator($request->all(), [
            'holiday_ids' => 'required|array',
            'holiday_ids.*' => 'required|exists:official_holidays,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,id',
            'type_id' => 'required|exists:official_types,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            $holidayIds = $request->input('holiday_ids');
            $userIds = $request->input('user_ids');
            $typeId = $request->input('type_id');

            // Seçilen tatil günlerini alıyoruz
            $holidays = OfficialHoliday::whereIn('id', $holidayIds)->get();

            // Kullanıcı bazında tatil günlerini ekliyoruz
            $addedCount = 0;
            foreach ($userIds as $userId) {
                foreach ($holidays as $holiday) {
                    // Aynı gün için zaten izin var mı kontrol ediyoruz
                    $existingHoliday = \App\Models\Holiday::where('user_id', $userId)
                        ->where(function ($query) use ($holiday) {
                            $query->whereBetween('start_date', [$holiday->start_date, $holiday->end_date])
                                ->orWhereBetween('end_date', [$holiday->start_date, $holiday->end_date]);
                        })
                        ->first();

                    // Eğer bu gün için izin yoksa ekliyoruz
                    if (!$existingHoliday) {
                        \App\Models\Holiday::create([
                            'company_id' => Auth::user()->company_id,
                            'branch_id' => Auth::user()->branch_id,
                            'user_id' => $userId,
                            'start_date' => $holiday->start_date,
                            'end_date' => $holiday->end_date,
                            'type_id' => $typeId,
                            'note' => $holiday->title,
                            'status' => 'approved',
                            'created_by' => Auth::id(),
                        ]);
                        $addedCount++;
                    }
                }
            }

            DB::commit();

            if ($addedCount === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Seçilen tüm tatil günleri zaten personellere eklenmiş.'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Toplam ' . $addedCount . ' tatil günü ' . count($userIds) . ' personele başarıyla eklendi.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Tatil günleri eklenirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Takvim görünümünü gösterir
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function calendar(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $years = range(Carbon::now()->year - 2, Carbon::now()->year + 2);

        return view('backend.official_holiday.calendar', compact('year', 'years'));
    }

    /**
     * Takvim için etkinlikleri getirir
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalendarEvents(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');
        $holidays = OfficialHoliday::active()
            ->with('type') // Tatil türü bilgisini de getir
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->get();

        $events = [];

        foreach ($holidays as $holiday) {
            // Tatil türüne göre renk ve sınıf belirle
            $backgroundColor = '#28c76f'; // Default yeşil
            $borderColor = '#28c76f';
            $className = 'holiday-custom';

            if ($holiday->type_id == 1) {
                $backgroundColor = '#ea5455';
                $borderColor = '#ea5455';
                $className = 'holiday-official';
            } elseif ($holiday->type_id == 2) {
                $backgroundColor = '#ff9f43';
                $borderColor = '#ff9f43';
                $className = 'holiday-weekend';
            } elseif ($holiday->type_id == 3) {
                $backgroundColor = '#00cfe8';
                $borderColor = '#00cfe8';
                $className = 'holiday-half-day';
            } elseif ($holiday->type_id == 4) {
                $backgroundColor = '#28c76f';
                $borderColor = '#28c76f';
                $className = 'holiday-custom';
            } elseif ($holiday->type_id == 5) {
                $backgroundColor = '#28c76f';
                $borderColor = '#28c76f';
                $className = 'holiday-custom';
            }

            // Tarihler için doğru formatları hazırla
            // Carbon'un parse metodunu kullanarak string'i tarihe çevir,
            // sonra format metoduyla FullCalendar'ın anladığı formata dönüştür
            $startDate = Carbon::parse($holiday->start_date)->startOfDay();

            // Clone ile yeni bir kopya oluştur, 1 gün ekle (FullCalendar için)
            $endDate = Carbon::parse($holiday->end_date)->endOfDay()->addDay();

            $events[] = [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'allDay' => true,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'className' => $className,
                'extendedProps' => [
                    'description' => $holiday->description,
                    'date_range' => $holiday->date_range,
                    'days_count' => $holiday->days_count,
                    'type_name' => $holiday->type ? $holiday->type->name : 'Bilinmeyen Tür',
                    'type_color' => $holiday->type ? $holiday->type->color : 'secondary'
                ]
            ];
        }

        return response()->json($events);
    }

    /**
     * Bugün ve sonrası için aktif tatil günlerini getirir
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveHolidays(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');

        $holidays = OfficialHoliday::active()
            ->where(function ($query) use ($today) {
                $query->where('start_date', '>=', $today)
                    ->orWhere('end_date', '>=', $today);
            })
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $holidays
        ]);
    }

    /**
     * Form sayfası için yaklaşan tatil günlerini getirir
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpcomingHolidays()
    {
        $today = Carbon::now()->format('Y-m-d');

        $holidays = OfficialHoliday::active()
            ->where(function ($query) use ($today) {
                $query->where('start_date', '>=', $today)
                    ->orWhere('end_date', '>=', $today);
            })
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        $formattedHolidays = $holidays->map(function ($holiday) {
            return [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'date_range' => $holiday->date_range,
                'description' => $holiday->description,
                'start_date' => $holiday->start_date->format('d/m/Y H:i'),
                'end_date' => $holiday->end_date->format('d/m/Y H:i'),
            ];
        });

        return response()->json($formattedHolidays);
    }
}
