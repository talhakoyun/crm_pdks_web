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
                            <th scope="col">Ad</th>
                            <th scope="col">Email</th>
                            <th scope="col">Telefon</th>
                            <th scope="col">Adres</th>
                            <th scope="col">Resim</th>
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
                        data: 'title',
                        name: 'title',
                        className: 'text-center'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        className: 'text-center'
                    },
                    {
                        data: 'phone_number',
                        name: 'phone_number',
                        className: 'text-center'
                    },
                    {
                        data: 'address',
                        name: 'address',
                        className: 'text-center'
                    },
                    {
                        data: 'image',
                        name: 'image',
                        className: 'text-center',
                        searchable: false,
                        orderable: false,
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        className: 'text-center',
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
