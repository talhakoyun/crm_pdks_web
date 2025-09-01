@extends('layout.layout')
@php
    $title = $container->title;
    $subTitle = $container->title . ' Listesi';
@endphp

@section('style')
<style>
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
</style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $subTitle }}</h5>
            <a href="{{ route('backend.' . $container->page . '_list') }}"
                class="btn btn-primary btn-sm rounded-pill waves-effect waves-themed d-flex align-items-center">
                Kayıtları Görüntüle
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('backend.' . $container->page . '_save_bulk') }}" method="POST">
                @csrf
                <div class="row mb-5">
                    <div class="col-md-12">
                        <h6>Hızlı Toplu İşlem</h6>
                        <p class="text-muted small mb-3">Seçili personeller için aynı tarih aralığında geçici vardiya ataması yapabilirsiniz.</p>
                        @if(isset($users))
                            <div class="alert alert-info">
                                <i class="ri-information-line"></i> Toplam {{ $users->count() }} personel listelendi.
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="ri-alert-line"></i> Kullanıcı verisi yüklenemedi.
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Geçici Vardiya</label>
                        <select id="global-shift" class="form-select select2">
                            <option value="">Geçici Vardiya Seçiniz</option>
                            @foreach ($shiftDefinitions as $shift)
                                <option value="{{ $shift->id }}"
                                        data-schedule="{{ json_encode($shift->getWeeklySchedule()) }}"
                                        data-hours="{{ number_format($shift->getWeeklyWorkingHours(), 1) }}">
                                    {{ $shift->title }} ({{ number_format($shift->getWeeklyWorkingHours(), 1) }} saat/hafta)
                                </option>
                            @endforeach
                        </select>
                        <div id="global-shift-preview" class="mt-2" style="display: none;">
                            <small class="text-info">
                                <i class="ri-information-line"></i>
                                <span class="preview-text"></span>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Geçici Değişiklik Tarihi</label>
                        <div class="input-group">
                            <input type="date" id="global-start-date" class="form-control">
                            <span class="input-group-text">-</span>
                            <input type="date" id="global-end-date" class="form-control">
                        </div>
                        <div id="global-affected-days" class="mt-2">
                            <small class="text-muted">Tarih aralığı seçin</small>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="w-100">
                            <button type="button" id="apply-to-selected" class="btn btn-primary w-100 mb-2">
                                <i class="ri-check-line"></i> Seçilenlere Uygula ve Kaydet
                            </button>
                            <small class="text-muted d-block text-center">
                                <span id="selected-count">0</span> personel seçili
                            </small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                    </div>
                                </th>
                                <th>Personel Adı</th>
                                <th>Mevcut Vardiya</th>
                                <th>Tarih Aralığı</th>
                                <th>Geçici Vardiya</th>
                                <th>Etkilenen Günler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($users) && $users->count() > 0)
                                @foreach ($users as $user)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input user-checkbox" type="checkbox" name="selected_users[]" value="{{ $user->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-bold">{{ $user->name }} {{ $user->surname }}</div>
                                                <small class="text-muted">{{ $user->department->title ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $currentShift = $user->userShift?->shiftDefinition ?? null;
                                        @endphp
                                        @if($currentShift)
                                            <div class="current-shift-info">
                                                <strong class="text-primary">{{ $currentShift->title }}</strong>
                                                <div class="mt-1">
                                                    @php
                                                        $workingDays = $currentShift->getWorkingDays();
                                                        $dayNames = [
                                                            'monday' => 'Pzt',
                                                            'tuesday' => 'Sal',
                                                            'wednesday' => 'Çar',
                                                            'thursday' => 'Per',
                                                            'friday' => 'Cum',
                                                            'saturday' => 'Cmt',
                                                            'sunday' => 'Paz'
                                                        ];
                                                        $displayDays = array_map(function($day) use ($dayNames) {
                                                            return $dayNames[$day] ?? $day;
                                                        }, $workingDays);
                                                    @endphp
                                                    <small class="badge bg-info">{{ implode(', ', $displayDays) }}</small>
                                                    <small class="badge bg-secondary">{{ number_format($currentShift->getWeeklyWorkingHours(), 1) }} saat/hafta</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-warning">Vardiya Atanmamış</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="input-group">
                                                <input type="date" class="form-control start-date" name="start_date[{{ $user->id }}]" onchange="updateAffectedDays({{ $user->id }})">
                                                <span class="input-group-text">-</span>
                                                <input type="date" class="form-control end-date" name="end_date[{{ $user->id }}]" onchange="updateAffectedDays({{ $user->id }})">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select shift-select" name="shift_definition_id[{{ $user->id }}]" onchange="updateShiftPreview({{ $user->id }})">
                                            <option value="">Geçici Vardiya Seçiniz</option>
                                            @foreach ($shiftDefinitions as $shift)
                                                <option value="{{ $shift->id }}"
                                                        data-schedule="{{ json_encode($shift->getWeeklySchedule()) }}"
                                                        data-hours="{{ number_format($shift->getWeeklyWorkingHours(), 1) }}">
                                                    {{ $shift->title }} ({{ number_format($shift->getWeeklyWorkingHours(), 1) }} saat/hafta)
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="mt-2 shift-preview-{{ $user->id }}" style="display: none;">
                                            <small class="text-info">
                                                <i class="ri-information-line"></i>
                                                <span class="preview-text"></span>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="affected-days-{{ $user->id }}" style="min-width: 150px;">
                                            <small class="text-muted">Tarih seçin</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-user-unfollow-line fs-1"></i>
                                            <p class="mt-2">Personel bulunamadı veya yetkiniz bulunmamaktadır.</p>
                                            <small>Role ID 1, 2, 3 olan kullanıcılar (Süper Admin, Admin, Şirket Sahibi) listede görünmez.</small>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button type="button" id="save-changes" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Başlangıç tarihi seçildiğinde bitiş tarihini de aynı tarih yap
        $(".start-date").on("change", function() {
            var selectedDate = $(this).val();
            var endDateInput = $(this).closest('tr').find('.end-date');
            if (selectedDate) {
                endDateInput.val(selectedDate);
            }
        });

        // Global başlangıç tarihi seçildiğinde global bitiş tarihini de aynı tarih yap
        $("#global-start-date").on("change", function() {
            var selectedDate = $(this).val();
            var endDateInput = $("#global-end-date");

            if (selectedDate) {
                endDateInput.val(selectedDate);
            }
        });

        // Seçili kullanıcı sayısını güncelle
        function updateSelectedCount() {
            const selectedCount = $(".user-checkbox:checked").length;
            $("#selected-count").text(selectedCount);
        }

        // Tümünü seç/kaldır
        $("#select-all").on("change", function() {
            $(".user-checkbox").prop("checked", $(this).prop("checked"));
            updateSelectedCount();
        });

        // Kullanıcı checkbox değişiminde sayıyı güncelle
        $(document).on("change", ".user-checkbox", function() {
            updateSelectedCount();
        });

        // Global vardiya seçiminde önizleme göster
        $("#global-shift").on("change", function() {
            const selectedOption = $(this).find('option:selected');
            const previewContainer = $("#global-shift-preview");

            if (!selectedOption.val()) {
                previewContainer.hide();
                return;
            }

            const scheduleData = selectedOption.data('schedule');
            const hours = selectedOption.data('hours');

            if (scheduleData) {
                const workingDays = [];
                Object.keys(scheduleData).forEach(function(day) {
                    const dayData = scheduleData[day];
                    if (dayData.is_working_day) {
                        const dayNames = {
                            'monday': 'Pzt',
                            'tuesday': 'Sal',
                            'wednesday': 'Çar',
                            'thursday': 'Per',
                            'friday': 'Cum',
                            'saturday': 'Cmt',
                            'sunday': 'Paz'
                        };
                        workingDays.push(`${dayNames[day]}: ${dayData.start}-${dayData.end}`);
                    }
                });

                const previewText = `${workingDays.join(', ')} (${hours} saat/hafta)`;
                previewContainer.find('.preview-text').text(previewText);
                previewContainer.show();
            } else {
                previewContainer.hide();
            }
        });

        // Global tarih değişiminde etkilenen günleri göster
        function updateGlobalAffectedDays() {
            const startDate = $("#global-start-date").val();
            const endDate = $("#global-end-date").val();
            const affectedDaysContainer = $("#global-affected-days");

            if (!startDate || !endDate) {
                affectedDaysContainer.html('<small class="text-muted">Tarih aralığı seçin</small>');
                return;
            }

            const start = new Date(startDate);
            const end = new Date(endDate);

            if (start > end) {
                affectedDaysContainer.html('<small class="text-danger">Geçersiz tarih aralığı</small>');
                return;
            }

            // Gün sayısını hesapla
            const timeDiff = end.getTime() - start.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;

            // Günleri listele
            const dayNames = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];
            let daysHtml = `<div class="mb-1"><strong>${daysDiff} gün etkilenecek:</strong></div>`;

            const currentDate = new Date(start);
            const days = [];

            while (currentDate <= end) {
                const dayName = dayNames[currentDate.getDay()];
                const dateStr = currentDate.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit' });
                days.push(`${dayName} ${dateStr}`);
                currentDate.setDate(currentDate.getDate() + 1);
            }

            // Her 4 günü bir satırda göster
            for (let i = 0; i < days.length; i += 4) {
                const chunk = days.slice(i, i + 4);
                daysHtml += `<div class="mb-1">`;
                chunk.forEach(day => {
                    daysHtml += `<span class="badge bg-warning-subtle text-warning me-1 mb-1">${day}</span>`;
                });
                daysHtml += `</div>`;
            }

            affectedDaysContainer.html(daysHtml);
        }

        $("#global-start-date, #global-end-date").on("change", updateGlobalAffectedDays);

        // Seçilenlere uygula butonu
        $("#apply-to-selected").on("click", function() {
            var shiftId = $("#global-shift").val();
            var startDate = $("#global-start-date").val();
            var endDate = $("#global-end-date").val();
            var selectedCount = $(".user-checkbox:checked").length;

            if (selectedCount === 0) {
                Swal.fire('Uyarı', 'Lütfen en az bir personel seçiniz', 'warning');
                return;
            }

            if (!shiftId || !startDate || !endDate) {
                Swal.fire('Uyarı', 'Lütfen geçici vardiya ve tarih aralığı seçiniz', 'warning');
                return;
            }

            // Onay modalı göster
            Swal.fire({
                title: 'Toplu İşlem Onayı',
                html: `<strong>${selectedCount}</strong> personel için<br>
                       <strong>${startDate}</strong> - <strong>${endDate}</strong> tarihleri arasında<br>
                       geçici vardiya ataması yapılacak.<br><br>
                       Bu işlem mevcut vardiyalarını geçici olarak değiştirecek.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Evet, Uygula',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(".user-checkbox:checked").each(function() {
                        var userId = $(this).val();
                        var row = $(this).closest('tr');

                        // Tarihleri ayarla
                        row.find('.start-date').val(startDate).trigger('change');
                        row.find('.end-date').val(endDate).trigger('change');

                        // Vardiyayı ayarla
                        row.find('.shift-select').val(shiftId).trigger('change');

                        // Etkilenen günleri güncelle
                        updateAffectedDays(userId);
                        updateShiftPreview(userId);
                    });

                    // Alanları doldurduktan sonra direkt kaydet
                    saveSelectedUsers();
                }
            });
        });

        // Seçili kullanıcıları direkt kaydet
        function saveSelectedUsers() {
            // Sadece seçili kullanıcıların verilerini topla
            var formData = [];
            var hasData = false;

            $(".user-checkbox:checked").each(function() {
                var userId = $(this).val();
                var row = $(this).closest('tr');
                var startDate = row.find('.start-date').val();
                var endDate = row.find('.end-date').val();
                var shiftId = row.find('.shift-select').val();

                if (startDate && endDate && shiftId) {
                    formData.push({
                        name: `start_date[${userId}]`,
                        value: startDate
                    });
                    formData.push({
                        name: `end_date[${userId}]`,
                        value: endDate
                    });
                    formData.push({
                        name: `shift_definition_id[${userId}]`,
                        value: shiftId
                    });
                    hasData = true;
                }
            });

            if (!hasData) {
                Swal.fire('Uyarı', 'Kaydedilecek veri bulunamadı.', 'warning');
                return;
            }

            // CSRF token ekle
            formData.push({
                name: '_token',
                value: '{{ csrf_token() }}'
            });

            // AJAX ile kaydet
            $.ajax({
                url: $("form").attr('action'),
                type: 'POST',
                data: $.param(formData),
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Lütfen bekleyin...',
                        html: 'Geçici vardiya atamaları kaydediliyor',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı!',
                            text: response.message,
                            confirmButtonText: 'Tamam'
                        }).then(() => {
                            // Başarılı kayıtları temizle
                            if (response.saved_users && response.saved_users.length > 0) {
                                $.each(response.saved_users, function(index, userId) {
                                    var row = $(`.user-checkbox[value="${userId}"]`).closest('tr');
                                    row.find('.start-date').val('');
                                    row.find('.end-date').val('');
                                    row.find('.shift-select').val('');
                                    row.find('.affected-days-' + userId).html('<small class="text-muted">Tarih seçin</small>');
                                    row.find('.shift-preview-' + userId).hide();

                                    // Checkbox'ı kaldır
                                    $(`.user-checkbox[value="${userId}"]`).prop('checked', false);

                                    // Başarı efekti
                                    row.addClass('bg-success-subtle').delay(3000).queue(function(){
                                        $(this).removeClass('bg-success-subtle').dequeue();
                                    });
                                });
                                updateSelectedCount();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata!',
                            text: response.message,
                            confirmButtonText: 'Tamam'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sistem Hatası!',
                        text: 'Bir hata oluştu: ' + error,
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        }

        // Etkilenen günleri güncelle
        window.updateAffectedDays = function(userId) {
            const startDate = $(`input[name="start_date[${userId}]"]`).val();
            const endDate = $(`input[name="end_date[${userId}]"]`).val();
            const affectedDaysContainer = $(`.affected-days-${userId}`);

            if (!startDate || !endDate) {
                affectedDaysContainer.html('<small class="text-muted">Tarih seçin</small>');
                return;
            }

            const start = new Date(startDate);
            const end = new Date(endDate);

            if (start > end) {
                affectedDaysContainer.html('<small class="text-danger">Geçersiz tarih aralığı</small>');
                return;
            }

            // Gün sayısını hesapla
            const timeDiff = end.getTime() - start.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;

            // Günleri listele
            const dayNames = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];
            let daysHtml = `<div class="mb-1"><strong>${daysDiff} gün:</strong></div>`;

            const currentDate = new Date(start);
            const days = [];

            while (currentDate <= end) {
                const dayName = dayNames[currentDate.getDay()];
                const dateStr = currentDate.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit' });
                days.push(`${dayName} ${dateStr}`);
                currentDate.setDate(currentDate.getDate() + 1);
            }

            // Her 3 günü bir satırda göster
            for (let i = 0; i < days.length; i += 3) {
                const chunk = days.slice(i, i + 3);
                daysHtml += `<div class="mb-1">`;
                chunk.forEach(day => {
                    daysHtml += `<span class="badge bg-primary-subtle text-primary me-1 mb-1">${day}</span>`;
                });
                daysHtml += `</div>`;
            }

            affectedDaysContainer.html(daysHtml);
        };

        // Vardiya önizlemesini güncelle
        window.updateShiftPreview = function(userId) {
            const shiftSelect = $(`select[name="shift_definition_id[${userId}]"]`);
            const selectedOption = shiftSelect.find('option:selected');
            const previewContainer = $(`.shift-preview-${userId}`);

            if (!selectedOption.val()) {
                previewContainer.hide();
                return;
            }

            const scheduleData = selectedOption.data('schedule');
            const hours = selectedOption.data('hours');

            if (scheduleData) {
                const workingDays = [];
                Object.keys(scheduleData).forEach(function(day) {
                    const dayData = scheduleData[day];
                    if (dayData.is_working_day) {
                        const dayNames = {
                            'monday': 'Pzt',
                            'tuesday': 'Sal',
                            'wednesday': 'Çar',
                            'thursday': 'Per',
                            'friday': 'Cum',
                            'saturday': 'Cmt',
                            'sunday': 'Paz'
                        };
                        workingDays.push(`${dayNames[day]}: ${dayData.start}-${dayData.end}`);
                    }
                });

                const previewText = `${workingDays.join(', ')} (${hours} saat/hafta)`;
                previewContainer.find('.preview-text').text(previewText);
                previewContainer.show();
            } else {
                previewContainer.hide();
            }
        };

        // Kaydet butonu - Form kontrolü ve gönderimi
        $("#save-changes").on("click", function(e) {
            e.preventDefault();

            var hasError = false;
            var errorMessage = '';
            var rowsWithData = 0;

            // Tüm satırları kontrol et
            $("tbody tr").each(function() {
                var row = $(this);
                var startDateInput = row.find('.start-date');
                var endDateInput = row.find('.end-date');
                var shiftSelect = row.find('.shift-select');

                var startDate = startDateInput.val();
                var endDate = endDateInput.val();
                var shiftId = shiftSelect.val();

                // Hata sınıflarını temizle
                startDateInput.removeClass('is-invalid');
                endDateInput.removeClass('is-invalid');
                shiftSelect.removeClass('is-invalid');

                // Eğer başlangıç tarihi girilmişse diğer alanları kontrol et
                if (startDate) {
                    rowsWithData++;

                    // Bitiş tarihi kontrolü
                    if (!endDate) {
                        endDateInput.addClass('is-invalid');
                        hasError = true;
                        errorMessage = 'Başlangıç tarihi seçildiğinde bitiş tarihi de seçilmelidir.';
                    }

                    // Vardiya kontrolü
                    if (!shiftId) {
                        shiftSelect.addClass('is-invalid');
                        hasError = true;
                        errorMessage = 'Başlangıç tarihi seçildiğinde vardiya da seçilmelidir.';
                    }

                    // Tarih sıralaması kontrolü
                    if (endDate && new Date(startDate) > new Date(endDate)) {
                        startDateInput.addClass('is-invalid');
                        endDateInput.addClass('is-invalid');
                        hasError = true;
                        errorMessage = 'Bitiş tarihi başlangıç tarihinden önce olamaz.';
                    }
                }

                // Eğer bitiş tarihi girilmişse diğer alanları kontrol et
                else if (endDate) {
                    rowsWithData++;

                    // Başlangıç tarihi kontrolü
                    if (!startDate) {
                        startDateInput.addClass('is-invalid');
                        hasError = true;
                        errorMessage = 'Bitiş tarihi seçildiğinde başlangıç tarihi de seçilmelidir.';
                    }

                    // Vardiya kontrolü
                    if (!shiftId) {
                        shiftSelect.addClass('is-invalid');
                        hasError = true;
                        errorMessage = 'Bitiş tarihi seçildiğinde vardiya da seçilmelidir.';
                    }
                }
            });

            // Hiç veri girilmemişse uyar
            if (rowsWithData === 0) {
                Swal.fire('Uyarı', 'En az bir personel için tarih aralığı ve vardiya seçmelisiniz.', 'warning');
                return;
            }

            // Hata varsa göster
            if (hasError) {
                Swal.fire('Hata', errorMessage, 'error');
                return;
            }

            // Herşey tamamsa formu AJAX ile gönder
            var formData = $("form").serialize();

            // Yükleniyor göstergesi
            Swal.fire({
                title: 'Lütfen bekleyin...',
                html: 'Veriler kaydediliyor',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: $("form").attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        // Başarılı işlem
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı!',
                            text: response.message,
                            confirmButtonText: 'Tamam'
                        }).then((result) => {
                            // Başarılı kayıtları temizle
                            if (response.saved_users && response.saved_users.length > 0) {
                                $.each(response.saved_users, function(index, userId) {
                                    var row = $("tr").find("input[name='start_date[" + userId + "]']").closest('tr');
                                    row.find('.start-date').val('');
                                    row.find('.end-date').val('');
                                    row.find('.shift-select').val('');
                                    row.addClass('bg-success-subtle').delay(2000).queue(function(){
                                        $(this).removeClass('bg-success-subtle').dequeue();
                                    });
                                });
                            }
                        });
                    } else {
                        // Hata durumu
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata!',
                            text: response.message,
                            confirmButtonText: 'Tamam'
                        });

                        // Hatalı alanları işaretle
                        if (response.errors) {
                            $.each(response.errors, function(userId, errors) {
                                var row = $("tr").find("input[name='start_date[" + userId + "]']").closest('tr');

                                if (errors.start_date) {
                                    row.find('.start-date').addClass('is-invalid');
                                }

                                if (errors.end_date) {
                                    row.find('.end-date').addClass('is-invalid');
                                }

                                if (errors.shift_definition_id) {
                                    row.find('.shift-select').addClass('is-invalid');
                                }

                                row.addClass('bg-danger-subtle').delay(2000).queue(function(){
                                    $(this).removeClass('bg-danger-subtle').dequeue();
                                });
                            });
                        }
                    }
                },
                error: function(xhr, status, error) {
                    // AJAX hatası
                    Swal.fire({
                        icon: 'error',
                        title: 'Sistem Hatası!',
                        text: 'Bir hata oluştu: ' + error,
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        });


    });
</script>
@endsection
