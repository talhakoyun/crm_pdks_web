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
                class="btn btn-warning btn-sm rounded-pill waves-effect waves-themed d-flex align-items-center">
                <i class="ri-calendar-line me-1"></i> Tatil Günü Ata
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive scroll-sm">
                <table datatable class="table bordered-table mb-0" id="dataTable" data-page-length='10'>
                    <thead>
                        <tr>
                            <th scope="col">Personel</th>
                            <th scope="col">Email</th>
                            <th scope="col">Şube</th>
                            <th scope="col">Departman</th>
                            <th scope="col">Tatil Günleri</th>
                            <th scope="col">Oluşturma Tarihi</th>
                            <th scope="col" class="text-center">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tatil Günleri Detay Modal -->
    <div class="modal fade" id="holidayDetailsModal" tabindex="-1" aria-labelledby="holidayDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="holidayDetailsModalLabel">
                        <i class="ri-calendar-check-line me-2"></i>Tatil Günleri Detayı
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="holidayDetailsList">
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
        serverSide: true,
        ajax: {
            url: "{{ route('backend.' . $container->page . '_list') }}",
            type: 'GET',
            data: {
                datatable: true
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        },
        columns: [
            {
                data: 'user_id',
                name: 'user_id',
                render: function(data, type, row) {
                    return `<strong>${data}</strong>`;
                }
            },
            {
                data: 'user_email',
                name: 'user_email',
                defaultContent: '-'
            },
            {
                data: 'branch_name',
                name: 'branch_name',
                defaultContent: 'Tanımsız',
                render: function(data, type, row) {
                    return data ? `<span class="badge bg-info">${data}</span>` : '<span class="badge bg-secondary">Tanımsız</span>';
                }
            },
            {
                data: 'department_name',
                name: 'department_name',
                defaultContent: 'Tanımsız',
                render: function(data, type, row) {
                    return data ? `<span class="badge bg-secondary">${data}</span>` : '<span class="badge bg-secondary">Tanımsız</span>';
                }
            },
            {
                data: 'holiday_days',
                name: 'holiday_days',
                className: 'text-center',
                render: function(data, type, row) {
                    const displayText = data || 'Tanımlı değil';
                    const badgeClass = data === 'Tanımlı değil' ? 'bg-secondary' : 'bg-warning';

                    return `<span class="badge ${badgeClass} cursor-pointer" onclick="showHolidayDetails('${row.user_id}', '${displayText}')" title="Detayları görmek için tıklayın">${displayText}</span>`;
                }
            },
            {
                data: 'created_at',
                name: 'created_at',
                className: 'text-center'
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
                                    class="edit-holiday-btn bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle"
                                    data-holiday-id="${row.id}"
                                    data-user-name="${row.user_id}"
                                    title="Tatil Günlerini Düzenle">
                                <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                            </button>
                            <button type="button"
                                    class="delete-holiday-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle"
                                    data-holiday-id="${row.id}"
                                    data-user-name="${row.user_id}"
                                    title="Tatil Günlerini Kaldır">
                                <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[5, 'desc']], // Oluşturma tarihine göre sırala
        pageLength: 15,
        language: {
            url: '/assets/json/tr.json'
        }
    });

    // Tatil günleri detaylarını göster
    window.showHolidayDetails = function(userName, holidayDaysString) {
        $('#holidayDetailsModalLabel').html(`<i class="ri-calendar-check-line me-2"></i>${userName} - Tatil Günleri`);

        const dayNames = {
            'Pazartesi': { icon: 'ri-calendar-2-line', color: 'primary' },
            'Salı': { icon: 'ri-calendar-2-line', color: 'primary' },
            'Çarşamba': { icon: 'ri-calendar-2-line', color: 'primary' },
            'Perşembe': { icon: 'ri-calendar-2-line', color: 'primary' },
            'Cuma': { icon: 'ri-calendar-2-line', color: 'primary' },
            'Cumartesi': { icon: 'ri-calendar-2-line', color: 'warning' },
            'Pazar': { icon: 'ri-calendar-2-line', color: 'danger' }
        };

        let html = '<div class="row">';

        if (holidayDaysString === 'Tanımlı değil') {
            html += '<div class="col-12"><div class="alert alert-warning"><i class="ri-calendar-close-line me-2"></i>Bu kullanıcı için tatil günü tanımlanmamış.</div></div>';
        } else {
            const holidays = holidayDaysString.split(', ');
            holidays.forEach(function(day) {
                const dayInfo = dayNames[day] || { icon: 'ri-calendar-line', color: 'secondary' };
                html += `
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="${dayInfo.icon} text-${dayInfo.color} me-2"></i>
                            <span class="fw-medium">${day}</span>
                        </div>
                    </div>
                `;
            });
        }

        html += '</div>';
        $('#holidayDetailsList').html(html);
        $('#holidayDetailsModal').modal('show');
    };

    // Tatil günü düzenle butonu
    $(document).on('click', '.edit-holiday-btn', function() {
        const holidayId = $(this).data('holiday-id');
        const userName = $(this).data('user-name');

        // Form sayfasına yönlendir
        window.location.href = `{{ route('backend.' . $container->page . '_form') }}/${holidayId}`;
    });

    // Tatil günü silme butonu
    $(document).on('click', '.delete-holiday-btn', function() {
        const holidayId = $(this).data('holiday-id');
        const userName = $(this).data('user-name');

        Swal.fire({
            title: 'Emin misiniz?',
            text: `${userName} kullanıcısının tatil günleri kaldırılacak!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, Kaldır',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteHolidayAssignment(holidayId, userName);
            }
        });
    });

    // Tatil günü atamasını sil
    function deleteHolidayAssignment(holidayId, userName) {
        $.ajax({
            url: "{{ route('backend.' . $container->page . '_delete') }}",
            type: 'POST',
            data: {
                id: holidayId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        title: 'Başarılı!',
                        text: `${userName} kullanıcısının tatil günleri başarıyla kaldırıldı.`,
                        icon: 'success',
                        confirmButtonText: 'Tamam'
                    }).then(() => {
                        table.ajax.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Hata!',
                        text: response.message || 'Tatil günleri kaldırılırken hata oluştu.',
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            },
            error: function(xhr) {
                let message = 'Tatil günleri kaldırılırken hata oluştu.';
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
.cursor-pointer {
    cursor: pointer;
}

.badge.cursor-pointer:hover {
    opacity: 0.8;
    transform: scale(1.05);
    transition: all 0.2s ease;
}

/* Modal içi tatil günleri için özel stil */
.modal-body .border {
    border-color: #dee2e6 !important;
}

.modal-body .border:hover {
    border-color: #adb5bd !important;
    background-color: #f8f9fa;
    transition: all 0.2s ease;
}

/* Tablo hover efektleri */
.table tbody tr:hover {
    background-color: rgba(255, 193, 7, 0.1);
}
</style>
@endsection
