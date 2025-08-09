@extends('layout.layout')
@php
    $title = 'Dashboard';
    $subTitle = 'PDKS';
    $script = "";

    use Carbon\Carbon;
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-xxl-8">
            <div class="row gy-4">

                <div class="col-xxl-6 col-sm-6">
                    <div class="card p-3 shadow-2 radius-8 border input-form-light h-100 bg-gradient-end-1">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">

                                <div class="d-flex align-items-center gap-2">
                                    <span
                                        class="mb-0 w-48-px h-48-px bg-primary-600 flex-shrink-0 text-white d-flex justify-content-center align-items-center rounded-circle h6 mb-0">
                                        <iconify-icon icon="mingcute:user-follow-fill" class="icon"></iconify-icon>
                                    </span>
                                    <div>
                                        <span class="mb-2 fw-medium text-secondary-light text-sm">Toplam Personel</span>
                                        <h6 class="fw-semibold">{{ $totalUsers }}</h6>
                                    </div>
                                </div>

                                <div id="new-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                            </div>
                            <p class="text-sm mb-0">Aktif personel oranı <span
                                    class="bg-success-focus px-1 rounded-2 fw-medium text-success-main text-sm">{{ $totalUsers > 0 ? number_format(($activeUsers / $totalUsers) * 100, 1) : 0 }}%</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-6 col-sm-6">
                    <div class="card p-3 shadow-2 radius-8 border input-form-light h-100 bg-gradient-end-2">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">

                                <div class="d-flex align-items-center gap-2">
                                    <span
                                        class="mb-0 w-48-px h-48-px bg-success-main flex-shrink-0 text-white d-flex justify-content-center align-items-center rounded-circle h6">
                                        <iconify-icon icon="mingcute:user-follow-fill" class="icon"></iconify-icon>
                                    </span>
                                    <div>
                                        <span class="mb-2 fw-medium text-secondary-light text-sm">Bugün Gelen</span>
                                        <h6 class="fw-semibold">{{ $presentToday }}</h6>
                                    </div>
                                </div>

                                <div id="active-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                            </div>
                            <p class="text-sm mb-0">Devam oranı <span
                                    class="bg-success-focus px-1 rounded-2 fw-medium text-success-main text-sm">{{ $activeUsers > 0 ? number_format(($presentToday / $activeUsers) * 100, 1) : 0 }}%</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-6 col-sm-6">
                    <div class="card p-3 shadow-2 radius-8 border input-form-light h-100 bg-gradient-end-3">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">

                                <div class="d-flex align-items-center gap-2">
                                    <span
                                        class="mb-0 w-48-px h-48-px bg-yellow text-white flex-shrink-0 d-flex justify-content-center align-items-center rounded-circle h6">
                                        <iconify-icon icon="iconamoon:discount-fill" class="icon"></iconify-icon>
                                    </span>
                                    <div>
                                        <span class="mb-2 fw-medium text-secondary-light text-sm">Bugün Gelmeyen</span>
                                        <h6 class="fw-semibold">{{ $absentToday }}</h6>
                                    </div>
                                </div>

                                <div id="total-sales-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                            </div>
                            <p class="text-sm mb-0">Devamsızlık oranı <span
                                    class="bg-danger-focus px-1 rounded-2 fw-medium text-danger-main text-sm">{{ $activeUsers > 0 ? number_format(($absentToday / $activeUsers) * 100, 1) : 0 }}%</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-6 col-sm-6">
                    <div class="card p-3 shadow-2 radius-8 border input-form-light h-100 bg-gradient-end-4">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">

                                <div class="d-flex align-items-center gap-2">
                                    <span
                                        class="mb-0 w-48-px h-48-px bg-purple text-white flex-shrink-0 d-flex justify-content-center align-items-center rounded-circle h6">
                                        <iconify-icon icon="mdi:watch-time" class="icon"></iconify-icon>
                                    </span>
                                    <div>
                                        <span class="mb-2 fw-medium text-secondary-light text-sm">Geç Gelenler</span>
                                        <h6 class="fw-semibold">{{ count($lateArrivals) }}</h6>
                                    </div>
                                </div>

                                <div id="conversion-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                            </div>
                            <p class="text-sm mb-0">Geç gelme oranı <span
                                    class="bg-warning-focus px-1 rounded-2 fw-medium text-warning-main text-sm">{{ $presentToday > 0 ? number_format((count($lateArrivals) / $presentToday) * 100, 1) : 0 }}%</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top performance Start -->
        <div class="col-xxl-4">
            <div class="card">

                <div class="card-body">
                    <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
                        <h6 class="mb-2 fw-bold text-lg mb-0">Son Geç Gelen Personeller</h6>
                        <a href="javascript:void(0)"
                            class="text-primary-600 hover-text-primary d-flex align-items-center gap-1">
                            Tümünü Gör
                            <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                        </a>
                    </div>

                    <div class="mt-32">
                        @foreach ($lateArrivals as $index => $late)
                            @if ($index < 6)
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-32">
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ $late->user->name }}+{{ $late->user->surname }}&background=random"
                                            alt=""
                                            class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                        <div class="flex-grow-1">
                                            <h6 class="text-md mb-0">{{ $late->user->name }} {{ $late->user->surname }}
                                            </h6>
                                            <span
                                                class="text-sm text-secondary-light fw-medium">{{ $late->user->department->title ?? 'Bilinmiyor' }}</span>
                                        </div>
                                    </div>
                                    <span class="text-primary-light text-md fw-medium">
                                        @php
                                            // transaction_date string olabilir, Carbon olduğundan emin olalım
                                            $transactionDate = $late->transaction_date;
                                            if (!($transactionDate instanceof Carbon)) {
                                                $transactionDate = Carbon::parse($transactionDate);
                                            }
                                            echo $transactionDate->format('H:i');
                                        @endphp
                                    </span>
                                </div>
                            @endif
                        @endforeach

                        @if (count($lateArrivals) == 0)
                            <div class="alert alert-info">
                                Bugün geç gelen personel bulunmamaktadır.
                            </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>
        <!-- Top performance End -->

        <!-- Latest Performance Start -->
        <div class="col-xxl-6">
            <div class="card h-100">
                <div
                    class="card-header border-bottom bg-base ps-0 py-0 pe-24 d-flex align-items-center justify-content-between">
                    <ul class="nav bordered-tab nav-pills mb-0" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-to-do-list-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-to-do-list" type="button" role="tab"
                                aria-controls="pills-to-do-list" aria-selected="true">Personel Giriş/Çıkış
                                Durumları</button>
                        </li>
                    </ul>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-primary filter-btn active"
                            data-filter="all">Tümü</button>
                        <button type="button" class="btn btn-sm btn-success filter-btn"
                            data-filter="present">Mevcut</button>
                        <button type="button" class="btn btn-sm btn-warning filter-btn" data-filter="late">Geç
                            Gelen</button>
                        <button type="button" class="btn btn-sm btn-info filter-btn" data-filter="checked_out">Çıkış
                            Yapmış</button>
                        <button type="button" class="btn btn-sm btn-danger filter-btn"
                            data-filter="absent">Gelmeyen</button>
                    </div>
                </div>
                <div class="card-body p-24">
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Personel</th>
                                    <th scope="col">Departman</th>
                                    <th scope="col">Giriş Saati</th>
                                    <th scope="col">Çıkış Saati</th>
                                    <th scope="col">Çalışma Süresi</th>
                                    <th scope="col">Mevcut Konum</th>
                                    <th scope="col">Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($userStatuses as $index => $status)
                                    <tr class="status-row" data-status="{{ $status['status'] }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name={{ $status['user']->name }}+{{ $status['user']->surname }}&background=random"
                                                    alt=""
                                                    class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                                <div class="flex-grow-1">
                                                    <h6 class="text-md mb-0">{{ $status['user']->name }}
                                                        {{ $status['user']->surname }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $status['user']->department->title ?? 'Belirsiz' }}</td>
                                        <td>{{ $status['check_in_time'] ? $status['check_in_time']->format('H:i') : '-' }}
                                        </td>
                                        <td>{{ $status['check_out_time'] ? $status['check_out_time']->format('H:i') : '-' }}
                                        </td>
                                        <td>
                                            @if ($status['work_duration'] !== null)
                                                @php
                                                    $hours = floor($status['work_duration'] / 60);
                                                    $minutes = $status['work_duration'] % 60;
                                                    echo sprintf('%02d:%02d', $hours, $minutes);
                                                @endphp
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $status['current_location'] ?? '-' }}</td>
                                        <td>
                                            @if ($status['status'] == 'present')
                                                <span
                                                    class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Mevcut</span>
                                            @elseif($status['status'] == 'late')
                                                <span
                                                    class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Geç
                                                    Geldi</span>
                                            @elseif($status['status'] == 'checked_out')
                                                <span
                                                    class="bg-info-focus text-info-main px-24 py-4 rounded-pill fw-medium text-sm">Çıkış
                                                    Yaptı</span>
                                            @elseif($status['status'] == 'late_checked_out')
                                                <span
                                                    class="bg-secondary-focus text-secondary-main px-24 py-4 rounded-pill fw-medium text-sm">Geç
                                                    Geldi, Çıkış Yaptı</span>
                                            @elseif($status['status'] == 'absent')
                                                <span
                                                    class="bg-danger-focus text-danger-main px-24 py-4 rounded-pill fw-medium text-sm">Gelmedi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            Giriş/Çıkış kaydı bulunamadı.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-6">
            <div class="card h-100">
                <div
                    class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                    <h6 class="text-lg fw-semibold mb-0">Son Giriş/Çıkış İşlemleri</h6>
                    <a href="javascript:void(0)"
                        class="text-primary-600 hover-text-primary d-flex align-items-center gap-1">
                        Tüm İşlemler
                        <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                    </a>
                </div>
                <div class="card-body p-24">
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Personel</th>
                                    <th scope="col">İşlem</th>
                                    <th scope="col">Lokasyon</th>
                                    <th scope="col">Tarih/Saat</th>
                                    <th scope="col">Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentTransactions as $transaction)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name={{ $transaction->user?->name }}+{{ $transaction->user?->surname }}&background=random"
                                                    alt=""
                                                    class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                                <div class="flex-grow-1">
                                                    <h6 class="text-md mb-0">{{ $transaction->user?->name }}
                                                        {{ $transaction->user?->surname }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $transaction->followType?->title }}</td>
                                        <td>{{ $transaction->branch?->title }}</td>
                                        <td>{{ Carbon::parse($transaction->transaction_date)->format('d.m.Y H:i') }}</td>
                                        <td>
                                            <span class="bg-info-focus text-info-main px-24 py-4 rounded-pill fw-medium text-sm">
                                                {{ $transaction->followType?->title }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            İşlem bulunamadı.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-6">
            <div class="card h-100 radius-8 border-0">
                <div
                    class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                    <h6 class="text-lg fw-semibold mb-0">En Uzun Çalışan Personeller</h6>
                </div>
                <div class="card-body p-24">
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Personel</th>
                                    <th scope="col">Çalışma Süresi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($longestHours as $record)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name={{ $record['user']->name }}+{{ $record['user']->surname }}&background=random"
                                                    alt=""
                                                    class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                                <div class="flex-grow-1">
                                                    <h6 class="text-md mb-0">{{ $record['user']->name }}
                                                        {{ $record['user']->surname }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $record['formatted_duration'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            Personel bulunamadı.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-6">
            <div class="card h-100 radius-8 border-0">
                <div
                    class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                    <h6 class="text-lg fw-semibold mb-0">Lokasyon Durumları</h6>
                    <a href="javascript:void(0)"
                        class="text-primary-600 hover-text-primary d-flex align-items-center gap-1">
                        Detaylı Görünüm
                        <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                    </a>
                </div>
                <div class="card-body p-24">
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Lokasyon</th>
                                    <th scope="col">Mevcut Personel</th>
                                    <th scope="col">Doluluk</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branchStats as $branch)
                                    <tr>
                                        <td>{{ $branch->title }}</td>
                                        <td>{{ $branch->present_users }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2 w-100">
                                                <div class="w-100 max-w-66 ms-auto">
                                                    @php
                                                        // Departman bazlı max personel sayısına göre doluluk hesapla
                                                        $departmentUsers = $totalUsers / max(count($branchStats), 1);
                                                        $percentage = $departmentUsers > 0 ? min(round(($branch->present_users / $departmentUsers) * 100), 100) : 0;
                                                    @endphp
                                                    <div class="progress progress-sm rounded-pill" role="progressbar"
                                                        aria-label="Success example" aria-valuenow="{{ $percentage }}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                        <div class="progress-bar bg-primary-600 rounded-pill"
                                                            style="width: {{ $percentage }}%;"></div>
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-secondary-light font-xs fw-semibold">{{ $percentage }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            Lokasyon bilgisi bulunamadı.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-6">
            <div class="card h-100">
                <div
                    class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                    <h6 class="text-lg fw-semibold mb-0">İzin Kullanım Durumu</h6>
                    <a href="javascript:void(0)"
                        class="text-primary-600 hover-text-primary d-flex align-items-center gap-1">
                        Tüm İzinler
                        <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                    </a>
                </div>
                <div class="card-body p-24">
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Personel</th>
                                    <th scope="col">İzin Türü</th>
                                    <th scope="col">Başlangıç</th>
                                    <th scope="col">Bitiş</th>
                                    <th scope="col">Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $izinTurleri = $holidayTypes;
                                    $durumlar = [
                                        [
                                            'value' => 'pending',
                                            'text' => 'Beklemede',
                                            'class' => 'bg-warning-focus text-warning-main',
                                        ],
                                        [
                                            'value' => 'approved',
                                            'text' => 'Onaylandı',
                                            'class' => 'bg-success-focus text-success-main',
                                        ],
                                        [
                                            'value' => 'rejected',
                                            'text' => 'Reddedildi',
                                            'class' => 'bg-danger-focus text-danger-main',
                                        ],
                                    ];
                                @endphp

                                @forelse ($recentTransactions as $index => $transaction)
                                    @if ($index < 5)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://ui-avatars.com/api/?name={{ $transaction->user?->name }}+{{ $transaction->user?->surname }}&background=random"
                                                        alt=""
                                                        class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                                    <div class="flex-grow-1">
                                                            <h6 class="text-md mb-0">{{ $transaction->user?->name }}
                                                            {{ $transaction->user?->surname }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $transaction->holidayType?->title }}</td>
                                            <td>{{ Carbon::parse($transaction->start_date)->format('d.m.Y') }}</td>
                                            <td>{{ Carbon::parse($transaction->end_date)->format('d.m.Y') }}</td>
                                            <td>
                                                @php $durum = $durumlar[array_rand($durumlar)]; @endphp
                                                <span
                                                    class="{{ $durum['class'] }} px-24 py-4 rounded-pill fw-medium text-sm">{{ $durum['text'] }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            İzin bilgisi bulunamadı.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-6">
            <div class="card h-100">
                <div
                    class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                    <h6 class="text-lg fw-semibold mb-0">Vardiya Optimizasyon Raporu</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary active"
                            id="shift-view-daily">Günlük</button>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            id="shift-view-weekly">Haftalık</button>
                    </div>
                </div>
                <div class="card-body p-24">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card p-3 shadow-1 radius-8 border h-100 bg-primary-50">
                                <div class="card-body">
                                    <h5 class="fw-semibold mb-2">Personel Dağılımı</h5>
                                    <div class="d-flex align-items-center">
                                        @php
                                            // Toplam vardiya sayısı
                                            $totalShifts = count($upcomingShifts);
                                            // Normal vardiya sayısı (5-20 personel arası)
                                            $normalShifts = $upcomingShifts
                                                ->filter(function ($shift) {
                                                    return $shift->total_personnel >= 5 &&
                                                        $shift->total_personnel <= 20;
                                                })
                                                ->count();

                                            // Dağılım oranı
                                            $distributionPercentage =
                                                $totalShifts > 0 ? round(($normalShifts / $totalShifts) * 100) : 0;
                                        @endphp
                                        <div class="w-75 progress progress-lg">
                                            <div class="progress-bar bg-primary-600" role="progressbar"
                                                style="width: {{ $distributionPercentage }}%"
                                                aria-valuenow="{{ $distributionPercentage }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                        <span class="ms-3 fw-bold">{{ $distributionPercentage }}%</span>
                                    </div>
                                    <p class="mt-2 text-sm text-secondary-light">Vardiyalara göre personel dağılım oranı
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card p-3 shadow-1 radius-8 border h-100 bg-warning-50">
                                <div class="card-body">
                                    <h5 class="fw-semibold mb-2">Personel Eksik Vardiyalar</h5>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="fw-bold mb-0">
                                                {{ isset($eksikVardiyaSayisi) ? $eksikVardiyaSayisi : 0 }}</h3>
                                            <p class="mt-2 text-sm text-secondary-light">Yetersiz personel sayısı olan
                                                vardiyalar
                                            </p>
                                        </div>
                                        <span class="text-warning-main text-3xl">
                                            <iconify-icon icon="mdi:alert-circle-outline"></iconify-icon>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="daily-shifts-view">
                        <div class="table-responsive scroll-sm">
                            <table class="table bordered-table mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Vardiya Adı</th>
                                        <th scope="col">Başlangıç</th>
                                        <th scope="col">Bitiş</th>
                                        <th scope="col">Personel Sayısı</th>
                                        <th scope="col">Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($upcomingShifts) && count($upcomingShifts) > 0)
                                        @foreach ($upcomingShifts as $index => $shift)
                                            @if ($index < 5)
                                                @php
                                                    // Personel sayısını hesapla
                                                    $shiftPersonelCount = $shift->total_personnel;

                                                    // Vardiya kapasitesine göre değerlendir (5-20 arası ideal)
                                                    $idealMin = 5;
                                                    $idealMax = 20;

                                                    if ($shiftPersonelCount < $idealMin) {
                                                        $statusClass = 'bg-danger-focus text-danger-main';
                                                        $statusText = 'Yetersiz';
                                                    } elseif ($shiftPersonelCount <= $idealMax) {
                                                        $statusClass = 'bg-success-focus text-success-main';
                                                        $statusText = 'Normal';
                                                    } else {
                                                        $statusClass = 'bg-warning-focus text-warning-main';
                                                        $statusText = 'Yoğun';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>{{ $shift->title ?? ($shift->name ?? 'İsimsiz Vardiya') }}</td>
                                                    <td>{{ isset($shift->start_time) ? Carbon::parse($shift->start_time)->format('H:i') : '-' }}
                                                    </td>
                                                    <td>{{ isset($shift->end_time) ? Carbon::parse($shift->end_time)->format('H:i') : '-' }}
                                                    </td>
                                                    <td>{{ $shiftPersonelCount }}</td>
                                                    <td><span
                                                            class="{{ $statusClass }} px-24 py-4 rounded-pill fw-medium text-sm">{{ $statusText }}</span>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center">Vardiya bilgisi bulunamadı</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="weekly-shifts-view" style="display:none;">
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-center">
                                <iconify-icon icon="mdi:information" class="me-2 text-xl"></iconify-icon>
                                <span>Haftalık vardiya görünümü gösteriliyor. Toplam {{ $upcomingShifts->count() }} vardiya
                                    bulunmaktadır.</span>
                            </div>
                        </div>
                        <div id="weekly-shift-chart" style="height: 300px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // İstatistik grafikleri
            if (typeof ApexCharts !== 'undefined') {
                // Küçük grafik oluşturma fonksiyonu
                function createSmallChart(chartId, chartColor) {
                    if (!document.querySelector(`#${chartId}`)) return;

                    var options = {
                        series: [{
                            name: 'Veri',
                            data: [35, 45, 38, 41, 36, 43, 37, 55, 40]
                        }],
                        chart: {
                            type: 'area',
                            width: 80,
                            height: 42,
                            sparkline: {
                                enabled: true
                            },
                            toolbar: {
                                show: false
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2,
                            colors: [chartColor],
                            lineCap: 'round'
                        },
                        fill: {
                            type: 'gradient',
                            colors: [chartColor],
                            gradient: {
                                shade: 'light',
                                type: 'vertical',
                                shadeIntensity: 0.5,
                                gradientToColors: [`${chartColor}00`],
                                inverseColors: false,
                                opacityFrom: .75,
                                opacityTo: 0.3,
                                stops: [0, 100]
                            }
                        },
                        markers: {
                            colors: [chartColor],
                            strokeWidth: 2,
                            size: 0,
                            hover: {
                                size: 8
                            }
                        },
                        xaxis: {
                            labels: {
                                show: false
                            }
                        },
                        yaxis: {
                            labels: {
                                show: false
                            }
                        },
                        tooltip: {
                            enabled: false
                        }
                    };

                    try {
                        var chart = new ApexCharts(document.querySelector(`#${chartId}`), options);
                        chart.render();
                    } catch (error) {
                        console.log(`Grafik yüklenemedi: ${chartId}`, error);
                    }
                }

                // Tüm küçük grafikleri oluştur
                setTimeout(function() {
                    if (document.querySelector('#new-user-chart')) createSmallChart('new-user-chart', '#487fff');
                    if (document.querySelector('#active-user-chart')) createSmallChart('active-user-chart', '#45b369');
                    if (document.querySelector('#total-sales-chart')) createSmallChart('total-sales-chart', '#f4941e');
                    if (document.querySelector('#conversion-user-chart')) createSmallChart('conversion-user-chart', '#8252e9');
                }, 500);
            }

            // Personel durumu filtre işlemleri
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');

                var filter = $(this).data('filter');

                if (filter === 'all') {
                    $('.status-row').show();
                } else {
                    $('.status-row').hide();
                    $('.status-row[data-status="' + filter + '"]').show();

                    // Geç geldi, çıkış yaptı durumunu da göster
                    if (filter === 'late') {
                        $('.status-row[data-status="late_checked_out"]').show();
                    }

                    if (filter === 'checked_out') {
                        $('.status-row[data-status="late_checked_out"]').show();
                    }
                }
            });

            // Vardiya görünümleri arasında geçiş
            $('#shift-view-daily').click(function() {
                $(this).addClass('active').siblings().removeClass('active');
                $('#daily-shifts-view').show();
                $('#weekly-shifts-view').hide();
            });

            $('#shift-view-weekly').click(function() {
                $(this).addClass('active').siblings().removeClass('active');
                $('#daily-shifts-view').hide();
                $('#weekly-shifts-view').show();

                // Grafik henüz oluşturulmadıysa oluştur
                if (typeof weeklyShiftChart === 'undefined' &&
                    typeof ApexCharts !== 'undefined' &&
                    $('#weekly-shift-chart').length > 0) {
                    renderWeeklyShiftChart();
                }
            });

            // Haftalık vardiya grafiği
            function renderWeeklyShiftChart() {
                if (typeof ApexCharts !== 'undefined' && $('#weekly-shift-chart').length > 0) {
                    var weeklyShiftOptions = {
                        series: [{
                            name: 'Personel Sayısı',
                            data: [
                                @foreach ($upcomingShifts as $shift)
                                    {{ $shift->total_personnel }},
                                @endforeach
                            ]
                        }],
                        chart: {
                            type: 'bar',
                            height: 300,
                            toolbar: {
                                show: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '40%',
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        colors: ['#487FFF'],
                        grid: {
                            borderColor: '#e0e0e0',
                            strokeDashArray: 4,
                        },
                        xaxis: {
                            categories: [
                                @foreach ($upcomingShifts as $shift)
                                    '{{ $shift->title ?? 'Vardiya' }}',
                                @endforeach
                            ]
                        },
                        yaxis: {
                            title: {
                                text: 'Personel Sayısı'
                            }
                        }
                    };

                    try {
                        window.weeklyShiftChart = new ApexCharts(document.querySelector("#weekly-shift-chart"),
                            weeklyShiftOptions);
                        window.weeklyShiftChart.render();
                    } catch (error) {
                        console.log("Haftalık vardiya grafiği yüklenemedi", error);
                    }
                }
            }
        });
    </script>
@endsection
