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
                Ekle
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive scroll-sm">
                <table datatable class="table bordered-table mb-0" id="dataTable" data-page-length='10'>
                    <thead>
                        <tr>
                            <th scope="col">Başlık</th>
                            <th scope="col">Gönderim Tipi</th>
                            <th scope="col">Başlangıç Tarihi</th>
                            <th scope="col">Bitiş Tarihi</th>
                            <th scope="col" class="text-center">Durum</th>
                            <th scope="col" class="text-center">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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
                        var cfilter = {};
                        return $.extend({}, d, {
                            "cfilter": cfilter
                        });
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [{
                        data: 'title',
                        name: 'title',
                        className: 'text-start'
                    },
                    {
                        data: 'send_type_text',
                        name: 'send_type',
                        className: 'text-center'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        className: 'text-center'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        render: function(data, type, row) {
                            return `
                            <td class="text-center">
                                <div class="d-flex align-items-center gap-10 justify-content-center">
                                    <a href="{{ route('backend.' . $container->page . '_form') }}/${row.id}" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                                        <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                    </a>
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
                    [2, 'desc']
                ],
                pageLength: 15,
            });

            BaseCRUD.delete("{{ route('backend.' . $container->page . '_delete') }}");
        });
    </script>
@endsection
