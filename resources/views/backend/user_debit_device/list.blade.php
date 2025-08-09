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
                            <th scope="col">Kullanıcı</th>
                            <th scope="col">Zimmet Cihazı</th>
                            <th scope="col">Başlangıç Tarihi</th>
                            <th scope="col">Bitiş Tarihi</th>
                            <th scope="col">Durum</th>
                            <th scope="col" class="text-center">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Teslim Alma Modal -->
    <div class="modal fade" id="returnDeviceModal" tabindex="-1" aria-labelledby="returnDeviceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnDeviceModalLabel">Zimmet Teslim Alma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="returnDeviceForm" action="{{ route('backend.user_debit_device_return') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="return_device_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="return_date" class="form-label">Teslim Alma Tarihi</label>
                            <input type="date" class="form-control" id="return_date" name="return_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="return_note" class="form-label">Teslim Alma Notu</label>
                            <textarea class="form-control" id="return_note" name="return_note" rows="3" placeholder="Teslim alma ile ilgili notlar..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-success">Teslim Alındı Olarak İşaretle</button>
                    </div>
                </form>
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
                        data: 'user_id',
                        name: 'user_id',
                        className: 'text-center'
                    },
                    {
                        data: 'debit_device_id',
                        name: 'debit_device_id',
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
                        className: 'text-center'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
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

            $('[filter-name]').change(function() {
                $("[datatable]").DataTable().ajax.reload();
            });

            BaseCRUD.delete("{{ route('backend.' . $container->page . '_delete') }}");

            // Teslim alma modal işlemleri
            $(document).on('click', '.return-device-btn', function() {
                var deviceId = $(this).attr('data-id');
                $('#return_device_id').val(deviceId);
                $('#returnDeviceModal').modal('show');
            });
        });
    </script>
@endsection
