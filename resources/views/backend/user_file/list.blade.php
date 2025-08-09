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
                            <th>#</th>
                            <th scope="col">Personel</th>
                            <th scope="col">Dosya Tipi</th>
                            <th scope="col">Başlık</th>
                            <th scope="col">Dosya Bilgisi</th>
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
                        data: 'id',
                        name: 'id',
                        visible: false,
                        className: 'text-center d-none'
                    },
                    {
                        data: 'user_name',
                        name: 'user_name',
                        className: 'text-left'
                    },
                    {
                        data: 'file_type_name',
                        name: 'file_type_name',
                        className: 'text-left'
                    },
                    {
                        data: 'title',
                        name: 'title',
                        className: 'text-left',
                        render: function(data, type, row) {
                            return data ? data : '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        data: 'file_info',
                        name: 'file_info',
                        className: 'text-left'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        className: 'text-center',
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                pageLength: 15,
            });

            BaseCRUD.delete("{{ route('backend.' . $container->page . '_delete') }}");
        });
    </script>
@endsection
