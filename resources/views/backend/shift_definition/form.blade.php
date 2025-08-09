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
                            <div class="col-12">
                                <label class="form-label">Vardiya Adı</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="icon-park-outline:branch-one"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="Lütfen vardiya adı giriniz"
                                        value="{{ old('title') ?? ($item->title ?? '') }}">
                                    <x-form-error field="title" />
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Başlangıç Saati</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <input type="time" class="form-control" name="start_time"
                                        value="{{ old('start_time') ?? ($item->start_time ?? '') }}">
                                    <x-form-error field="start_time" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Bitiş Saati</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="fluent:status-16-regular"></iconify-icon>
                                    </span>
                                    <input type="time" class="form-control" name="end_time"
                                        value="{{ old('end_time') ?? ($item->end_time ?? '') }}">
                                    <x-form-error field="end_time" />
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

    const timeSettings = {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
    }

    $("#shift_start_time_picker").flatpickr(timeSettings)
    $("#shift_end_time_picker").flatpickr(timeSettings)

</script>
@endsection