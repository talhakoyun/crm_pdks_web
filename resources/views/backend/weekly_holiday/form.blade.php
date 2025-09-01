@extends('layout.layout')
@php
    $title = $container->title;
    $subTitle = isset($editHoliday) ? 'Haftalık Tatil Günleri Düzenle' : 'Haftalık Tatil Günleri Atama';
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

                    <!-- Tatil Günleri Atama -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-warning-subtle">
                                <div class="card-body p-3">
                                    <h6 class="mb-3">Haftalık Tatil Günleri</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Seçili Kullanıcı Sayısı</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="ri-user-line"></i>
                                                </span>
                                                <input type="text" class="form-control" id="selectedCount" value="0" readonly>
                                                <button type="button" class="btn btn-warning" id="assignHolidays" disabled>
                                                    <i class="ri-calendar-line"></i> Tatil Günü Ata
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Seçili Tatil Günleri</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="ri-calendar-check-line"></i>
                                                </span>
                                                <input type="text" class="form-control" id="selectedHolidaysDisplay" value="Henüz seçilmedi" readonly>
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
                                                        <th>Mevcut Tatil Günleri</th>
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

    <!-- Tatil Günleri Modal -->
    <div class="modal fade" id="holidayDaysModal" tabindex="-1" aria-labelledby="holidayDaysModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="holidayDaysModalLabel">
                        <i class="ri-calendar-line me-2"></i>Haftalık Tatil Günleri Seçin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <strong>Bilgi:</strong> Seçilen günler kullanıcıların haftalık tatil günleri olacaktır.
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="form-label fw-bold">Tatil Günlerini Seçin:</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input holiday-day-checkbox" type="checkbox" value="1" id="monday">
                                        <label class="form-check-label fw-medium" for="monday">
                                            <i class="ri-calendar-2-line me-2 text-primary"></i>Pazartesi
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input holiday-day-checkbox" type="checkbox" value="2" id="tuesday">
                                        <label class="form-check-label fw-medium" for="tuesday">
                                            <i class="ri-calendar-2-line me-2 text-primary"></i>Salı
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input holiday-day-checkbox" type="checkbox" value="3" id="wednesday">
                                        <label class="form-check-label fw-medium" for="wednesday">
                                            <i class="ri-calendar-2-line me-2 text-primary"></i>Çarşamba
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input holiday-day-checkbox" type="checkbox" value="4" id="thursday">
                                        <label class="form-check-label fw-medium" for="thursday">
                                            <i class="ri-calendar-2-line me-2 text-primary"></i>Perşembe
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input holiday-day-checkbox" type="checkbox" value="5" id="friday">
                                        <label class="form-check-label fw-medium" for="friday">
                                            <i class="ri-calendar-2-line me-2 text-primary"></i>Cuma
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input holiday-day-checkbox" type="checkbox" value="6" id="saturday">
                                        <label class="form-check-label fw-medium" for="saturday">
                                            <i class="ri-calendar-2-line me-2 text-warning"></i>Cumartesi
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input holiday-day-checkbox" type="checkbox" value="7" id="sunday">
                                        <label class="form-check-label fw-medium" for="sunday">
                                            <i class="ri-calendar-2-line me-2 text-danger"></i>Pazar
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="alert alert-light border">
                            <h6 class="mb-2">
                                <i class="ri-calendar-check-line me-2"></i>Seçilen Tatil Günleri:
                            </h6>
                            <div id="selectedHolidaysPreview" class="text-muted">
                                Henüz gün seçilmedi
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i> İptal
                    </button>
                    <button type="button" class="btn btn-warning" id="saveHolidays" disabled>
                        <i class="ri-save-line"></i> Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
<style>
    /* Kullanıcı tablosu stilleri */
    .user-row {
        transition: all 0.2s ease;
    }

    .user-row:hover {
        background-color: rgba(255, 193, 7, 0.05) !important;
    }

    .user-row.table-warning {
        background-color: rgba(255, 193, 7, 0.15) !important;
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

    /* Modal stilleri */
    .modal-lg {
        max-width: 600px;
    }

    .holiday-day-checkbox {
        transform: scale(1.1);
    }

    .form-check-label {
        cursor: pointer;
        user-select: none;
    }

    .form-check:hover .form-check-label {
        color: #0d6efd;
    }

    /* Seçilen günler önizleme */
    #selectedHolidaysPreview {
        min-height: 30px;
        padding: 10px;
        border-radius: 6px;
        background-color: #f8f9fa;
    }

    /* Animasyonlar */
    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('script')
