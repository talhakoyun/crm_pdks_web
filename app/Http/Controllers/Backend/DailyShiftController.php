<?php

namespace App\Http\Controllers\Backend;

use App\Models\UserShiftSchedule;
use App\Models\ShiftDefinition;
use App\Models\UserShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyShiftController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Günlük Vardiya Yönetimi';
        $this->page = 'daily_shift';
        $this->model = new UserShiftSchedule();
        $this->relation = ['user', 'shiftDefinition', 'creator'];

        $this->view = (object)array(
            'breadcrumb' => array(
                'Vardiya Yönetimi' => '#',
                'Günlük Vardiya Yönetimi' => route('backend.daily_shift_list'),
            ),
        );

        parent::__construct();
    }

    /**
     * Haftalık takvim görünümü
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();
        
        // Hafta seçimi - varsayılan bu hafta
        $weekStart = $request->get('week') ? 
            Carbon::parse($request->get('week'))->startOfWeek() : 
            Carbon::now()->startOfWeek();
        
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Kullanıcıları filtrele (role_id 1,2,3 hariç)
        $usersQuery = User::with(['userShift.shiftDefinition', 'branch', 'department'])
            ->where('is_active', 1)
            ->whereNotIn('role_id', [1, 2, 3]);

        // Rol bazlı filtreleme
        if (!in_array($user->role_id, [1, 2])) {
            $usersQuery->where('company_id', $user->company_id);
            
            if (!in_array($user->role_id, [3, 4]) && $user->branch_id) {
                $usersQuery->where('branch_id', $user->branch_id);
            }
        }

        $users = $usersQuery->get();

        // Vardiya tanımlarını getir
        $shiftDefinitionsQuery = ShiftDefinition::active();
        if (!in_array($user->role_id, [1, 2])) {
            $shiftDefinitionsQuery->where('company_id', $user->company_id);
        }
        $shiftDefinitions = $shiftDefinitionsQuery->get();

        // Bu hafta için özel vardiya atamalarını getir
        $schedules = UserShiftSchedule::with(['user', 'shiftDefinition'])
            ->whereBetween('schedule_date', [$weekStart, $weekEnd])
            ->where('is_active', 1)
            ->get()
            ->keyBy(function($item) {
                return $item->user_id . '_' . $item->schedule_date->format('Y-m-d');
            });

        // Haftalık günler
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = $weekStart->copy()->addDays($i);
        }

        return view('backend.daily_shift.calendar', compact(
            'users', 
            'shiftDefinitions', 
            'schedules', 
            'weekDays', 
            'weekStart',
            'weekEnd'
        ));
    }

    /**
     * Günlük vardiya kaydet/güncelle
     */
    public function saveDaily(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_date' => 'required|date',
            'shift_definition_id' => 'nullable|exists:shift_definitions,id',
            'schedule_type' => 'required|in:regular,custom,holiday,overtime,off'
        ]);

        try {
            DB::beginTransaction();

            $userId = $request->user_id;
            $scheduleDate = $request->schedule_date;
            $shiftId = $request->shift_definition_id;
            $scheduleType = $request->schedule_type;

            // Mevcut kaydı kontrol et
            $existingSchedule = UserShiftSchedule::where('user_id', $userId)
                ->whereDate('schedule_date', $scheduleDate)
                ->first();

            if ($scheduleType === 'off') {
                // İzin günü - vardiya atamasını kaldır
                if ($existingSchedule) {
                    $existingSchedule->delete();
                }
                
                // İzin kaydı oluştur
                UserShiftSchedule::create([
                    'user_id' => $userId,
                    'schedule_date' => $scheduleDate,
                    'shift_definition_id' => null,
                    'schedule_type' => UserShiftSchedule::TYPE_OFF,
                    'is_active' => 1,
                    'created_by' => Auth::id(),
                    'notes' => 'İzin günü'
                ]);

            } else if ($scheduleType === 'regular') {
                // Normal vardiya - özel atamayı sil, normal vardiya devreye girer
                if ($existingSchedule) {
                    $existingSchedule->delete();
                }

            } else {
                // Özel vardiya atama
                if ($existingSchedule) {
                    $existingSchedule->update([
                        'shift_definition_id' => $shiftId,
                        'schedule_type' => $scheduleType,
                        'updated_at' => now()
                    ]);
                } else {
                    UserShiftSchedule::create([
                        'user_id' => $userId,
                        'schedule_date' => $scheduleDate,
                        'shift_definition_id' => $shiftId,
                        'schedule_type' => $scheduleType,
                        'is_active' => 1,
                        'created_by' => Auth::id()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vardiya başarıyla güncellendi.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Vardiya güncellenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kullanıcının belirli bir gün için vardiyasını getir
     */
    public function getUserShift(Request $request)
    {
        $userId = $request->user_id;
        $date = $request->date;

        // Önce özel vardiya var mı kontrol et
        $schedule = UserShiftSchedule::where('user_id', $userId)
            ->whereDate('schedule_date', $date)
            ->where('is_active', 1)
            ->with('shiftDefinition')
            ->first();

        if ($schedule) {
            if ($schedule->schedule_type === UserShiftSchedule::TYPE_OFF) {
                return response()->json([
                    'type' => 'off',
                    'shift' => null,
                    'message' => 'İzin günü'
                ]);
            } else {
                return response()->json([
                    'type' => 'custom',
                    'shift' => $schedule->shiftDefinition,
                    'schedule_type' => $schedule->schedule_type
                ]);
            }
        }

        // Özel vardiya yoksa normal vardiyayı kontrol et
        $userShift = UserShift::where('user_id', $userId)
            ->where('is_active', 1)
            ->with('shiftDefinition')
            ->first();

        if ($userShift && $userShift->shiftDefinition) {
            $dayOfWeek = Carbon::parse($date)->format('l'); // Monday, Tuesday, etc.
            $dayKey = strtolower($dayOfWeek); // monday, tuesday, etc.
            
            // O gün için çalışma saati var mı kontrol et
            if ($userShift->shiftDefinition->isWorkingDay($dayKey)) {
                return response()->json([
                    'type' => 'regular',
                    'shift' => $userShift->shiftDefinition,
                    'day_schedule' => $userShift->shiftDefinition->getDaySchedule($dayKey)
                ]);
            }
        }

        return response()->json([
            'type' => 'none',
            'shift' => null,
            'message' => 'Vardiya atanmamış'
        ]);
    }

    /**
     * Haftalık toplu işlem
     */
    public function saveBulk(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'week_start' => 'required|date',
            'shifts' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            $userIds = $request->user_ids;
            $weekStart = Carbon::parse($request->week_start);
            $shifts = $request->shifts; // [day_index => shift_id] formatında

            $savedCount = 0;

            foreach ($userIds as $userId) {
                foreach ($shifts as $dayIndex => $shiftId) {
                    $scheduleDate = $weekStart->copy()->addDays($dayIndex);
                    
                    if ($shiftId === 'off') {
                        // İzin günü
                        UserShiftSchedule::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'schedule_date' => $scheduleDate->format('Y-m-d')
                            ],
                            [
                                'shift_definition_id' => null,
                                'schedule_type' => UserShiftSchedule::TYPE_OFF,
                                'is_active' => 1,
                                'created_by' => Auth::id(),
                                'notes' => 'Toplu işlem - İzin günü'
                            ]
                        );
                        $savedCount++;
                    } else if ($shiftId === 'regular') {
                        // Normal vardiya - özel kaydı sil
                        UserShiftSchedule::where('user_id', $userId)
                            ->whereDate('schedule_date', $scheduleDate->format('Y-m-d'))
                            ->delete();
                    } else if ($shiftId) {
                        // Özel vardiya
                        UserShiftSchedule::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'schedule_date' => $scheduleDate->format('Y-m-d')
                            ],
                            [
                                'shift_definition_id' => $shiftId,
                                'schedule_type' => UserShiftSchedule::TYPE_CUSTOM,
                                'is_active' => 1,
                                'created_by' => Auth::id(),
                                'notes' => 'Toplu işlem'
                            ]
                        );
                        $savedCount++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$savedCount} adet vardiya ataması başarıyla kaydedildi."
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Toplu işlem sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}
