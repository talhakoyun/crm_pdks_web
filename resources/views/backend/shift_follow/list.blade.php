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
            <table datatable class="table bordered-table mb-0 text-center" id="dataTable" data-page-length='10'>
                <thead>
                    <tr>
                        <th scope="col" class="text-center align-middle">Tip</th>
                        <th scope="col" class="text-center align-middle">Personel</th>
                        <th scope="col" class="text-center align-middle">Vardiya</th>
                        <th scope="col" class="text-center align-middle">Tarih</th>
                        <th scope="col" class="text-center align-middle">Cihaz</th>
                        <th scope="col" class="text-center align-middle">Açıklama</th>
                        <th scope="col" class="text-center align-middle">İşlemler</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Tooltipleri etkinleştir
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

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
                        data: 'shift_follow_type_id',
                        name: 'shift_follow_type_id',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'user_id',
                        name: 'user_id',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'shift_id',
                        name: 'shift_id',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date',
                        className: 'text-center align-middle',
                    },
                    {
                        data: 'device_model',
                        name: 'device_model',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'note',
                        name: 'note',
                        className: 'text-center align-middle'
                    },
                    {
                        render: function(data, type, row) {
                            return `
                        <div class="text-center">
                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                <a href="{{ route('backend.' . $container->page . '_form') }}/${row.id}" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </a>
                                <button type="button" class="remove-item-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" row-delete="${row.id}">
                                    <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                </button>
                            </div>
                        </div>`;
                        },
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle',
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
        });
    </script>
@endsection