<script>
$(document).ready(function() {
    let users = [];
    let selectedUsers = [];
    let selectedHolidayDays = [];

    const dayNames = {
        1: 'Pazartesi',
        2: 'Salı',
        3: 'Çarşamba',
        4: 'Perşembe',
        5: 'Cuma',
        6: 'Cumartesi',
        7: 'Pazar'
    };

    // Edit mode için önceden seçili kullanıcıları set et
    @if(isset($editHoliday) && $editHoliday && !empty($selectedUsers))
        users = @json($selectedUsers);
        selectedUsers = @json(array_column($selectedUsers, 'id'));
        selectedHolidayDays = @json($editHoliday->holiday_days ?? []);

        // Kullanıcıları render et
        renderUsers();
        updateSelectedCount();
        updateHolidaysDisplay();
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
            url: '{{ route("backend.weekly_holiday_get_users") }}',
            type: 'GET',
            data: {
                branch_id: branchId,
                department_id: departmentId,
                search: search
            },
            beforeSend: function() {
                $('#usersList').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-warning" role="status">
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
            const holidayBadgeClass = user.current_holidays === 'Tanımlı değil' ? 'bg-warning' : 'bg-success';

            tableHtml += `
                <tr class="user-row ${isSelected ? 'table-warning' : ''}" data-user-id="${user.id}" style="cursor: pointer;">
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
                                <h6 class="mb-0">${user.name} ${user.surname}</h6>
                                <small class="text-muted">${user.email}</small>
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
                        <span class="badge ${holidayBadgeClass}">${user.current_holidays}</span>
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
            $(this).closest('tr').addClass('table-warning');
        } else {
            selectedUsers = selectedUsers.filter(id => id !== userId);
            $(this).closest('tr').removeClass('table-warning');
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
        $('#assignHolidays').prop('disabled', selectedUsers.length === 0);
    }

    // Tatil günü atama butonuna tıklama
    $('#assignHolidays').click(function() {
        if (selectedUsers.length === 0) {
            showError('Lütfen en az bir kullanıcı seçin.');
            return;
        }

        $('#holidayDaysModal').modal('show');
    });

    // Tatil günü checkbox değişimi
    $(document).on('change', '.holiday-day-checkbox', function() {
        updateSelectedHolidays();
    });

    // Seçili tatil günlerini güncelle
    function updateSelectedHolidays() {
        selectedHolidayDays = [];
        $('.holiday-day-checkbox:checked').each(function() {
            selectedHolidayDays.push(parseInt($(this).val()));
        });

        // Önizleme güncelle
        if (selectedHolidayDays.length === 0) {
            $('#selectedHolidaysPreview').html('<span class="text-muted">Henüz gün seçilmedi</span>');
            $('#saveHolidays').prop('disabled', true);
        } else {
            const dayNamesArray = selectedHolidayDays.map(day => dayNames[day]);
            $('#selectedHolidaysPreview').html(`
                <div class="d-flex flex-wrap gap-2">
                    ${dayNamesArray.map(day => `<span class="badge bg-warning">${day}</span>`).join('')}
                </div>
            `);
            $('#saveHolidays').prop('disabled', false);
        }

        // Ana ekrandaki display'i güncelle
        updateHolidaysDisplay();
    }

    // Ana ekrandaki tatil günleri display'ini güncelle
    function updateHolidaysDisplay() {
        if (selectedHolidayDays.length === 0) {
            $('#selectedHolidaysDisplay').val('Henüz seçilmedi');
        } else {
            const dayNamesArray = selectedHolidayDays.map(day => dayNames[day]);
            $('#selectedHolidaysDisplay').val(dayNamesArray.join(', '));
        }
    }

    // Tatil günlerini kaydet
    $('#saveHolidays').click(function() {
        if (selectedHolidayDays.length === 0) {
            showError('Lütfen en az bir tatil günü seçin.');
            return;
        }

        if (selectedUsers.length === 0) {
            showError('Lütfen en az bir kullanıcı seçin.');
            return;
        }

        const dayNamesArray = selectedHolidayDays.map(day => dayNames[day]);
        const message = `${selectedUsers.length} kullanıcıya ${dayNamesArray.join(', ')} günlerini tatil günü olarak atamak istediğinize emin misiniz?`;

        Swal.fire({
            title: 'Tatil Günü Atama Onayı',
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, Ata',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                assignHolidaysToUsers();
            }
        });
    });

    // Tatil günlerini kullanıcılara ata
    function assignHolidaysToUsers() {
        $.ajax({
            url: '{{ route("backend.weekly_holiday_assign") }}',
            type: 'POST',
            data: {
                user_ids: selectedUsers,
                holiday_days: selectedHolidayDays,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#saveHolidays').prop('disabled', true).html('<i class="ri-loader-4-line"></i> Atanıyor...');
            },
            success: function(response) {
                if (response.success) {
                    $('#holidayDaysModal').modal('hide');

                    Swal.fire({
                        title: 'Başarılı!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Tamam'
                    }).then(() => {
                        // Formu sıfırla
                        selectedUsers = [];
                        selectedHolidayDays = [];
                        $('.holiday-day-checkbox').prop('checked', false);
                        updateSelectedCount();
                        updateHolidaysDisplay();
                        loadUsers(); // Kullanıcıları yeniden yükle
                    });
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr) {
                let message = 'Tatil günleri atama sırasında hata oluştu.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showError(message);
            },
            complete: function() {
                $('#saveHolidays').prop('disabled', false).html('<i class="ri-save-line"></i> Kaydet');
            }
        });
    }

    // Modal kapatıldığında checkbox'ları temizle
    $('#holidayDaysModal').on('hidden.bs.modal', function() {
        // Sadece kaydetme işlemi yapılmadıysa temizle
        if (!$(this).data('saved')) {
            $('.holiday-day-checkbox').prop('checked', false);
            updateSelectedHolidays();
        }
        $(this).removeData('saved');
    });

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
});
</script>
@endsection
