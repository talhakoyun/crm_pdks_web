@extends('layout.layout')
@php
    $title = $container->title;
    $subTitle = $container->title . ' Listesi';
    $script = '<script>
        $(".remove-item-btn").on("click", function() {
            $(this).closest("tr").addClass("d-none")
        });
    </script>';
@endphp

@section('content')
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $subTitle }}</h5>
            <a href="{{ route('backend.' . $container->page . '_form') }}"
                class="btn btn-primary btn-sm rounded-pill waves-effect waves-themed d-flex align-items-center">
                Ekle
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive scroll-sm">
                <table datatable class="table bordered-table mb-0" id="dataTable" data-page-length='10'>
                    <thead>
                        <tr>
                            <th scope="col">Personel</th>
                            <th scope="col">Tarih</th>
                            <th scope="col">Başlangıç Saati</th>
                            <th scope="col">Bitiş Saati</th>
                            <th scope="col">İzin Tipi</th>
                            <th scope="col">Durum</th>
                            <th scope="col" class="text-center">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Durum Değiştirme Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Saatlik İzin Durumu Güncelle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        <input type="hidden" id="hourly_leave_id" name="id">
                        <input type="hidden" id="status_action" name="status">

                        <div class="mb-3">
                            <label for="status_description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="status_description" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" id="saveStatus">Kaydet</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            BaseCRUD.selector = "[datatable]";
            var table = BaseCRUD.ajaxtable({
                ajax: {
                    url: "{{ route('backend.' . $container->page . '_list') }}?datatable=true",
                    type: 'POST',
                    data: function(d) {
                        var cfilter = {
                            branch: $('[filter-name="branch"]').val()
                        };
                        return $.extend({}, d, {
                            "cfilter": cfilter
                        });
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [{
                        data: 'user_id',
                        name: 'user_id',
                        className: 'text-center'
                    },
                    {
                        data: 'date',
                        name: 'date',
                        className: 'text-center'
                    },
                    {
                        data: 'start_time',
                        name: 'start_time',
                        className: 'text-center'
                    },
                    {
                        data: 'end_time',
                        name: 'end_time',
                        className: 'text-center'
                    },
                    {
                        data: 'type',
                        name: 'type',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center',
                    },
                    {
                        render: function(data, type, row) {
                            var statusButtons = '';

                            // Sadece bekleyen durumda onaylama ve reddetme butonları göster
                            if (row.status === 'pending') {
                                statusButtons = `
                                    <button type="button" class="status-btn bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" data-status="approved" data-id="${row.id}">
                                        <iconify-icon icon="mdi:check-bold" class="menu-icon"></iconify-icon>
                                    </button>
                                    <button type="button" class="status-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" data-status="rejected" data-id="${row.id}">
                                        <iconify-icon icon="mdi:close-thick" class="menu-icon"></iconify-icon>
                                    </button>
                                `;
                            }

                            return `
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                <a href="{{ route('backend.' . $container->page . '_form') }}/${row.id}" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </a>
                                ${statusButtons}
                                <button type="button" class="remove-item-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" row-delete="${row.id}">
                                    <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                </button>
                            </div>
                        </td>`;
                        },
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center act-col',
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 15,
            });

            $('[filter-name]').change(function() {
                $("[datatable]").DataTable().ajax.reload();
            });

            BaseCRUD.delete("{{ route('backend.' . $container->page . '_delete') }}");

            // Status değiştirme işlemleri
            $(document).on('click', '.status-btn', function() {
                var id = $(this).data('id');
                var status = $(this).data('status');

                $('#hourly_leave_id').val(id);
                $('#status_action').val(status);
                $('#status_description').val('');

                var modalTitle = status === 'approved' ? 'Saatlik İzin Onaylama' : 'Saatlik İzin Reddetme';
                $('#statusModalLabel').text(modalTitle);

                $('#statusModal').modal('show');
            });

            $('#saveStatus').on('click', function() {
                $.ajax({
                    url: "{{ route('backend.' . $container->page . '_change_status') }}",
                    type: 'POST',
                    data: $('#statusForm').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#statusModal').modal('hide');
                            $("[datatable]").DataTable().ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Başarılı',
                                text: 'Durum başarıyla güncellendi',
                                confirmButtonText: 'Tamam',
                                timer: 1500
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Hata',
                                text: response.message || 'Bir hata oluştu',
                                confirmButtonText: 'Tamam',
                                timer: 1500
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata',
                            text: 'İşlem sırasında bir hata oluştu',
                            confirmButtonText: 'Tamam',
                            timer: 1500
                        });
                    }
                });
            });
        });
    </script>
@endsection
