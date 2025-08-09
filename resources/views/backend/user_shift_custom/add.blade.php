@extends('layout.layout')
@php
    $title = $container->title;
    $subTitle = $container->title . ' Listesi';
@endphp

@section('style')
<style>
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
</style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $subTitle }}</h5>
            <a href="{{ route('backend.' . $container->page . '_list') }}"
                class="btn btn-primary btn-sm rounded-pill waves-effect waves-themed d-flex align-items-center">
                Kayıtları Görüntüle
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('backend.' . $container->page . '_save_bulk') }}" method="POST">
                @csrf
                <div class="row mb-5">
                    <div class="col-md-12">
                        <h6>Hızlı Veri Girişi</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Vardiya</label>
                        <select id="global-shift" class="form-select select2">
                            <option value="">Vardiya Seçiniz</option>
                            @foreach ($shiftDefinitions as $shift)
                                <option value="{{ $shift->id }}">
                                    {{ $shift->title }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tarih Aralığı</label>
                        <div class="input-group">
                            <input type="date" id="global-start-date" class="form-control">
                            <span class="input-group-text">-</span>
                            <input type="date" id="global-end-date" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="apply-to-selected" class="btn btn-primary w-100">Seçilenlere Uygula</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                    </div>
                                </th>
                                <th>Personel Adı</th>
                                <th>Tarih Aralığı</th>
                                <th>Yeni Vardiya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input user-checkbox" type="checkbox" name="selected_users[]" value="{{ $user->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-bold">{{ $user->name }} {{ $user->surname }}</div>
                                                <small class="text-muted">{{ $user->department->title ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="input-group">
                                                <input type="date" class="form-control start-date" name="start_date[{{ $user->id }}]">
                                                <span class="input-group-text">-</span>
                                                <input type="date" class="form-control end-date" name="end_date[{{ $user->id }}]">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select shift-select" name="shift_definition_id[{{ $user->id }}]">
                                            <option value="">Vardiya Seçiniz</option>
                                            @foreach ($shiftDefinitions as $shift)
                                                <option value="{{ $shift->id }}" @if ($user->userShifts?->shift_definition_id == $shift->id) selected @endif>
                                                    {{ $shift->title }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button type="button" id="save-changes" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Başlangıç tarihi seçildiğinde bitiş tarihini de aynı tarih yap
        $(".start-date").on("change", function() {
            var selectedDate = $(this).val();
            var endDateInput = $(this).closest('tr').find('.end-date');
            if (selectedDate) {
                endDateInput.val(selectedDate);
            }
        });

        // Global başlangıç tarihi seçildiğinde global bitiş tarihini de aynı tarih yap
        $("#global-start-date").on("change", function() {
            var selectedDate = $(this).val();
            var endDateInput = $("#global-end-date");

            if (selectedDate) {
                endDateInput.val(selectedDate);
            }
        });

        // Tümünü seç/kaldır
        $("#select-all").on("change", function() {
            $(".user-checkbox").prop("checked", $(this).prop("checked"));
        });

        // Seçilenlere uygula butonu
        $("#apply-to-selected").on("click", function() {
            var shiftId = $("#global-shift").val();
            var startDate = $("#global-start-date").val();
            var endDate = $("#global-end-date").val();

            if (!shiftId || !startDate || !endDate) {
                Swal.fire('Uyarı', 'Lütfen vardiya ve tarih aralığı seçiniz', 'warning');
                return;
            }

            $(".user-checkbox:checked").each(function() {
                var userId = $(this).val();
                var row = $(this).closest('tr');

                // Tarihleri ayarla
                row.find('.start-date').val(startDate);
                row.find('.end-date').val(endDate);

                // Vardiyayı ayarla
                row.find('.shift-select').val(shiftId).trigger('change');
            });
        });

        // Kaydet butonu - Form kontrolü ve gönderimi
        $("#save-changes").on("click", function(e) {
            e.preventDefault();

            var hasError = false;
            var errorMessage = '';
            var rowsWithData = 0;

            // Tüm satırları kontrol et
            $("tbody tr").each(function() {
                var row = $(this);
                var startDateInput = row.find('.start-date');
                var endDateInput = row.find('.end-date');
                var shiftSelect = row.find('.shift-select');

                var startDate = startDateInput.val();
                var endDate = endDateInput.val();
                var shiftId = shiftSelect.val();

                // Hata sınıflarını temizle
                startDateInput.removeClass('is-invalid');
                endDateInput.removeClass('is-invalid');
                shiftSelect.removeClass('is-invalid');

                // Eğer başlangıç tarihi girilmişse diğer alanları kontrol et
                if (startDate) {
                    rowsWithData++;

                    // Bitiş tarihi kontrolü
                    if (!endDate) {
                        endDateInput.addClass('is-invalid');
                        hasError = true;
                        errorMessage = 'Başlangıç tarihi seçildiğinde bitiş tarihi de seçilmelidir.';
                    }

                    // Vardiya kontrolü
                    if (!shiftId) {
                        shiftSelect.addClass('is-invalid');
                        hasError = true;
                        errorMessage = 'Başlangıç tarihi seçildiğinde vardiya da seçilmelidir.';
                    }

                    // Tarih sıralaması kontrolü
                    if (endDate && new Date(startDate) > new Date(endDate)) {
                        startDateInput.addClass('is-invalid');
                        endDateInput.addClass('is-invalid');
                        hasError = true;
                        errorMessage = 'Bitiş tarihi başlangıç tarihinden önce olamaz.';
                    }
                }

                // Eğer bitiş tarihi girilmişse diğer alanları kontrol et
                else if (endDate) {
                    rowsWithData++;

                    // Başlangıç tarihi kontrolü
                    if (!startDate) {
                        startDateInput.addClass('is-invalid');
                        hasError = true;
                        errorMessage = 'Bitiş tarihi seçildiğinde başlangıç tarihi de seçilmelidir.';
                    }

                    // Vardiya kontrolü
                    if (!shiftId) {
                        shiftSelect.addClass('is-invalid');
                        hasError = true;
                        errorMessage = 'Bitiş tarihi seçildiğinde vardiya da seçilmelidir.';
                    }
                }
            });

            // Hiç veri girilmemişse uyar
            if (rowsWithData === 0) {
                Swal.fire('Uyarı', 'En az bir personel için tarih aralığı ve vardiya seçmelisiniz.', 'warning');
                return;
            }

            // Hata varsa göster
            if (hasError) {
                Swal.fire('Hata', errorMessage, 'error');
                return;
            }

            // Herşey tamamsa formu AJAX ile gönder
            var formData = $("form").serialize();

            // Yükleniyor göstergesi
            Swal.fire({
                title: 'Lütfen bekleyin...',
                html: 'Veriler kaydediliyor',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: $("form").attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        // Başarılı işlem
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı!',
                            text: response.message,
                            confirmButtonText: 'Tamam'
                        }).then((result) => {
                            // Başarılı kayıtları temizle
                            if (response.saved_users && response.saved_users.length > 0) {
                                $.each(response.saved_users, function(index, userId) {
                                    var row = $("tr").find("input[name='start_date[" + userId + "]']").closest('tr');
                                    row.find('.start-date').val('');
                                    row.find('.end-date').val('');
                                    row.find('.shift-select').val('');
                                    row.addClass('bg-success-subtle').delay(2000).queue(function(){
                                        $(this).removeClass('bg-success-subtle').dequeue();
                                    });
                                });
                            }
                        });
                    } else {
                        // Hata durumu
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata!',
                            text: response.message,
                            confirmButtonText: 'Tamam'
                        });

                        // Hatalı alanları işaretle
                        if (response.errors) {
                            $.each(response.errors, function(userId, errors) {
                                var row = $("tr").find("input[name='start_date[" + userId + "]']").closest('tr');

                                if (errors.start_date) {
                                    row.find('.start-date').addClass('is-invalid');
                                }

                                if (errors.end_date) {
                                    row.find('.end-date').addClass('is-invalid');
                                }

                                if (errors.shift_definition_id) {
                                    row.find('.shift-select').addClass('is-invalid');
                                }

                                row.addClass('bg-danger-subtle').delay(2000).queue(function(){
                                    $(this).removeClass('bg-danger-subtle').dequeue();
                                });
                            });
                        }
                    }
                },
                error: function(xhr, status, error) {
                    // AJAX hatası
                    Swal.fire({
                        icon: 'error',
                        title: 'Sistem Hatası!',
                        text: 'Bir hata oluştu: ' + error,
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        });


    });
</script>
@endsection
