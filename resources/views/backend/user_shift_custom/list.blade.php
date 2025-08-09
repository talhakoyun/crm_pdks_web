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
                            <th scope="col">Personel</th>
                            <th scope="col">Vardiya</th>
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
                        return $.extend({}, d, {});
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [{
                        data: 'user_name',
                        name: 'user_name'
                    },
                    {
                        data: 'shift_name',
                        name: 'shift_name'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        className: 'text-center'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Delete işlemi
            $(document).on('click', '[row-delete]', function() {
                var id = $(this).attr('row-delete');
                Swal.fire({
                    title: 'Emin misiniz?',
                    text: "Bu kaydı silmek istediğinize emin misiniz?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Evet, sil!',
                    cancelButtonText: 'İptal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('backend.' . $container->page . '_delete') }}",
                            type: 'DELETE',
                            data: {
                                id: id
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire(
                                        'Silindi!',
                                        'Kayıt başarıyla silindi.',
                                        'success'
                                    );
                                    table.ajax.reload();
                                } else {
                                    Swal.fire(
                                        'Hata!',
                                        response.message,
                                        'error'
                                    );
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
