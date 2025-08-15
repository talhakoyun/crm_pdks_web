<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ShiftFollow;
use App\Models\ShiftFollowType;
use App\Models\ShiftDefinition;
use App\Models\User;
use App\Models\Department;
use App\Models\Branch;
use App\Models\Holiday;
use App\Models\HolidayType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Bugünkü giriş-çıkış istatistikleri
        $today = Carbon::today();
        $company_id = Auth::user()->company_id;

        // Personel sayıları ve durumları
        $totalUsers = User::where('company_id', $company_id)->where('is_active', 1)->where('role_id', 7)->count();
        $activeUsers = User::where('company_id', $company_id)->where('is_active', 1)->where('role_id', 7)->count();

        // Departman bazlı personel sayıları
        $departmentStats = Department::select('departments.id', 'departments.title as name', DB::raw('COUNT(users.id) as total_users'))
            ->leftJoin('users', 'departments.id', '=', 'users.department_id')
            ->where('users.company_id', $company_id)
            ->where('users.is_active', 1)
            ->where('users.role_id', 7)
            ->groupBy('departments.id', 'departments.title')
            ->get();

        // Bugün işe gelen personel sayısı
        $presentToday = ShiftFollow::where('company_id', $company_id)
            ->whereDate('transaction_date', $today)
            ->whereHas('followType', function ($query) {
                $query->where('type', 'in');
            })
            ->distinct('user_id')
            ->count('user_id');

        // Bugün gelmeyenler (izinli, hastalık vb dahil)
        $absentToday = $activeUsers - $presentToday;

        // Son İzin işlemleri
        $recentTransactions = Holiday::with(['user', 'holidayType', 'branch'])
            ->where('company_id', $company_id)
            ->limit(5)
            ->get();

        $holidayTypes = HolidayType::all();
        // Lokasyon bazlı personel durumları
        $branchStats = Branch::select('branches.id', 'branches.title', DB::raw('COUNT(DISTINCT shift_follows.user_id) as present_users'))
            ->leftJoin('shift_follows', function ($join) use ($today) {
                $join->on('branches.id', '=', 'shift_follows.branch_id')
                    ->whereDate('shift_follows.transaction_date', $today);
            })
            ->where('branches.company_id', $company_id)
            ->groupBy('branches.id', 'branches.title')
            ->get();

        // Geç gelen personeller
        $lateArrivals = ShiftFollow::with('user')
            ->where('company_id', $company_id)
            ->whereDate('transaction_date', $today)
            ->whereHas('followType', function ($query) {
                $query->where('type', 'in');
            })
            ->where('status', \App\Models\ShiftFollow::STATUS_LATE)
            ->get();

        // Haftalık giriş istatistikleri
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weeklyStats = ShiftFollow::select(DB::raw('DATE(transaction_date) as date'), DB::raw('COUNT(DISTINCT user_id) as user_count'))
            ->where('company_id', $company_id)
            ->whereBetween('transaction_date', [$weekStart, $weekEnd])
            ->whereHas('followType', function ($query) {
                $query->where('type', 'in');
            })
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->get();

        // Bugünkü personel durumlarının detaylı analizi
        $userStatuses = $this->getUserStatuses($company_id, $today);

        // En uzun süre çalışan personeller (bugün)
        $longestHours = $this->getLongestWorkingUsers($company_id, $today);

        // Yaklaşan vardiyalar - personel sayıları ile birlikte
        $upcomingShifts = ShiftDefinition::with('branch')
            ->where('company_id', $company_id)
            ->whereDate('start_time', '>=', $today)
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        // Her vardiya için personel sayılarını hesapla
        foreach ($upcomingShifts as $shift) {
            // Normal vardiya atamaları
            $userShiftsCount = DB::table('user_shifts')
                ->where('shift_definition_id', $shift->id)
                ->count();

            // Özel vardiya atamaları
            $userShiftCustomsCount = DB::table('user_shift_customs')
                ->where('shift_definition_id', $shift->id)
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->count();

            // Toplam personel sayısı
            $shift->user_shifts_count = $userShiftsCount;
            $shift->user_shift_customs_count = $userShiftCustomsCount;
            $shift->total_personnel = $userShiftsCount + $userShiftCustomsCount;
        }

        // Personel eksik olan vardiyaları hesapla (5'ten az personeli olan vardiyalar)
        $eksikVardiyaSayisi = $upcomingShifts->filter(function ($shift) {
            return $shift->total_personnel < 5;
        })->count();

        return view('backend.dashboard.index', compact(
            'totalUsers',
            'activeUsers',
            'presentToday',
            'absentToday',
            'departmentStats',
            'branchStats',
            'recentTransactions',
            'holidayTypes',
            'lateArrivals',
            'weeklyStats',
            'longestHours',
            'upcomingShifts',
            'userStatuses',
            'eksikVardiyaSayisi'
        ));
    }

    private function getUserStatuses($company_id, $date)
    {
        // Bugün en az bir giriş veya çıkış kaydı olan kullanıcı ID'lerini al
        $activeUserIds = ShiftFollow::where('company_id', $company_id)
            ->whereDate('transaction_date', $date)
            ->whereHas('followType', function ($query) {
                $query->whereIn('type', ['in', 'out']);
            })
            ->pluck('user_id')
            ->unique()
            ->toArray();

        // Sadece bugün işlem yapan personelleri al
        $users = User::whereIn('id', $activeUserIds)
            ->where('company_id', $company_id)
            ->where('is_active', 1)
            ->where('role_id', 7)
            ->with(['department'])
            ->get();

        $userStatuses = [];

        foreach ($users as $user) {
            // Kullanıcının bugünkü en son giriş/çıkış işlemi
            $lastCheckIn = ShiftFollow::where('user_id', $user->id)
                ->whereDate('transaction_date', $date)
                ->whereHas('followType', function ($query) {
                    $query->where('type', 'in');
                })
                ->orderBy('transaction_date', 'desc')
                ->first();

            $lastCheckOut = ShiftFollow::where('user_id', $user->id)
                ->whereDate('transaction_date', $date)
                ->whereHas('followType', function ($query) {
                    $query->where('type', 'out');
                })
                ->orderBy('transaction_date', 'desc')
                ->first();

            $status = 'absent'; // Varsayılan durum: gelmedi
            $checkInTime = null;
            $checkOutTime = null;
            $workDuration = null;
            $currentLocation = null;

            if ($lastCheckIn) {
                // transaction_date değeri string olabilir, Carbon nesnesine dönüştürelim
                $checkInTime = $lastCheckIn->transaction_date instanceof Carbon
                    ? $lastCheckIn->transaction_date
                    : Carbon::parse($lastCheckIn->transaction_date);

                $currentLocation = $lastCheckIn->branch->title ?? null;

                if ($lastCheckOut) {
                    // Hem giriş hem çıkış yapmış
                    $status = 'checked_out';
                    // transaction_date değeri string olabilir, Carbon nesnesine dönüştürelim
                    $checkOutTime = $lastCheckOut->transaction_date instanceof Carbon
                        ? $lastCheckOut->transaction_date
                        : Carbon::parse($lastCheckOut->transaction_date);

                    // Çalışma süresi hesaplama
                    $workDuration = $checkInTime->diffInMinutes($checkOutTime);
                } else {
                    // Sadece giriş yapmış, çıkış yapmamış
                    $status = 'present';

                    // Şu ana kadar çalışma süresi
                    $workDuration = $checkInTime->diffInMinutes(Carbon::now());
                }

                // Status alanından geç giriş kontrolü
                if ($lastCheckIn->status === \App\Models\ShiftFollow::STATUS_LATE) {
                    $status = $status == 'checked_out' ? 'late_checked_out' : 'late';
                }
            }

            $userStatuses[] = [
                'user' => $user,
                'status' => $status,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'work_duration' => $workDuration,
                'current_location' => $currentLocation
            ];
        }

        return $userStatuses;
    }

    private function getLongestWorkingUsers($company_id, $date)
    {
        // En uzun süre çalışan personeller (bugün)
        $checkInOut = ShiftFollow::select(
            'user_id',
            DB::raw('MIN(CASE WHEN shift_follow_types.type = "in" THEN shift_follows.transaction_date END) as check_in_time'),
            DB::raw('MAX(CASE WHEN shift_follow_types.type = "out" THEN shift_follows.transaction_date END) as check_out_time')
        )
            ->join('shift_follow_types', 'shift_follows.shift_follow_type_id', '=', 'shift_follow_types.id')
            ->where('shift_follows.company_id', $company_id)
            ->whereDate('shift_follows.transaction_date', $date)
            ->whereIn('shift_follow_types.type', ['in', 'out'])
            ->groupBy('user_id')
            ->havingRaw('check_in_time IS NOT NULL')
            ->get();

        $userIds = $checkInOut->pluck('user_id')->toArray();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $longestHours = [];
        foreach ($checkInOut as $record) {
            if (!isset($users[$record->user_id])) continue;

            // DB::raw ile seçilen alanlar herzaman string olarak döner, Carbon nesnesine dönüştürelim
            $checkInTime = $record->check_in_time ? Carbon::parse($record->check_in_time) : null;
            $checkOutTime = $record->check_out_time ? Carbon::parse($record->check_out_time) : Carbon::now();

            // Eğer check_in_time null ise, diffInMinutes çağıramayız
            if (!$checkInTime) continue;

            $duration = $checkInTime->diffInMinutes($checkOutTime);
            $hours = floor($duration / 60);
            $minutes = $duration % 60;

            $longestHours[] = [
                'user' => $users[$record->user_id],
                'check_in' => $checkInTime,
                'check_out' => $record->check_out_time ? $checkOutTime : null,
                'duration_minutes' => $duration,
                'formatted_duration' => sprintf('%02d:%02d', $hours, $minutes)
            ];
        }

        // Süreye göre sırala
        usort($longestHours, function ($a, $b) {
            return $b['duration_minutes'] - $a['duration_minutes'];
        });

        // En uzun 5 kayıt
        return array_slice($longestHours, 0, 5);
    }
}
