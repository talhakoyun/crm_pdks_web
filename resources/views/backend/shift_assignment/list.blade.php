@extends('layout.layout')
@php
    $title = $container->title;
    $subTitle = $container->title . ' Listesi';
@endphp

@section('content')
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $subTitle }}</h5>
            <a href="{{ route('backend.' . $container->page . '_form') }}"
                class="btn btn-primary btn-sm rounded-pill waves-effect waves-themed d-flex align-items-center">
                <i class="ri-add-line me-1"></i> Vardiya Ata
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive scroll-sm">
                <table datatable class="table bordered-table mb-0" id="dataTable" data-page-length='10'>
                    <thead>
                        <tr>
                            <th scope="col">Vardiya Adı</th>
                            <th scope="col">Kullanıcı Sayısı</th>
                            <th scope="col">Çalışma Günleri</th>
                            <th scope="col">Haftalık Saat</th>
                            <th scope="col">Vardiya Saatleri</th>
                            <th scope="col" class="text-center">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kullanıcı Detayları Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userDetailsModalLabel">Vardiya Kullanıcıları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="userDetailsList">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('backend.' . $container->page . '_list') }}",
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        },
        columns: [
            {
                data: 'shift_name',
                name: 'shift_name',
                render: function(data, type, row) {
                    return `<strong>${data}</strong>`;
                }
            },
            {
                data: 'user_count',
                name: 'user_count',
                className: 'text-center',
                render: function(data, type, row) {
                    return `<span class="badge bg-primary cursor-pointer" onclick="showUserDetails('${row.shift_name}', ${JSON.stringify(row.users).replace(/"/g, '&quot;')})">${data} Kullanıcı</span>`;
                }
            },
            {
                data: 'working_days',
                name: 'working_days',
                className: 'text-center',
                render: function(data, type, row) {
                    if (!data || data.length === 0) {
                        return '<span class="badge bg-warning">Tanımsız</span>';
                    }

                    const dayNames = {
                        'monday': 'Pzt',
                        'tuesday': 'Sal',
                        'wednesday': 'Çar',
                        'thursday': 'Per',
                        'friday': 'Cum',
                        'saturday': 'Cmt',
                        'sunday': 'Paz'
                    };

                    const displayDays = data.map(day => dayNames[day] || day);
                    return `<span class="badge bg-success">${displayDays.join(', ')}</span>`;
                }
            },
            {
                data: 'weekly_hours',
                name: 'weekly_hours',
                className: 'text-center',
                render: function(data, type, row) {
                    if (data == 0) {
                        return '<span class="badge bg-warning">0 saat</span>';
                    }
                    return `<span class="badge bg-info">${parseFloat(data).toFixed(1)} saat</span>`;
                }
            },
            {
                data: 'schedule',
                name: 'schedule',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (!data) return '-';

                    let scheduleHtml = '<div class="schedule-preview">';
                    const dayNames = {
                        'monday': 'Pzt',
                        'tuesday': 'Sal',
                        'wednesday': 'Çar',
                        'thursday': 'Per',
                        'friday': 'Cum',
                        'saturday': 'Cmt',
                        'sunday': 'Paz'
                    };

                    Object.keys(data).forEach(function(day) {
                        const dayData = data[day];
                        if (dayData.is_working_day) {
                            const dayName = dayNames[day] || dayData.name;
                            scheduleHtml += `<small class="d-block">${dayName}: ${dayData.start}-${dayData.end}</small>`;
                        }
                    });
                    scheduleHtml += '</div>';

                    return scheduleHtml;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center gap-10 justify-content-center">
                            <button type="button"
                                    class="edit-shift-btn bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle"
                                    data-shift-id="${row.id}"
                                    data-shift-name="${row.shift_name}"
                                    title="Vardiya Düzenle">
                                <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                            </button>
                            <button type="button"
                                    class="delete-shift-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle"
                                    data-shift-id="${row.id}"
                                    data-shift-name="${row.shift_name}"
                                    title="Tüm Atamaları Kaldır">
                                <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'desc']], // Kullanıcı sayısına göre sırala
        pageLength: 15,
        language: {
            url: '/assets/json/tr.json'
        }
    });

    // Kullanıcı detaylarını göster
    window.showUserDetails = function(shiftName, users) {
        $('#userDetailsModalLabel').text(shiftName + ' - Atanmış Kullanıcılar');

        let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Ad Soyad</th><th>Email</th><th>Şube</th><th>Departman</th></tr></thead><tbody>';

        users.forEach(function(user) {
            html += `<tr>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.branch}</td>
                <td>${user.department}</td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        $('#userDetailsList').html(html);
        $('#userDetailsModal').modal('show');
    };

    // Vardiya düzenle butonu
    $(document).on('click', '.edit-shift-btn', function() {
        const shiftId = $(this).data('shift-id');
        const shiftName = $(this).data('shift-name');

        // Form sayfasına yönlendir ve shift_id parametresi ile o vardiyaya atanmış kullanıcıları seçili getir
        window.location.href = `{{ route('backend.' . $container->page . '_form') }}?edit_shift=${shiftId}`;
    });

    // Vardiya silme butonu
    $(document).on('click', '.delete-shift-btn', function() {
        const shiftId = $(this).data('shift-id');
        const shiftName = $(this).data('shift-name');

        Swal.fire({
            title: 'Emin misiniz?',
            text: `${shiftName} vardiyasına atanmış tüm kullanıcıların atamaları kaldırılacak!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, Kaldır',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteShiftAssignments(shiftId, shiftName);
            }
        });
    });

    // Vardiya atamalarını sil
    function deleteShiftAssignments(shiftId, shiftName) {
        $.ajax({
            url: "{{ route('backend.' . $container->page . '_delete') }}",
            type: 'DELETE',
            data: {
                shift_id: shiftId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Başarılı!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Tamam'
                    }).then(() => {
                        table.ajax.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Hata!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            },
            error: function(xhr) {
                let message = 'Vardiya atamaları kaldırılırken hata oluştu.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    title: 'Hata!',
                    text: message,
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            }
        });
    }
});
</script>

<style>
.schedule-preview {
    max-width: 200px;
}

.cursor-pointer {
    cursor: pointer;
}

.badge.cursor-pointer:hover {
    opacity: 0.8;
}
</style>
@endsection
