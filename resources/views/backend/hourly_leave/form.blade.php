@extends('layout.layout')
@php
    $title = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
    $subTitle = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6">{{ $container->title }} {{ !is_null($item->id) ? 'Düzenle' : 'Ekle' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.' . $container->page . '_save', ['unique' => $item->id]) }}"
                        method="POST">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-6">
                                <label class="form-label">Personel</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:user"></iconify-icon>
                                    </span>
                                    <select class="form-select select2" name="user_id">
                                        <option value="">Seçiniz</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ (old('user_id') ?? ($item->user_id ?? '')) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} {{ $user->surname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-form-error field="user_id" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Tarih</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:calendar"></iconify-icon>
                                    </span>
                                    <input type="date" class="form-control" name="date" id="date"
                                        placeholder="Lütfen tarih giriniz"
                                        value="{{ old('date') ?? ($item->date ?? '') }}">
                                    <x-form-error field="date" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Başlangıç Saati</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:time"></iconify-icon>
                                    </span>
                                    <input type="time" class="form-control" name="start_time" id="start_time"
                                        placeholder="Lütfen başlangıç saati giriniz"
                                        value="{{ old('start_time') ?? ($item->start_time ?? '') }}">
                                    <x-form-error field="start_time" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Bitiş Saati</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:time"></iconify-icon>
                                    </span>
                                    <input type="time" class="form-control" name="end_time" id="end_time"
                                        placeholder="Lütfen bitiş saati giriniz"
                                        value="{{ old('end_time') ?? ($item->end_time ?? '') }}">
                                    <x-form-error field="end_time" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">İzin Tipi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:document"></iconify-icon>
                                    </span>
                                    <select class="form-select select2" name="type">
                                        <option value="">Seçiniz</option>
                                        @foreach ($holidayTypes as $holidayType)
                                            <option value="{{ $holidayType->id }}" {{ (old('type') ?? ($item->type ?? '')) == $holidayType->id ? 'selected' : '' }}>
                                                {{ $holidayType->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-form-error field="type" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Durumu</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:status"></iconify-icon>
                                    </span>
                                    <select class="form-select select2" name="status">
                                        <option value="">Seçiniz</option>
                                        <option value="pending" {{ (old('status') ?? ($item->status ?? '')) == 'pending' ? 'selected' : '' }}>Beklemede</option>
                                        <option value="approved" {{ (old('status') ?? ($item->status ?? '')) == 'approved' ? 'selected' : '' }}>Onaylandı</option>
                                        <option value="rejected" {{ (old('status') ?? ($item->status ?? '')) == 'rejected' ? 'selected' : '' }}>Reddedildi</option>
                                    </select>
                                    <x-form-error field="status" />
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Açıklama</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="material-symbols:description-outline-sharp"></iconify-icon>
                                    </span>
                                    <textarea class="form-control" name="reason" placeholder="Lütfen açıklama giriniz" rows="3">{{ old('reason') ?? ($item->reason ?? '') }}</textarea>
                                    <x-form-error field="reason" />
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-600">Kaydet</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Select2 initialization
        $('.select2').select2({
            placeholder: "Lütfen seçiniz",
            allowClear: true,
            width: '100%'
        });

        // Saat validasyonu
        $('#start_time, #end_time').on('change', function() {
            var startTime = $('#start_time').val();
            var endTime = $('#end_time').val();

            if (startTime && endTime && startTime >= endTime) {
                alert('Bitiş saati başlangıç saatinden sonra olmalıdır!');
                $(this).val('');
            }
        });
    });
</script>
@endsection
