@extends('layout.layout')
@php
    $title = $container->title;
    $subTitle = isset($editShift) ? $editShift->title . ' Vardiyası Düzenle' : 'Vardiya Atama';
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6">{{ $subTitle }}</h5>
                </div>
                <div class="card-body">
                    <!-- Filtreleme Alanı -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <h6 class="mb-3">Kullanıcı Filtreleme</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Şube</label>
                                            <select class="form-select" id="branchFilter">
                                                <option value="">Tüm Şubeler</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Departman</label>
                                            <select class="form-select" id="departmentFilter">
                                                <option value="">Tüm Departmanlar</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Arama</label>
                                            <input type="text" class="form-control" id="searchInput" placeholder="İsim, soyisim veya email ile ara...">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-primary w-100" id="filterUsers">
                                                <i class="ri-search-line"></i> Filtrele
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vardiya Seçimi -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-primary-subtle">
                                <div class="card-body p-3">
                                    <h6 class="mb-3">Atanacak Vardiya</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                                                                        <label class="form-label">Vardiya Seçin <span class="text-danger">*</span></label>
                                            <select class="form-select" id="shiftDefinitionId" required title="Vardiya seçmek için tıklayın">
                                                <option value="">Vardiya Seçiniz</option>
                                                @foreach($shiftDefinitions as $shift)
                                                    @php
                                                        $schedule = $shift->getWeeklySchedule();
                                                        $workingDays = array_filter($schedule, function($day) {
                                                            return $day['is_working_day'];
                                                        });
                                                        $workingDaysCount = count($workingDays);
                                                        $totalHours = $shift->getWeeklyWorkingHours();

                                                        // Tooltip içeriği oluştur
                                                        $tooltipContent = "Haftalık Program:\n";
                                                        foreach($schedule as $day => $info) {
                                                            if($info['is_working_day']) {
                                                                $tooltipContent .= $info['name'] . ": " . $info['start'] . " - " . $info['end'] . "\n";
                                                            } else {
                                                                $tooltipContent .= $info['name'] . ": İzin Günü\n";
                                                            }
                                                        }
                                                        $tooltipContent .= "\nToplam: " . number_format($totalHours, 1) . " saat/hafta";
                                                    @endphp
                                                    <option value="{{ $shift->id }}"
                                                            data-schedule="{{ json_encode($schedule) }}"
                                                            data-hours="{{ number_format($totalHours, 1) }}"
                                                            title="{{ $tooltipContent }}">
                                                        {{ $shift->title }}
                                                        ({{ $workingDaysCount }} gün, {{ number_format($totalHours, 1) }} saat/hafta)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="shiftError"></div>

                                            <!-- Vardiya Detay Bilgisi -->
                                            <div id="shiftDetails" class="mt-3" style="display: none;">
                                                <div class="card border-info">
                                                    <div class="card-header bg-info-subtle">
                                                        <h6 class="mb-0 text-info">
                                                            <i class="ri-time-line me-2"></i>Seçili Vardiya Detayları
                                                        </h6>
                                                    </div>
                                                    <div class="card-body p-3">
                                                        <div class="row" id="weeklyScheduleInfo">
                                                            <!-- JavaScript ile doldurulacak -->
                                                        </div>
                                                        <div class="mt-3 text-center">
                                                            <span class="badge bg-primary fs-6" id="totalHoursInfo">
                                                                <i class="ri-calendar-check-line me-1"></i>
                                                                Toplam: 0 saat/hafta
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Seçili Kullanıcı Sayısı</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="ri-user-line"></i>
                                                </span>
                                                <input type="text" class="form-control" id="selectedCount" value="0" readonly>
                                                <button type="button" class="btn btn-success" id="assignShift" disabled>
                                                    <i class="ri-check-line"></i> Vardiya Ata
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kullanıcı Listesi -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Kullanıcı Listesi</h6>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="selectAll">
                                            <i class="ri-checkbox-multiple-line"></i> Tümünü Seç
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAll">
                                            <i class="ri-checkbox-blank-line"></i> Seçimi Temizle
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="usersList">
                                        <div class="text-center py-4">
                                            <i class="ri-user-search-line fs-1 text-muted"></i>
                                            <p class="text-muted mt-2">Kullanıcıları görmek için filtreleme yapın</p>
                                        </div>
                                    </div>

                                    <!-- Kullanıcı Tablosu -->
                                    <div id="usersTable" style="display: none;">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="50">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" id="selectAllUsers">
                                                                <label class="form-check-label" for="selectAllUsers"></label>
                                                            </div>
                                                        </th>
                                                        <th>Kullanıcı Adı</th>
                                                        <th>Şube</th>
                                                        <th>Departman</th>
                                                        <th>Mevcut Vardiya</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="usersTableBody">
                                                    <!-- JavaScript ile doldurulacak -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
<style>
    /* Vardiya detayları için özel stiller */
    #shiftDetails .card {
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    #shiftDetails .card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .schedule-day {
        transition: all 0.2s ease;
        border-radius: 8px;
        padding: 8px;
    }

    .schedule-day:hover {
        background-color: rgba(13, 110, 253, 0.1);
        transform: translateY(-1px);
    }

    .badge {
        font-size: 0.75em;
        padding: 0.5em 0.75em;
    }

    /* Select option hover efekti */
    #shiftDefinitionId option:hover {
        background-color: #e3f2fd !important;
    }

        /* Tooltip için özel stil */
    .custom-tooltip {
        position: relative;
        cursor: help;
    }

    .custom-tooltip:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        white-space: pre-line;
        z-index: 1000;
        max-width: 300px;
    }

    /* Kullanıcı tablosu stilleri */
    .user-row {
        transition: all 0.2s ease;
    }

    .user-row:hover {
        background-color: rgba(13, 110, 253, 0.05) !important;
    }

    .user-row.table-primary {
        background-color: rgba(13, 110, 253, 0.15) !important;
    }

    .avatar-sm {
        width: 32px;
        height: 32px;
    }

    /* Tablo header stilleri */
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }

    /* Checkbox stilleri */
    .form-check-input:indeterminate {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    /* Badge stilleri */
    .badge {
        font-size: 0.75em;
        font-weight: 500;
    }
</style>
@endsection

@section('script')
<script>
$(document).ready(function() {
    let users = [];
    let selectedUsers = [];

        // Edit mode için önceden seçili kullanıcıları set et
    @if(isset($editShift) && $editShift && !empty($selectedUsers))
        users = @json($selectedUsers);
        selectedUsers = @json(array_column($selectedUsers, 'id'));

        // Vardiyayı önceden seç
        $('#shiftDefinitionId').val({{ $editShift->id }}).trigger('change');

        // Kullanıcıları render et
        renderUsers();
        updateSelectedCount();

        // Form başlığını güncelle
        $('.card-title').first().text('{{ $editShift->title }} Vardiyası Düzenle');
    @endif

    // Kullanıcıları filtrele ve getir
    $('#filterUsers').click(function() {
        loadUsers();
    });

    // Enter tuşu ile arama
    $('#searchInput').keypress(function(e) {
        if (e.which == 13) {
            loadUsers();
        }
    });

    // Kullanıcıları yükle
    function loadUsers() {
        const branchId = $('#branchFilter').val();
        const departmentId = $('#departmentFilter').val();
        const search = $('#searchInput').val();

        $.ajax({
            url: '{{ route("backend.shift_assignment_get_users") }}',
            type: 'GET',
            data: {
                branch_id: branchId,
                department_id: departmentId,
                search: search
            },
            beforeSend: function() {
                $('#usersList').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <p class="text-muted mt-2">Kullanıcılar yükleniyor...</p>
                    </div>
                `);
            },
            success: function(response) {
                if (response.success) {
                    users = response.data;
                    renderUsers();
                } else {
                    showError('Kullanıcılar yüklenirken hata oluştu.');
                }
            },
            error: function() {
                showError('Kullanıcılar yüklenirken hata oluştu.');
            }
        });
    }

        // Kullanıcıları render et
    function renderUsers() {
        if (users.length === 0) {
            $('#usersList').show();
            $('#usersTable').hide();
            $('#usersList').html(`
                <div class="text-center py-4">
                    <i class="ri-user-unfollow-line fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Filtreleme kriterlerine uygun kullanıcı bulunamadı</p>
                </div>
            `);
            return;
        }

        // Tablo formatında render et
        let tableHtml = '';
        users.forEach(function(user) {
            const isSelected = selectedUsers.includes(user.id);
            const shiftBadgeClass = user.current_shift === 'Atanmamış' ? 'bg-warning' : 'bg-success';

            tableHtml += `
                <tr class="user-row ${isSelected ? 'table-primary' : ''}" data-user-id="${user.id}" style="cursor: pointer;">
                    <td>
                        <div class="form-check">
                            <input class="form-check-input user-checkbox" type="checkbox"
                                   value="${user.id}" ${isSelected ? 'checked' : ''}>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                <i class="ri-user-line text-muted"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">${user.name}</h6>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info">${user.branch}</span>
                    </td>
                    <td>
                        <span class="badge bg-secondary">${user.department}</span>
                    </td>
                    <td>
                        <span class="badge ${shiftBadgeClass}">${user.current_shift}</span>
                    </td>
                </tr>
            `;
        });

        $('#usersTableBody').html(tableHtml);
        $('#usersList').hide();
        $('#usersTable').show();
    }

        // Kullanıcı seçimi
    $(document).on('change', '.user-checkbox', function() {
        const userId = parseInt($(this).val());
        const isChecked = $(this).is(':checked');

        if (isChecked) {
            if (!selectedUsers.includes(userId)) {
                selectedUsers.push(userId);
            }
            $(this).closest('tr').addClass('table-primary');
        } else {
            selectedUsers = selectedUsers.filter(id => id !== userId);
            $(this).closest('tr').removeClass('table-primary');
        }

        updateSelectedCount();
        updateSelectAllCheckbox();
    });

    // Tablo satırına tıklama
    $(document).on('click', '.user-row', function(e) {
        if (!$(e.target).hasClass('form-check-input') && !$(e.target).closest('.form-check').length) {
            const checkbox = $(this).find('.user-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });

    // Tümünü seç checkbox'ı
    $(document).on('change', '#selectAllUsers', function() {
        const isChecked = $(this).is(':checked');
        $('.user-checkbox').prop('checked', isChecked).trigger('change');
    });

    // Tümünü seç checkbox'ının durumunu güncelle
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.user-checkbox').length;
        const checkedCheckboxes = $('.user-checkbox:checked').length;

        if (checkedCheckboxes === 0) {
            $('#selectAllUsers').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAllUsers').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAllUsers').prop('indeterminate', true).prop('checked', false);
        }
    }

        // Tümünü seç butonuna tıklama
    $('#selectAll').click(function() {
        $('.user-checkbox').prop('checked', true).trigger('change');
        $('#selectAllUsers').prop('checked', true).prop('indeterminate', false);
    });

    // Seçimi temizle butonuna tıklama
    $('#deselectAll').click(function() {
        $('.user-checkbox').prop('checked', false).trigger('change');
        $('#selectAllUsers').prop('checked', false).prop('indeterminate', false);
    });

    // Seçili kullanıcı sayısını güncelle
    function updateSelectedCount() {
        $('#selectedCount').val(selectedUsers.length);
        $('#assignShift').prop('disabled', selectedUsers.length === 0 || !$('#shiftDefinitionId').val());
    }

    // Vardiya seçimi değiştiğinde
    $('#shiftDefinitionId').change(function() {
        updateSelectedCount();
        showShiftDetails();
    });

    // Vardiya detaylarını göster
    function showShiftDetails() {
        const selectedOption = $('#shiftDefinitionId option:selected');
        const shiftId = selectedOption.val();

        if (!shiftId) {
            $('#shiftDetails').hide();
            return;
        }

        const scheduleData = selectedOption.data('schedule');
        const totalHours = selectedOption.data('hours');

        if (!scheduleData) {
            $('#shiftDetails').hide();
            return;
        }

        // Haftalık program bilgilerini oluştur
        let scheduleHtml = '';
        const dayNames = {
            'monday': 'Pazartesi',
            'tuesday': 'Salı',
            'wednesday': 'Çarşamba',
            'thursday': 'Perşembe',
            'friday': 'Cuma',
            'saturday': 'Cumartesi',
            'sunday': 'Pazar'
        };

        Object.keys(scheduleData).forEach(function(day) {
            const dayData = scheduleData[day];
            const isWorking = dayData.is_working_day;
            const dayName = dayNames[day] || dayData.name;

            let badgeClass = isWorking ? 'bg-success' : 'bg-secondary';
            let timeText = isWorking ? `${dayData.start} - ${dayData.end}` : 'İzin Günü';

            scheduleHtml += `
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="schedule-day d-flex align-items-center justify-content-between">
                        <span class="fw-medium">${dayName}:</span>
                        <span class="badge ${badgeClass}">${timeText}</span>
                    </div>
                </div>
            `;
        });

        $('#weeklyScheduleInfo').html(scheduleHtml);
        $('#totalHoursInfo').html(`
            <i class="ri-calendar-check-line me-1"></i>
            Toplam: ${totalHours} saat/hafta
        `);

        $('#shiftDetails').show();
    }

    // Vardiya atama
    $('#assignShift').click(function() {
        const shiftId = $('#shiftDefinitionId').val();

        if (!shiftId) {
            showError('Lütfen bir vardiya seçin.');
            return;
        }

        if (selectedUsers.length === 0) {
            showError('Lütfen en az bir kullanıcı seçin.');
            return;
        }

        Swal.fire({
            title: 'Vardiya Atama Onayı',
            text: `${selectedUsers.length} kullanıcıya vardiya atamak istediğinize emin misiniz?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Evet, Ata',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                assignShiftToUsers(shiftId, selectedUsers);
            }
        });
    });

    // Vardiya atama işlemi
    function assignShiftToUsers(shiftId, userIds) {
        $.ajax({
            url: '{{ route("backend.shift_assignment_assign") }}',
            type: 'POST',
            data: {
                shift_definition_id: shiftId,
                user_ids: userIds,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#assignShift').prop('disabled', true).html('<i class="ri-loader-4-line"></i> Atanıyor...');
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Başarılı!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Tamam'
                    }).then(() => {
                        // Formu sıfırla
                        selectedUsers = [];
                        $('#shiftDefinitionId').val('');
                        updateSelectedCount();
                        loadUsers(); // Kullanıcıları yeniden yükle
                    });
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr) {
                let message = 'Vardiya atama sırasında hata oluştu.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showError(message);
            },
            complete: function() {
                $('#assignShift').prop('disabled', false).html('<i class="ri-check-line"></i> Vardiya Ata');
            }
        });
    }

    // Hata mesajı göster
    function showError(message) {
        Swal.fire({
            title: 'Hata!',
            text: message,
            icon: 'error',
            confirmButtonText: 'Tamam'
        });
    }

    // Sayfa yüklendiğinde kullanıcıları yükle
    loadUsers();

    // Vardiya select'ine hover efekti ekle
    $('#shiftDefinitionId').on('mouseenter', 'option', function() {
        const scheduleData = $(this).data('schedule');
        const hours = $(this).data('hours');

        if (scheduleData && hours) {
            let tooltip = `Toplam: ${hours} saat/hafta\n`;
            Object.keys(scheduleData).forEach(function(day) {
                const dayData = scheduleData[day];
                if (dayData.is_working_day) {
                    tooltip += `${dayData.name}: ${dayData.start}-${dayData.end}\n`;
                }
            });
            $(this).attr('title', tooltip);
        }
    });

    // Select2 kullanıyorsak, onun için özel tooltip
    if (typeof $.fn.select2 !== 'undefined') {
        $('#shiftDefinitionId').select2({
            placeholder: 'Vardiya Seçiniz',
            allowClear: true,
            templateResult: function(option) {
                if (!option.id) return option.text;

                const $option = $(option.element);
                const scheduleData = $option.data('schedule');
                const hours = $option.data('hours');

                if (scheduleData && hours) {
                    let workingDays = 0;
                    Object.keys(scheduleData).forEach(function(day) {
                        if (scheduleData[day].is_working_day) workingDays++;
                    });

                    return $(`
                        <div>
                            <strong>${option.text}</strong>
                            <br>
                            <small class="text-muted">
                                <i class="ri-calendar-line"></i> ${workingDays} gün çalışma
                                <i class="ri-time-line ms-2"></i> ${hours} saat/hafta
                            </small>
                        </div>
                    `);
                }

                return $(`<div>${option.text}</div>`);
            }
        });
    }
});
</script>
@endsection
